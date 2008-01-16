<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: flickr.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 */

/**
 * Importer for fetching photos messages from flickr
 *
 * @package org.routamc.photostream
 */
class org_routamc_photostream_importer_flickr extends org_routamc_photostream_importer
{
    var $flickr = null;
    var $api_key = '';

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_photostream_importer_flickr($photostream)
    {
        parent::org_routamc_photostream_importer($photostream);
        
        $this->api_key = $this->_config->get('flickr_api_key');
             
        $_MIDCOM->load_library('org.openpsa.httplib');
    }

    /**
     * Seek users with Flickr account settings set
     *
     * @return Array
     */
    function seek_flickr_users()
    {
        // TODO: With 1.8 we can query parameters more efficiently
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->add_constraint('domain', '=','org.routamc.photostream:flickr');
        $qb->add_constraint('name', '=','username');
        $accounts = $qb->execute();
        if (count($accounts) > 0)
        {
            foreach ($accounts as $account_param)
            {
                $user = new midcom_db_person($account_param->parentguid);
                $this->get_flickr_photos($user, true);
            }
        }
    }
    
    function _clean_description($description)
    {
        $description = strip_tags($description);
        if (substr($description, 0, 20) == 'ZoneTag: Photosphere')
        {
            // No need to import the ZoneTag-generated gibberish
            return '';
        }
        return $description;
    }

    function _fetch_flickr_photos($username, $photos)
    {
        if (is_null($this->flickr))
        {
            require_once(MIDCOM_ROOT . "/org/routamc/photostream/phpFlickr.php");
            $this->flickr = new phpFlickr($this->api_key);
        }
    
        // Find the NSID of the username inputted via the form
        $person = $this->flickr->people_findByUsername($username);
        if (!$person)
        {
            $this->error = 'PHOTOSTREAM_FLICKR_INVALID_USER';
            return null;
        }
        
        // Get the user's latest public photos
        $photos = $this->flickr->people_getPublicPhotos($person['id'], NULL, $photos);
        if (   !$photos
            || count($photos['photo']) == 0)
        {
            $this->error = 'PHOTOSTREAM_FLICKR_CONNECTION_NORESULTS';
            return null;
        }
        
        $flickr_photos = array();
        
        foreach ($photos['photo'] as $photo) 
        {
            // Gather all the information we need of the photo
            $photo_id = "flickr:{$photo['owner']}:{$photo['id']}";
            $photo_info = $this->flickr->photos_getInfo($photo['id']);
            $photo_sizes = $this->flickr->photos_getSizes($photo['id']);
            $photo_url = null;
            foreach ($photo_sizes as $size)
            {
                if ($size['label'] == $this->_config->get('flickr_import_size'))
                {
                    $photo_url = $size['source'];
                }
                
                if ($size['label'] == 'Original')
                {
                    $photo_url_fallback = $size['source'];
                }
            }
            
            if (!$photo_url)
            {
                // Use original as fallback
                $photo_url = $photo_url_fallback;
            }
        
            $flickr_photos[$photo_id] = array
            (
                // The main attributes we need at least
                'id'          => $photo_id,
                'url'         => $photo_url,
                'title'       => $photo['title'],
                'description' => $this->_clean_description($photo_info['description']),
                'taken'       => strtotime($photo_info['dates']['taken']),
                
                // "Nice to have" attributes we can use later
                'rotation'    => $photo_info['rotation'],
                'tags'        => array(),
            );
            
            // Handle position
            if (isset($photo_info['location']))
            {
                $flickr_photos[$photo_id]['location'] = $photo_info['location'];
            }
            
            // Handle tags
            if (isset($photo_info['tags']['tag']))
            {
                foreach ($photo_info['tags']['tag'] as $tag)
                {
                    $flickr_photos[$photo_id]['tags'][$tag['raw']] = '';
                }
            }
        }
    
        return $flickr_photos;
    }

    /**
     * Get flickr photos for a user
     *
     * @param midcom_db_person $user Person to fetch Flickr data for
     * @param boolean $cache Whether to cache the photos to a photos object
     * @param int $photos How many latest photos to fetch
     * @return Array
     */
    function get_flickr_photos($user, $cache = true, $photos = 1)
    {
        $username = $user->parameter('org.routamc.photostream:flickr', 'username');

        if ($username)
        {
            $photos = $this->_fetch_flickr_photos($username, $photos);

            if (is_null($photos))
            {
                return null;
            }

            if ($cache)
            {
                $cached_photos = array();
                
                foreach ($photos as $photo)
                {
                    $photo['photographer'] = $user->id;
                    
                    if ($this->import($photo))
                    {
                        $cached_photos[] = $photo;
                    }
                }
                
                $photos = $cached_photos;
            }

            return $photos;
        }
        else
        {
            $this->error = 'PHOTOSTREAM_FLICKR_NO_USERNAME';
        }

        return null;
    }

    /**
     * Import photo. The entries are associative arrays containing
     * all of the following keys:
     *
     * - id
     * - image_url
     * - photographer
     * - taken
     * - description
     * - tags
     *
     * @param Array $photo Photo in Array format specific to importer
     * @param integer $person_id ID of the person to import photos for
     * @return boolean Indicating success.
     */
    function import($photo)
    {
        // Check for duplicates first
        if ($photo['id'])
        {
            // Check if the photo is already in database
            $qb = org_routamc_photostream_photo_dba::new_query_builder();
            $qb->add_constraint('externalid', '=', $photo['id']);
            if ($qb->count() > 0)
            {
                // This photo is already in database, update only tags and such
                $photos = $qb->execute();
                $photo_obj = $photos[0];
                
                $updated = false;
                if ($photo['title'] != $photo_obj->title)
                {
                    $photo_obj->title = $photo['title'];
                    $updated = true;
                }
                if ($photo['description'] != $photo_obj->description)
                {
                    $photo_obj->description = $photo['description'];
                    $updated = true;
                }
                
                if ($updated)
                {
                    $photo_obj->update();
                }
                
                net_nemein_tag_handler::tag_object($photo_obj, $photo['tags']);
                
                // TODO: Update location too
                
                return true;
            }
        }
        
        // Fetch the Flickr photo and store locally
        $tmp_file = tempnam($GLOBALS['midcom_config']['midcom_tempdir'], 'org_routamc_photostream_importer_flickr');
        $fh = fopen($tmp_file, 'w');
        $image_data = file_get_contents($photo['url']);
        if (!$image_data)
        {
            return false;
        }
        fwrite($fh, $image_data);
        fclose($fh);
        
        $photo_obj = new org_routamc_photostream_photo_dba();
        $photo_obj->node = $this->photostream;
        $photo_obj->title = $photo['title'];
        $photo_obj->description = $photo['description'];
        $photo_obj->taken = $photo['taken'];
        $photo_obj->photographer = $photo['photographer'];
        $photo_obj->externalid = $photo['id'];
        
        if (!$photo_obj->create())
        {
            return false;
        }
        
        if (!$this->datamanager->autoset_storage($photo_obj))
        {
            $photo_obj->delete();
            return false;
        }
        
        if (!$this->datamanager->types[$this->photo_field]->set_image(basename($photo['url']), $tmp_file, $photo['title']))
        {
            $photo_obj->delete();
            
            if (file_exists($tmp_file))
            {
                unlink($tmp_file);
            }
            return false;
        }
        
        if (!$this->datamanager->save())
        {
            $photo_obj->delete();
            
            if (file_exists($tmp_file))
            {
                unlink($tmp_file);
            }
            return false;
        }
        
        net_nemein_tag_handler::tag_object($photo_obj, $photo['tags'], 'org.routamc.photostream');
        
        if (   isset($photo['location'])
            && isset($photo['location']['latitude'])
            && isset($photo['location']['longitude'])
            && $GLOBALS['midcom_config']['positioning_enable'])
        {
            if (!class_exists('org_routamc_positioning_log_dba'))
            {
                // Load the positioning library
                $_MIDCOM->load_library('org.routamc.positioning');
            }
            
            $log = new org_routamc_positioning_log_dba();
            $log->importer = 'flickr';
            $log->person = $photo['photographer'];

            $log->date = (int) $photo['taken'];
            $log->latitude = (float) $photo['location']['latitude'];
            $log->longitude = (float) $photo['location']['longitude'];
            $log->altitude = 0;
            $log->accuracy = $photo['location']['accuracy'];

            // Try to create the entry
            if ($log->create())
            {
                $log->parameter('org.routamc.positioning:flickr', 'photo', $photo['id']);
            }
        }

        if (file_exists($tmp_file))
        {
            unlink($tmp_file);
        }
        return true;
    }
}