<?php
/**
 * @package org.routamc.gallery
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This class is used to manage the photolink objects for a gallery
 * @package org.routamc.gallery
 */
class org_routamc_gallery_helper
{
    var $_node = false;
    var $photos = array();
    var $_old_photos = array();
    var $_sync_method = false;

    /**
     * Constructor, determines the operation mode based on node
     * @param object midcom_db_topic object of the gallery node
     */
    function org_routamc_gallery_helper($node)
    {
        if (!is_object($node))
        {
            return false;
        }
        $this->_node = $node;
        switch ($this->_node->parameter('org.routamc.gallery', 'gallery_type'))
        {
            case ORG_ROUTAMC_GALLERY_TYPE_HANDPICKED:
                $this->_get_photos();
                $this->_sync_method = '_sync_photos';
                break;
            case ORG_ROUTAMC_GALLERY_TYPE_TAGS:
                $this->_sync_method = '_get_sync_tags';
                break;
            case ORG_ROUTAMC_GALLERY_TYPE_TIME:
                $this->_sync_method = '_get_sync_time';
                break;
            default:
                // unknown type
                return false;
        }
    }

    /**
     * Loads links from database
     *
     * @param string $prefix property prefix to use (default: '')
     */
    function _get_photos($prefix = '')
    {
        if (!empty($prefix))
        {
            $varname ="{$prefix}_photos";
        }
        else
        {
            $varname = 'photos';
        }
        $arr =& $this->$varname;
        $qb = org_routamc_gallery_photolink_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->_node->id);
        $photolinks = $qb->execute();
        foreach ($photolinks as $photolink)
        {
            $arr[$photolink->photo] = true;
        }
    }

    /**
     * Determines which photolinks should be in gallery (based on gallery type)
     * and calls the corresponding sync methods.
     */
    function sync()
    {
        if (empty($this->_sync_method))
        {
            return false;
        }
        if (!method_exists($this, $this->_sync_method))
        {
            return false;
        }
        $method =& $this->_sync_method;
        return $this->$method();
    }

    /**
     * Sync handler for tag based gallery
     *
     * Resolves the tags to list of photos, then syncs with DB
     */
    function _get_sync_tags()
    {
        // Populate $this->photos by tag information
        $tags = net_nemein_tag_handler::get_object_tags($this->_node);

        if ($tags)
        {
            $qb = net_nemein_tag_link_dba::new_query_builder();
            $qb->begin_group('OR');
                $qb->add_constraint('fromClass', '=', 'org_routamc_photostream_photo_dba');
                $qb->add_constraint('fromClass', '=', 'org_routamc_photostream_photo');
            $qb->end_group();
            $qb->begin_group('OR');
            foreach ($tags as $tag => $url)
            {
                $qb->add_constraint('tag.tag', '=', $tag);
            }
            $qb->end_group();
            $tag_links = $qb->execute();
            if (!is_array($tag_links))
            {
                // QB error
                return false;
            }
            // Make sure the photos list is empty
            $this->photos = array();
            foreach ($tag_links as $tag_link)
            {
                $photo = new org_routamc_photostream_photo_dba($tag_link->fromGuid);
                if (!is_object($photo))
                {
                    // Could not get photo
                    continue;
                }
                $this->photos[$photo->id] = true;
            }
        }
        // Sync
        return $this->_sync_photos();
    }

    /**
     * Sync handler for time based gallery
     *
     * Resolves the start and end to list of photos, then syncs with DB
     */
    function _get_sync_time()
    {
        // Populate $this->photos by time information
        $start = $this->_node->parameter('org.routamc.gallery', 'gallery_start');
        $end = $this->_node->parameter('org.routamc.gallery', 'gallery_end');
        if (   !$this->start
            || !$this->end
            || $this->end < $this->start)
        {
            // Invalid start/end
            return false;
        }
        $qb = org_routamc_photostream_photo_dba::new_query_builder();
        $qb->add_constraint('taken', '>=', $start);
        $qb->add_constraint('taken', '<=', $end);
        $photos = $qb->execute();
        if (!is_array($photos))
        {
            // QB error
            return false;
        }
        // Make sure the photos list is empty
        $this->photos = array();
        foreach ($photos as $photo)
        {
            $this->photos[$photo->id] = true;
        }
        // Sync
        return $this->_sync_photos();
    }

    /**
     * Synchronizes $this->photos with DB, adding/removing links as necessary
     */
    function _sync_photos()
    {
        $this->_get_photos('_old');
        $added_photos = array_diff_assoc($this->photos, $this->_old_photos);
        $removed_photos = array_diff_assoc($this->_old_photos, $this->photos);
        // Add missing links to photos desired in the gallery
        foreach ($added_photos as $photoid =>  $bool)
        {
            $link = new org_routamc_gallery_photolink_dba();
            $link->photo = $photoid;
            $link->node = $this->_node->id;
            if (!$link->create())
            {
                // TODO: error handling
            }
        }
        // Delete links to photos that should no longer be in this gallery
        foreach ($removed_photos as $photoid => $bool)
        {
            $link = $this->get_link_by_photo($photoid);
            if (!is_object($link))
            {
                // TODO: Error handling
                continue;
            }
            if (!$link->delete())
            {
                // TODO: Error handling
            }
        }

        return true;
    }

    /**
     * Get the link object by photo id
     * @param int $photo ID of photo object
     * @return object photolink or false on failure
     */
    function get_link_by_photo($photo)
    {
        if (!$this->_node)
        {
            return false;
        }
        $qb = org_routamc_gallery_photolink_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->_node->id);
        $qb->add_constraint('photo', '=', $photo);
        $results = $qb->execute();
        if (empty($results))
        {
            return false;
        }
        return $results[0];
    }
}
?>