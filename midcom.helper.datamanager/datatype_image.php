<?php

class midcom_helper_datamanager_datatype_image extends midcom_helper_datamanager_datatype_blob {
    
    var $_thumbheight;
    var $_thumbwidth;
    var $_thumbjpegqual;
    var $_filterfunction;
    
    function _constructor (&$datamanager, &$storage, $field) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (!array_key_exists("widget", $field) || substr($field["widget"],0,5) != "image")
        {
            $field["widget"] = "image";
        }
            
        if (!array_key_exists("datatype_image_thumbwidth", $field))
        {
            $field["datatype_image_thumbwidth"] = 175;
        }
        if (!array_key_exists("datatype_image_thumbheight", $field))
        {
            $field["datatype_image_thumbheight"] = 175;
        }
        if (!array_key_exists("datatype_image_thumbjpegqual", $field))
        {
            $field["datatype_image_thumbjpegqual"] = 85;
        }
        if (!array_key_exists("datatype_image_filterfunction", $field))
        {
            $field["datatype_image_filterfunction"] = null;
        }
        if (!array_key_exists("datatype_blob_autoindex", $field))
        {
            $field["datatype_blob_autoindex"] = false;
        }
                
        $this->_thumbwidth = $field["datatype_image_thumbwidth"];
        $this->_thumbheight = $field["datatype_image_thumbheight"];
        $this->_thumbjpegqual = $field["datatype_image_thumbjpegqual"];
        
        parent::_constructor ($datamanager, $storage, $field);
        
        debug_pop();
    }

    function _update_value($attid) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        parent::_update_value($attid);
        
        if (is_null($this->_value)) 
        {
            debug_add("Value is null");
            debug_add("Leaving");
            debug_pop();
            return;
        }
        
        $att = new midcom_baseclasses_database_attachment($attid);
        
        // Load data from the thumbnail here
        $thumbguid = $att->parameter("midcom.helper.datamanager.datatype.image","thumbguid");
        
        debug_add("Trying to load thumbnail with attachment guid {$thumbguid}");
        $att = new midcom_baseclasses_database_attachment($thumbguid);
        
        if ((!$thumbguid) || (!$att)) 
        {
            debug_add("Could not load Thumbnail Attachment, setting it to NULL and aborting. Error was:" . mgd_errstr());
            $this->_value["thumbnail"] = null;
            debug_add("Leaving");
            debug_pop();
            return;
        }
        
        $stat = $att->stat();
        $this->_value["thumbnail"] = Array();
        $this->_value["thumbnail"]["filename"] = $att->name;
        $this->_value["thumbnail"]["mimetype"] = $att->mimetype;
        $this->_value["thumbnail"]["description"] = $this->_value["description"];
        $this->_value["thumbnail"]["url"] = $this->_anchorprefix . "midcom-serveattachmentguid-" . $att->guid() . "/" . $att->name;
        $this->_value["thumbnail"]["filesize"] = $stat[7];
        $this->_value["thumbnail"]["lastmod"] = $stat[9];
        $this->_value["thumbnail"]["formattedsize"] = $this->_format_filesize($stat[7]);
        $this->_value["thumbnail"]["isoformattedlastmod"] = strftime("%Y-%m-%d %T",$stat[9]);
        $this->_value["thumbnail"]["id"] = $att->id;
        $this->_value["thumbnail"]["guid"] = $att->guid;
        $this->_value["thumbnail"]["size_y"] = $att->parameter("midcom.helper.datamanager.datatype.blob","size_y");
        $this->_value["thumbnail"]["size_x"] = $att->parameter("midcom.helper.datamanager.datatype.blob","size_x");
        $this->_value["thumbnail"]["size_line"] = $att->parameter("midcom.helper.datamanager.datatype.blob","size_line");
        
        debug_pop();
    }

    function _delete_attachment ($id) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        global $midcom_errstr;
        
        $attachment = new midcom_baseclasses_database_attachment($id);
        if (!$attachment) 
        {
            debug_add("Could not open attachment, seems that it isn't here, so we're fine. Error was: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_add("Leaving", MIDCOM_LOG_DEBUG);
            debug_pop();
            return true;
        }
        
        // Delete Thumbnail if there is one.
        $guid = $attachment->parameter("midcom.helper.datamanager.datatype.image","thumbguid");
        $att = new midcom_baseclasses_database_attachment($guid);
        
        if ($att->id == $attachment->id) 
        {
            debug_add("Attachment is its own Thumbnail, so don't delete 'Thumbnail'...", MIDCOM_LOG_DEBUG);
        } 
        else 
        {
            if ($guid && $att) 
            {
                debug_add("We should delete Thumbnail Attachment $att->id", MIDCOM_LOG_DEBUG);
                if (! parent::_delete_attachment ($att->id)) 
                {
                    debug_add("Could not delete thumbnail attachment $att->id, aborting. See above error for details", MIDCOM_LOG_ERROR);
                    debug_add("Leaving", MIDCOM_LOG_DEBUG);
                    debug_pop();
                    return false;
                }
            }
        }
        
        debug_add("Now we delete the regular attachment $id", MIDCOM_LOG_DEBUG);
        $result = parent::_delete_attachment($id);
        debug_add("Parent returned " . ($result ? "true" : "false"), MIDCOM_LOG_DEBUG);
        
        debug_pop();
        return $result;
    }
    
    function _handle_upload ($params, $meta) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // where temporary files are stored, usually /tmp 
        $tmp_dir = "/tmp";
       
        // additional options for the "convert" command to be used when 
        // converting the image: Drops any ICC profile, web browsers don't
        // listen to them anyway
        $convert_options = '+profile "*"';
        
        // additional options for the "convert" command for creating
        // the thumbnail, Drops the ICC profile and sets the thumbnailquality according
        // to the config
        $convert_options_thumb = '+profile "*" -quality ' . (int) $this->_thumbjpegqual;

        $id = parent::_handle_upload($params, $meta);
        if (!$id) 
        {
            debug_add("Aborting, as parent method returned false. See above for details", MIDCOM_LOG_ERROR);
            debug_add("Leaving", MIDCOM_LOG_DEBUG);
            debug_pop();
            return false;
        }
        
        $att = new midcom_baseclasses_database_attachment($id);
        
        $storetype = $meta["storetype"];
        $thumbtype = $meta["thumbtype"];
        debug_add("storetype = " . $storetype, MIDCOM_LOG_DEBUG);
        debug_add("thumbtype = " . $thumbtype, MIDCOM_LOG_DEBUG);
        
        // store image as-is, no thumbnail etc. 
        
        if ($thumbtype == "asis") 
        {
            debug_add("Storing thumbnail as-is, no further conversion.", MIDCOM_LOG_DEBUG);
            // thumbguid == attachment guid
            // image is its own thumbnail and its own parent 
            $att->parameter("midcom.helper.datamanager.datatype.image","thumbguid", $att->guid());
            $att->parameter("midcom.helper.datamanager.datatype.image","parent_guid",$att->guid());

            debug_pop();
            return $id;  
        }
        
        // get mimetype and mimetype to convert to 
        $file_orig = $params["tmp_name"];
        $orig_mimetype = exec("{$GLOBALS['midcom_config']['utility_file']} -ib {$file_orig} 2>/dev/null");
        debug_add("original mimetype: {$orig_mimetype}", MIDCOM_LOG_DEBUG);
        
        // auto detect mime type and file extension
        switch ($orig_mimetype) 
        {
            case "image/png":
            case "image/gif":
            case "application/postscript":
            case "application/pdf":
                $auto_ext = "png";
                $auto_mimetype = "image/png";
                break;
                
            default:
                $auto_ext = "jpg";
                $auto_mimetype = "image/jpeg";
        }

        debug_add("auto detected mimetype: " . $auto_mimetype, MIDCOM_LOG_DEBUG);
        debug_add("auto detected extension: " . $auto_ext, MIDCOM_LOG_DEBUG);

        switch ($storetype) 
        {
            case "":
                // auto detect
                $new_ext = $auto_ext;
                $new_mimetype = $auto_mimetype;
                break;
                    
            case "png":
                $new_ext = "png";
                $new_mimetype = "image/png";
                break;
                
            case "jpeg":
                $new_ext = "jpg";
                $new_mimetype = "image/jpeg";
                break;
                
            case "asis":
            default:
                $new_ext = "";
                $new_mimetype = "";
            
        }
        
        debug_add("new mimetype: " . $new_mimetype, MIDCOM_LOG_DEBUG);
        debug_add("new extension: " . $new_ext, MIDCOM_LOG_DEBUG);
        
        switch ($thumbtype) 
        {
            case "":
                // auto detect
                $thumb_ext = $auto_ext;
                $thumb_mimetype = $auto_mimetype;
                break;
                
            case "png":
                $thumb_ext = "png";
                $thumb_mimetype = "image/png";
                break;
                
            case "jpeg":
                $thumb_ext = "jpg";
                $thumb_mimetype = "image/jpeg";
                break;
                
            case "asis":  // <-- this IS already handled!
            default:
        }
        
        debug_add("thumb mimetype: " . $thumb_mimetype, MIDCOM_LOG_DEBUG);
        debug_add("thumb extension: " . $thumb_ext, MIDCOM_LOG_DEBUG);
        
        $file_base = preg_replace("/\.(\w+)$/", "", basename($params["name"]));
        
        $file_tmp_orig = $tmp_dir."/".$file_base.".".$new_ext;
        $file_tmp_thumb = $tmp_dir."/thumb_".$file_base.".".$thumb_ext;
        
        if (   $new_mimetype != $orig_mimetype
            && $storetype != "asis")
        {
            // convert original and overwrite attachment
            debug_add("convert original and overwrite attachment...", MIDCOM_LOG_DEBUG);           
            
            // the "[0]" in the command line makes convert take only the first frame of an animated gif file...
            $cmd = "{$GLOBALS['midcom_config']['utility_imagemagick_base']}convert $convert_options ". escapeshellarg($file_orig."[0]") . " ".escapeshellarg($file_tmp_orig);
            debug_add("Executing: $cmd", MIDCOM_LOG_DEBUG);
            exec($cmd);
            
            $dest = $att->open();
            if (!$dest) 
            {
                $midcom_errstr = "Could not open File Attachment for writing: " . mgd_errstr();
                debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                debug_pop();
                return $id;
            }
            $source = fopen($file_tmp_orig,"r");
            if (!$source) 
            {
                $midcom_errstr = "Could not open converted image for reading, aborting thumbnail creation.";
                debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
                fclose ($dest);
                mgd_delete_attachment($thumb_id);
                debug_pop();
                return false;
            }
            while (! feof($source))
            {
                fwrite($dest, fread($source, 100000));
            }
        
            fclose($dest);
            fclose($source);
            
            $att->mimetype = $new_mimetype;
            $att->name = preg_replace("/\.(\w+)$/", "", $att->name).".".$new_ext;
            $att->update();
        }
        
        
        // get original dimensions and determine format to convert to 
        
        $size = getimagesize($file_orig);
        if ($size) 
        {
            $orig_x = $size[0];
            $orig_y = $size[1];
            debug_add("original size: {$orig_x}x{$orig_y}");
        } 
        else 
        {
            $orig_x = false;
            $orig_y = false;
            debug_add("Could not get image data of original file.");
        }
  
        // create thumbnail 
        $cmd = "{$GLOBALS['midcom_config']['utility_imagemagick_base']}convert {$convert_options_thumb} "
            . "-geometry '{$this->_thumbwidth}x{$this->_thumbheight}>' " 
            . escapeshellarg("{$file_orig}[0]") 
            . ' ' .escapeshellarg($file_tmp_thumb);
        debug_add("Executing: $cmd");
        exec($cmd);

        $size = getimagesize($file_tmp_thumb);
        if ($size) 
        {
            $new_x = $size[0];
            $new_y = $size[1];
            debug_add("thumbnail size: {$new_x}x{$new_y}");
        } 
        else 
        {
            $new_x = false;
            $new_y = false;
            debug_add("Could not getimagesize of thumbnail.", MIDCOM_LOG_WARN);
        }
        
        // now store the thumbnail as attachment 
        
        $filename = basename($file_tmp_thumb);
       
        if ($this->_storage->getattachment($filename)) 
        {
            $midcom_errstr = "A file with the thumbnail file name already exists.";
            debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return $id;
        }
        
        $thumb_att = $this->_storage->create_attachment($filename, $att->title, $new_mimetype);
        if (!$thumb_att) 
        {
            $midcom_errstr = "Could not create Thumbnail File Attachment: " . mgd_errstr();
            debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return $id;
        }
        
        $dest = $thumb_att->open();
        if (!$dest) 
        {
            $midcom_errstr = "Could not open Thumbnail File Attachment for writing: " . mgd_errstr();
            debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return $id;
        }
        $source = fopen($file_tmp_thumb,"r");
        if (!$source) 
        {
            $midcom_errstr = "Could not open scaled image for reading, aborting thumbnail creation.";
            debug_add($midcom_errstr, MIDCOM_LOG_ERROR);
            fclose ($dest);
            mgd_delete_attachment($thumb_id);
            debug_pop();
            return $id;
        }
        while (! feof($source))
        { 
            fwrite($dest, fread($source, 100000));
        }
        
        // clean up
        fclose($dest);
        fclose($source);
        @unlink($file_tmp_orig);
        @unlink($file_tmp_thumb);
        
        // add parameters to thumbnail attachment
        $thumb_att->parameter("midcom.helper.datamanager.datatype.image","parent_guid",$att->guid());
        if ($new_x && $new_y) 
        {
            $thumb_att->parameter("midcom.helper.datamanager.datatype.blob","size_x",$new_x);
            $thumb_att->parameter("midcom.helper.datamanager.datatype.blob","size_y",$new_y);
            $thumb_att->parameter("midcom.helper.datamanager.datatype.blob","size_line","width=\"$new_x\" height=\"$new_y\"");
        }
        $att->parameter("midcom.helper.datamanager.datatype.image","thumbguid",$thumb_att->guid());
        
        debug_pop();
        return $id;       
    }

}

?>