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
     * Database encoding, convert all auto-read data to this.
     */
    var $encoding = 'UTF-8';

    function org_routamc_photostream_photo_dba($id = null)
    {
        return parent::__org_routamc_photostream_photo_dba($id);
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
        $this->read_exif_data();
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
        // Detect 1.8 vs 1.7 by precense of this writing of QB
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
     * @param $call_update boolean call $this->update() if we change the links
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
        // Detect 1.8 vs 1.7 by precense of this writing of QB
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
            // Detect 1.8 vs 1.7 by precense of this writing of QB
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
     * Create a working copy of the attachemnet, optionally only first X kB of it
     *
     * The option to copy only part of file is mainly for reading EXIF data which is in the headers
     * of the file, and thus only a few first kB are needed for it.
     *
     * @param $att ID of attachment object
     * @param $size kB to copy, if value is less than one then full file is copied
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
     * @param $overwrite boolean overwrite current values with ones we could parse from file even if they exist (defaults to false)
     */
    function read_exif_data($overwrite = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!function_exists('read_exif_data'))
        {
            // TODO: Use some CLI tool if availbale
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

        // Read photo taken time
        $this->_read_exif_data_taken($data, $overwrite);

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

    function _read_exif_data_taken(&$data, &$overwrite)
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

    function _read_exif_data_camera(&$data, &$overwrite)
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
           TODO: Figure out a sensible way (esp if owner is not set...) to identify the invidual camera
           OTOH: if we have owner and firmare along with make/model we can quite reliably use this to identify the
           photographer.
        */
    }

    function _read_exif_data_description(&$data, &$overwrite)
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
}
?>