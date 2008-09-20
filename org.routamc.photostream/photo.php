<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for photo objects
 *
 * @package org.routamc.photostream
 */
class org_routamc_photostream_photo_dba extends __org_routamc_photostream_photo_dba
{
    /**
     * Derived property for raw HTML to display the thumbnail (and possibly title)
     */
    var $thumbnail_html = '';

    /**
     * Raw read EXIF data
     */
    var $raw_exif = false;

    /**
     * Force the reading of EXIF data on updating
     *
     * @var boolean force_exif
     */
    var $force_exif = false;

    /**
     * Database encoding, convert all auto-read data to this.
     */
    var $encoding = 'UTF-8';

    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function get_parent_guid_uncached()
    {
        if ($this->node != 0)
        {
            $parent = new midcom_db_topic($this->node);
            return $parent->guid;
        }
        else
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("No parent defined for this product", MIDCOM_LOG_DEBUG);
            debug_pop();
            return null;
        }
    }

    function _on_creating()
    {
        if (!$this->photographer)
        {
            $this->photographer =  $_MIDGARD['user'];
        }

        return true;
    }

    function _on_updating()
    {
        $this->update_attachment_links(false);
        $this->read_exif_data($this->force_exif);
        if (!$this->photographer)
        {
            $this->photographer =  $_MIDGARD['user'];
        }
        if (empty($this->title))
        {
            // See if we can copy filename from one of the attachments so we have at least some title
            $check = array('archival', 'main');
            foreach ($check as $prop)
            {
                if (!$this->$prop)
                {
                    continue;
                }
                $att = mgd_get_attachment($this->$prop);
                if (!$att)
                {
                    continue;
                }
                if (empty($att->name))
                {
                    continue;
                }
                // Strip prefix
                $name = preg_replace('/^archival_/', '',  $att->name);
                // Strip extension
                $name = preg_replace('/\.(.*)?$/', '',  $name);
                // Convert underscores to spaces (other conversions ?)
                $name = str_replace(array('_'), array(' '), $name);
                $this->title = $name;
                break;
            }
        }
        if (!$this->taken)
        {
            $this->taken = time();
        }

        $this->metadata->published = $this->taken;

        return true;
    }

    function _on_updated()
    {
        $this->_exif_tagback();
        $this->_link_to_galleries();
    }

    function _on_loaded()
    {
        if (empty($this->title))
        {
            $this->title = 'untitled';
        }
        $this->_render_thumbnail_html();
        return true;
    }

    function _render_thumbnail_html()
    {
        if (!$this->thumb)
        {
            return;
        }
        // Detect 1.8 vs 1.7 by presence of this writing of QB
        if (class_exists('midgard_query_builder'))
        {
            $att = new midgard_attachment($this->thumb);
        }
        else
        {
            $att = mgd_get_attachment($this->thumb);
        }
        if (   !is_object($att)
            || !$att->id)
        {
            return;
        }
        if (   !isset($att->guid)
            || empty($att->guid)
            && method_exists($att, 'guid'))
        {
            // Object was initialized with legacy api, thus the guid property is not set.
            $att->guid = $att->guid();
        }
        $img_url = "{$GLOBALS['midcom_config']['midcom_site_url']}midcom-serveattachmentguid-{$att->guid}/" . rawurlencode($att->name);
        $img_size = $att->parameter('midcom.helper.datamanager2.type.blobs', 'size_line');
        $img_tag = "<img src=\"{$img_url}\" class=\"photo thumbnail\" {$img_size} />";
        $this->thumbnail_html = "<div class=\"org_routamc_photostream_photo_thumbnail\">\n    {$img_tag}\n    <span class=\"title\">{$this->title}</span>\n</div>";
    }

    /**
     * Checks the attachments of this object and tries to figure which ones
     * to link to in $this->archival, $this->main, $this->thumb
     *
     * @param boolean $call_update call $this->update() if we change the links
     */
    function update_attachment_links($call_update = true)
    {
        if (!$this->id)
        {
            return;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        $old_values = array
        (
            // PHP5-TODO: These must be copy by value
            'archival' => $this->archival,
            'main' => $this->main,
            'thumb' => $this->thumb
        );
        // Detect 1.8 vs 1.7 by presence of this writing of QB
        if (class_exists('midgard_query_builder'))
        {
            $qb = new midgard_query_builder('midgard_attachment');
            $qb->add_constraint('ptable', '=', 'org_routamc_photostream_photo');
            $qb->add_constraint('pid', '=', $this->id);
            if (class_exists('midgard_query_builder'))
            {
                // This typing format of the QB is only available in 1.8+ thus we can use that for testing parameters capability
                $qb->add_constraint('parameter.domain', '=', 'midcom.helper.datamanager2.type.blobs');
                $qb->add_constraint('parameter.name', '=', 'identifier');
                $qb->begin_group('OR');
                    $qb->add_constraint('parameter.value', '=', 'archival');
                    $qb->add_constraint('parameter.value', '=', 'main');
                    $qb->add_constraint('parameter.value', '=', 'thumbnail');
                $qb->end_group();
            }
            $atts = $qb->execute();
        }
        else
        {
            $atts = array();
            $attachments = $this->list_attachments();
            foreach ($attachments as $attachment)
            {
                $identifier = $attachment->get_parameter('midcom.helper.datamanager2.type.blobs', 'identifier');
                if (   $identifier == 'archival'
                    || $identifier == 'main'
                    || $identifier == 'thumbnail')
                {
                    $atts[] = $attachment;
                }
            }
        }
        if (!is_array($atts))
        {
            // QB error
            debug_pop();
            return;
        }
        $atts_by_identifier = array();
        foreach ($atts as $k => $att)
        {
            // Reload attachment to get parameters working properly
            // Detect 1.8 vs 1.7 by presence of this writing of QB
            if (class_exists('midgard_query_builder'))
            {
                $atts[$k] = new midgard_attachment($att->id);
            }
            else
            {
                $atts[$k] = mgd_get_attachment($att->id);
            }
            $att = $atts[$k];
            //mgd_debug_start();
            $identifier = $att->parameter('midcom.helper.datamanager2.type.blobs', 'identifier');
            //mgd_debug_stop();
            debug_add("got identifier '{$identifier}' for attachment #{$att->id}");
            // safety
            if (empty($identifier))
            {
                continue;
            }
            $atts_by_identifier[$identifier] =& $atts[$k];
        }
        // Set the archival link, to archival attachment if found, main otherwise
        if (array_key_exists('archival', $atts_by_identifier))
        {
            debug_add("setting this->archival = {$atts_by_identifier['archival']->id} (from archival image)");
            $this->archival = $atts_by_identifier['archival']->id;
        }
        else if (array_key_exists('main', $atts_by_identifier))
        {
            debug_add("setting this->archival = {$atts_by_identifier['main']->id} (from main image)");
            $this->archival = $atts_by_identifier['main']->id;
        }
        // Set the main link, to main attachment if found, archival otherwise
        if (array_key_exists('main', $atts_by_identifier))
        {
            debug_add("setting this->main = {$atts_by_identifier['main']->id} (from main image)");
            $this->main = $atts_by_identifier['main']->id;
        }
        else if (array_key_exists('archival', $atts_by_identifier))
        {
            debug_add("setting this->main = {$atts_by_identifier['archival']->id} (from archival image)");
            $this->main = $atts_by_identifier['archival']->id;
        }
        // Set the thumbnail link if proper attachment is found
        if (array_key_exists('thumbnail', $atts_by_identifier))
        {
            debug_add("setting this->thumb = {$atts_by_identifier['thumbnail']->id}");
            $this->thumb = $atts_by_identifier['thumbnail']->id;
        }
        // Re-render this at this point, we might need it before the object is refreshed from DB
        $this->_render_thumbnail_html();
        if (!$call_update)
        {
            // No need to even check if we should update
            debug_add('call_update is false, no need to check for the need, returning now');
            debug_pop();
            return;
        }
        // See if any of the link values have changed, call update if so.
        foreach ($old_values as $property => $value)
        {
            debug_add("checking this->{$property} for need to update ({$this->$property} vs {$value})");
            if ($this->$property != $value)
            {
                debug_add('values differ, calling update()');
                $this->update();
                debug_pop();
                return;
            }
        }
        debug_pop();
        return;
    }

    /**
     * Create a working copy of the attachment, optionally only first X kB of it
     *
     * The option to copy only part of file is mainly for reading EXIF data which is in the headers
     * of the file, and thus only a few first kB are needed for it.
     *
     * @param int $att ID of attachment object
     * @param int $size kB to copy, if value is less than one then full file is copied
     * @return string path to the file (or false in case of failure)
     */
    function create_working_copy($att, $size = -1)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $src = mgd_open_attachment($att, 'r');
        if (!$src)
        {
            debug_add("Could not open attachment #{$att} for reading, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $att_obj = mgd_get_attachment($att);
        $tmpname = tempnam('/tmp', 'orp_');
        unlink($tmpname);
        $tmpname .= '_' . $att_obj->name;
        $dst = fopen($tmpname, 'w');
        if (!$dst)
        {
            debug_add("Could not open '{$tmpname}' for writing", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if ($size > 0)
        {
            $kb = (int)$size * 1024;
            debug_add("Reading {$kb}kB from attachment #{$att} and writing to '{$tmpname}'");
            $buffer = fread($src, $kb);
            fwrite($dst, $buffer, $kb);
            fclose($dst);
            unset($buffer);
            fclose($src);
            debug_pop();
            return $tmpname;
        }
        debug_add("Reading all of attachment #{$att} and writing to '{$tmpname}'");
        while (!feof($src))
        {
            $buffer = fread($src, 131072); /* 128kB */
            fwrite($dst, $buffer, 131072);
        }
        fclose($src);
        fclose($dst);
        unset($buffer);
        debug_pop();
        return $tmpname;
    }

    /**
     * Reads EXIF data from the attached image (archival or main)
     *
     * @param boolean $overwrite overwrite current values with ones we could parse from file even if they exist (defaults to false)
     */
    function read_exif_data($overwrite = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!function_exists('read_exif_data'))
        {
            // TODO: Use some CLI tool if available
            debug_add("'read_exif_data()' not available, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $atts = array('archival' => $this->archival, 'main' => $this->main);
        $data = false;
        foreach ($atts as $k => $att)
        {
            if (!$att)
            {
                debug_add("this->{$k} is does not point to attachment, skipping");
                continue;
            }
            //$tmpfile = $this->create_working_copy($att, 25);
            $tmpfile = $this->create_working_copy($att);
            if (!$tmpfile)
            {
                debug_add("Could not create working copy from this->{$k} (#{$att})");
                continue;
            }
            $data = @read_exif_data($tmpfile, 'FILE,COMPUTED,COMMENT,IFD0,ANY_TAG', true, false);
            if (!$data)
            {
                debug_add("could not read_exif_data from '{$tmpfile}'", MIDCOM_LOG_WARN);
                continue;
            }
            unlink($tmpfile);
            break;
        }
        if (!$data)
        {
            debug_add("Could not get any exif data, aborting", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        if (   isset($this->_config)
            && !$this->_config->get('use_created_in_taken'))
        {
            // Read photo taken time
            $this->_read_exif_data_taken($data, $overwrite);
        }

        // Try to identify the camera
        $this->_read_exif_data_camera($data, $overwrite);

        // See if there's any sort of description already in the image
        $this->_read_exif_data_description($data, $overwrite);

        // PHP5-TODO: Must be copy-by-value
        $this->raw_exif = $data;
        unset($data);
        debug_pop();
        return true;
    }

    function _read_exif_data_taken(&$data, $overwrite)
    {
        $regex = '/([0-9]{4}):([0-9]{1,2}):([0-9]{1,2})\s+([0-9]{1,2}:[0-9]{1,2}:[0-9]{1,2})/';
        $repl = '\1-\2-\3 \4';
        switch(true)
        {
            case (   isset($data['EXIF'])
                  && isset($data['EXIF']['DateTimeOriginal'])
                  && !empty($data['EXIF']['DateTimeOriginal'])):
                $taken = strtotime(preg_replace($regex, $repl, $data['EXIF']['DateTimeOriginal']));
                break;
            case (   isset($data['IFD0'])
                  && isset($data['IFD0']['DateTime'])
                  && !empty($data['IFD0']['DateTime'])):
                $taken = strtotime(preg_replace($regex, $repl, $data['IFD0']['DateTime']));
                break;
            case (   isset($data['EXIF'])
                  && isset($data['EXIF']['DateTimeDigitized'])
                  && !empty($data['EXIF']['DateTimeDigitized'])):
                $taken = strtotime(preg_replace($regex, $repl, $data['EXIF']['DateTimeDigitized']));
                break;
            // TODO: Other sources for taken timestamp ??
            default:
                $taken = false;
        }

        if (   $taken > 7200
            && (   !$this->taken
                || $overwrite))
        {
            $this->taken = $taken;
        }
    }

    function _read_exif_data_camera(&$data, $overwrite)
    {
        $cameraid_components = array();
        if (   isset($data['IFD0'])
            && isset($data['IFD0']['Make'])
            && !empty($data['IFD0']['Make']))
        {
            $cameraid_components['make'] = $data['IFD0']['Make'];
        }
        if (   isset($data['IFD0'])
            && isset($data['IFD0']['Model'])
            && !empty($data['IFD0']['Model']))
        {
            $cameraid_components['model'] = $data['IFD0']['Model'];
        }
        if (   isset($data['MAKERNOTE'])
            && isset($data['MAKERNOTE']['FirmwareVersion'])
            && !empty($data['MAKERNOTE']['FirmwareVersion']))
        {
            $cameraid_components['firmware'] = $data['MAKERNOTE']['FirmwareVersion'];
        }
        if (   isset($data['MAKERNOTE'])
            && isset($data['MAKERNOTE']['OwnerName'])
            && !empty($data['MAKERNOTE']['OwnerName']))
        {
            $cameraid_components['owner'] = $data['MAKERNOTE']['OwnerName'];
        }
        /*
           TODO: Figure out a sensible way (esp if owner is not set...) to identify the individual camera
           OTOH: if we have owner and firmware along with make/model we can quite reliably use this to identify the
           photographer.
        */
    }

    function _read_exif_data_description(&$data, $overwrite)
    {
        $encoding = false;
        switch(true)
        {
            case (   isset($data['COMPUTED'])
                  && isset($data['COMPUTED']['UserComment'])
                  && !empty($data['COMPUTED']['UserComment'])):
                $description = $data['COMPUTED']['UserComment'];
                if (   isset($data['COMPUTED']['UserCommentEncoding'])
                    && !empty($data['COMPUTED']['UserCommentEncoding'])
                    && strtoupper($data['COMPUTED']['UserCommentEncoding']) !== 'UNDEFINED')
                {
                    $encoding = $data['COMPUTED']['UserCommentEncoding'];
                }
                break;
            case (   isset($data['EXIF'])
                  && isset($data['EXIF']['UserComment'])
                  && !empty($data['EXIF']['UserComment'])):
                $description = $data['EXIF']['UserComment'];
                break;
            // TODO: Other sources for description ?
            default:
                $description = false;
                break;
        }
        if (   !$encoding
            && function_exists('mb_detect_encoding')
            && isset($data['COMPUTED'])
            && isset($data['COMPUTED']['UserComment']))
        {
            $encoding = mb_detect_encoding($data['COMPUTED']['UserComment'], 'ASCII,JIS,UTF-8,ISO-8859-1,EUC-JP,SJIS');
        }
        if (   $encoding
            && function_exists('iconv'))
        {
            $encoding_lower = strtolower($encoding);
            $this_encoding_lower = strtolower($this->encoding);
            if (   $encoding_lower == $this_encoding_lower
                || (   $encoding_lower == 'ascii'
                    /* ASCII is a subset of the following encodings, and thus requires no conversion to them */
                    && (   $this_encoding_lower == 'utf-8'
                        || $this_encoding_lower == 'iso-8859-1'
                        || $this_encoding_lower == 'iso-8859-15')
                    )
                )
            {
                // No conversion required
            }
            else
            {
                $stat = @iconv($encoding_lower, $this_encoding_lower . '//TRANSLIT', $description);
                if (!empty($stat))
                {
                    $description = $stat;
                }
            }
        }

        $this_description = trim($this->description);
        if (   !empty($description)
            && (   empty($this_description)
                || $overwrite))
        {
            $this->description = trim($description);
        }
    }

    function _exif_tagback()
    {
        // TBD: In case we have changed something that should be written back to EXIF headers
    }

    function _link_to_galleries()
    {
        // TBD: Check if we need to add this photo to any dynamic galleries
    }

    /**
     * Shorthand for getting the next photo
     *
     * @access public
     * @static
     * @param mixed $photo        Either id or GUID of the photo or the org_routamc_photostream_photo_dba object itself
     * @param string $direction   < or >, depending on the wished direction
     * @param array $limiters     Array of limiters (keys type, tags, user, start and end)
     * @param array $tags_shared  Shared tags, pass by reference
     */
    function get_next($photo, $limiters = false, &$tags = false)
    {
        $guids = org_routamc_photostream_photo_dba::get_surrounding_photos($photo, '>', $limiters, &$tags);

        if (   $guids
            && isset($guids[0]))
        {
            return $guids[0];
        }

        return false;
    }

    /**
     * Shorthand for getting the previous photo
     *
     * @access public
     * @static
     * @param mixed $photo        Either id or GUID of the photo or the org_routamc_photostream_photo_dba object itself
     * @param string $direction   < or >, depending on the wished direction
     * @param array $limiters     Array of limiters (keys type, tags, user, start and end)
     * @param array $tags_shared  Shared tags, pass by reference
     */
    function get_previous($photo, $limiters = false, &$tags = false)
    {
        $guids = org_routamc_photostream_photo_dba::get_surrounding_photos($photo, '<', $limiters, &$tags);

        if (   $guids
            && isset($guids[0]))
        {
            return $guids[0];
        }

        return false;
    }

    /**
     * Get the next and previous photo guids
     *
     * @access public
     * @static
     * @param mixed $photo        Either id or GUID of the photo or the org_routamc_photostream_photo_dba object itself
     * @param string $direction   < or >, depending on the wished direction
     * @param array $limiters     Array of limiters (keys type, tags, user, start and end)
     * @param array $tags_shared  Shared tags, pass by reference since it can be used more often than once
     */
    function get_surrounding_photos($photo, $direction, $limiters_temp = false, &$tags_shared = false)
    {
        if (mgd_is_guid($photo))
        {
            $photo = new org_routamc_photostream_photo_dba($photo);
        }

        if (   !isset($photo->guid)
            || !$photo->guid)
        {
            return false;
        }

        if (!$tags_shared)
        {
            $tags_shared = array();
        }

        // Initialize the filters
        $limiters = array
        (
            'type' => '',
            'tags' => '',
            'user' => '',
            'start' => '',
            'end' => '',
            'limit' => 1,
        );

        // Get the filters
        foreach ($limiters_temp as $key => $value)
        {
            $limiters[$key] = $value;
        }

        $data['suffix'] = '';
        $guids = array();

        // Initialize the collector
        // Patch to the collector bug of forced second parameter
        $mc = org_routamc_photostream_photo_dba::new_collector('sitegroup', $_MIDGARD['sitegroup']);

        // Add first the common constraints
        $mc->add_value_property('title');

        if ($direction === '<')
        {
            $mc->add_constraint('taken', '<', $photo->taken);
            $mc->add_order('taken', 'DESC');
        }
        else
        {
            $mc->add_constraint('taken', '>', $photo->taken);
            $mc->add_order('taken');
        }

        $mc->add_constraint('id', '<>', $photo->id);

        $mc->add_constraint('node', '=', $photo->node);

        $mc->set_limit($limiters['limit']);

        // Check the corresponding limiter actions
        if ($limiters['type'])
        {
            switch ($limiters['type'])
            {
                case 'tag':
                    // Get a list of guids that share the requested tag
                    $mc_tag = net_nemein_tag_link_dba::new_collector('fromClass', 'org_routamc_photostream_photo_dba');
                    $mc_tag->add_value_property('fromGuid');
                    $mc_tag->add_constraint('tag.tag', '=', $limiters['tag']);
                    $mc_tag->add_constraint('fromGuid', '<>', $photo->guid);
                    $mc_tag->execute();

                    $tags = $mc_tag->list_keys();

                    // Initialize the array
                    $tags_shared = array();

                    // Store the object guids for later use
                    foreach ($tags as $guid => $array)
                    {
                        $tags_shared[] = $mc_tag->get_subkey($guid, 'fromGuid');
                    }

                    // Fall through

                case 'user':
                    if ($limiters['user'] !== 'all' && !empty($limiters['user']))
                    {
                        $mc_person = midcom_db_person::new_collector('username', $limiters['user']);
                        $mc_person->add_value_property('id');
                        $mc_person->add_constraint('username', '=', $limiters['user']);
                        $mc_person->set_limit(1);
                        $mc_person->execute();

                        $persons = $mc_person->list_keys();

                        foreach ($persons as $guid => $array)
                        {
                            $id = $mc_person->get_subkey($guid, 'id');
                            $mc->add_constraint('photographer', '=', $id);
                            break;
                        }
                    }
                    break;

                case 'between':
                    $start = @strtotime($limiters['start']);
                    $end = @strtotime($limiters['end']);

                    if ($limiters['user'])
                    {
                        // Add the person delimiter
                        $mc_person = midcom_db_person::new_collector('username', $limiters['user']);
                        $mc_person->add_value_property('id');
                        $mc_person->add_constraint('username', '=', $limiters['user']);
                        $mc_person->set_limit(1);
                        $mc_person->execute();

                        $persons = $mc_person->list_keys();

                        foreach ($persons as $guid => $array)
                        {
                            $id = $mc_person->get_subkey($guid, 'id');
                            break;
                        }

                        $mc->add_constraint('photographer', '=', $id);
                    }

                    if (   !$start
                        || !$end)
                    {
                        return false;
                    }

                    $mc->add_constraint('taken', '>=', $start);
                    $mc->add_constraint('taken', '<=', $end);

                    break;

                case 'all':
                default:
                    // TODO - anything needed?
            }
        }

        // Include the tag constraints
        if (count($tags_shared) > 0)
        {
            if (count($tags_shared) > 0)
            {
                $mc->begin_group('OR');
                foreach ($tags_shared as $guid)
                {
                    $mc->add_constraint('guid', '=', $guid);
                }
                $mc->end_group();
                $link = $mc->list_keys();
            }
            else
            {
                $link = array();
            }
        }
        else
        {
            $mc->execute();
            $link = $mc->list_keys();
        }

        // Initialize the array for returning
        $guids = array ();

        foreach ($link as $guid => $array)
        {
            $guids[] = $guid;
        }

        if (count($guids) > 0)
        {
            return $guids;
        }

        return false;
    }
}
?>