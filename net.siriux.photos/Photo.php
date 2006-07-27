<?php
/**
 * @package net.siriux.photos
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Photo class
 * 
 * @todo document
 * 
 * @author Eero af Heurlin, Nemein Oy <eero.afheurlin@nemein.com>
 * @author Nico Kaiser <nico@siriux.net>
 * @author Torben Nehmer <torben@nehmer.net>
 * @version 0.18 (2004-04-14)
 * @package net.siriux.photos
 */
class siriux_photos_Photo {

    var $_config;
    var $_l10n;
    var $_l10n_midcom;
    
    var $datamanager;
    var $article;

    var $fullscale;             // GUID, guid of the attachment holding the full-scale (or original) photo
    var $view;                  // GUID, guid of the attachment holding a viewing-scale version of the original
    var $thumbnail;             // GUID, guid of the attachment holding a thumbnail of the photo
    
    var $errstr;
    
    
    /**
     * Creates a new instance and get Photo by id
     * 
     * If $config is set, the component context is ignored. Configuration is used
     * directly, and the L10n libs are fetched from MidCOM.
     * 
     * @param int id (opt.)
     * @param midcom_helper_configuration An explicit configuration, required for usage outside of 
     *     the normal component context. Can be left empty normally.
     * @param MidgardTopic $topic can be used for overriding the current gallery topic, defaults to
     *     MIDCOM_CONTEXT_CONTENTTOPIC
     */
    function siriux_photos_Photo($id = FALSE, $config = null, $topic = null) {
        
        if (is_null($topic))
        {
        	   $this->_topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
        }
        else
        {
            $this->_topic = $topic;
        }
        
        if (is_null($config))
        {
            $this->_config =& $GLOBALS["midcom"]->get_custom_context_data("configuration");
            $this->_l10n =& $GLOBALS["midcom"]->get_custom_context_data("l10n");
            $this->_l10n_midcom =& $GLOBALS["midcom"]->get_custom_context_data("l10n_midcom");
        }
        else
        {
            $this->_config = $config;
            $i18n =& $GLOBALS['midcom']->get_service('i18n');
            $this->_l10n = $i18n->get_l10n('net.siriux.photos');
            $this->_l10n_midcom = $i18n->get_l10n('midcom');
        }
        
        $this->errstr =& $GLOBALS["midcom"]->get_custom_context_data("errstr");
        
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->datamanager = new midcom_helper_datamanager($this->_config->get("schemadb"));
        if (! $this->datamanager) {
            debug_add("Failed to create a datamanager instance, see above for details.");
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "siriux_photos_Photo: Failed to create a datamanger instance, this is fatal.");
            /* This will exit() */
        }
        
        
        if ($id)
        {
            if (!$this->get($id))
            {
                return false;
            }
        }
        else 
        {
            $this->id = FALSE;
            $this->gallery = FALSE;
            $this->name = FALSE;
            $this->type = FALSE;
            $this->title = FALSE;
            $this->description = FALSE;
            $this->abstract = FALSE;
            $this->photographer = FALSE;
            $this->fullscale = FALSE;
            $this->view = FALSE;
            $this->thumbnail = FALSE;
            $this->approved = FALSE;
            $this->keywords = FALSE;
            $this->taken = FALSE;
            $this->score = FALSE;
        }
    }


    /**
     * Gets a Photo by id or by object
     * @param mixed id The ID or MidgardArticle object to use.
     */
    function get($id) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (is_object($id) && is_a($id, 'MidgardArticle'))
        {
            $article = $id;
        }
        else
        {
	        $article = mgd_get_article($id);
	        if (! $article) 
            {
	            $this->errstr = "Could not get Article '{$id}': " . mgd_errstr();
                debug_add($this->errstr, MIDCOM_LOG_WARN);
                debug_pop();
	            return FALSE;
	        }
        }
        
        $this->id = $article->id;
        $this->gallery = $article->topic;
        $this->name = $article->name;
        $this->type = $article->type;
        $this->title = $article->title;
        $this->description = $article->content;
        $this->abstract = $article->abstract;
        $this->photographer = $article->extra1;
        
        $this->approved = $article->approved;
        $this->keywords = $article->extra3;
        $this->taken = $article->url;
        $this->score = $article->score;
        
        /*** old code above ***/
        
        $this->article = $article;
        if (! $this->datamanager->init($this->article)) 
        {
            $this->errstr = "Could not initialize the Datamanger. Aborting.";
            debug_add($this->errstr, MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        
        $scales = explode("|", $this->datamanager->data["scales"]);
        debug_print_r("Got these scales:", $scales);
        if (is_array($scales)) 
        {
            /* Check for missing images here */
            if (array_key_exists(0, $scales))
            {
                $this->fullscale = $scales[0];
            }
            else
            {
                $this->fullscale = null;
            }
            
            if (array_key_exists(1, $scales))
            {
                $this->view = $scales[1];
            }
            else
            {
                $this->view = null;
            }
            
            if (array_key_exists(2, $scales))
            {
                $this->thumbnail = $scales[2];
            }
            else
            {
                $this->thumbnail = null;
            }
        }
        
        debug_pop();
        return TRUE;
    }


    /** 
     * Gets a Photo by GUID
     */
    function get_by_guid($guid) {
        $object = mgd_get_object_by_guid($guid);
        if ($object && $object->__table__ = "article")
            return $this->get($object->id);
        else {
            $this->errstr = "Could not get Article '$guid'";
            debug_print_r("Could not get image article {$guid}, got error " . mgd_errstr() 
                . " and this object: ", $object, MIDCOM_LOG_INFO);
            return FALSE;
        }
    }
    
    /**
     * Creates a new photo based on a midcom datamanager driven form.
     * see the admin class for the big picture.
     *
     * You should be aware, that this method will use a different schema
     * then the upload form, as the upload form is a subset of the real
     * data schema. This is especially important for the bulk upload 
     * feature.
     * 
     * @param string filename    The full temporary filename of the uploaded file.
     * @param realname realname     The real name of the uploaded file.
     * @returns true on success, false otherwise, with an error in errstr.
     */
    function create_from_datamanager($filename, $realname) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Strip the extension, if present
        $lastdot = strrpos($realname, '.');
        if ($lastdot  === false)
        {
            $name = $realname;
        }
        else
        {
            $name = substr($realname, 0, $lastdot);
        }
        
        /* Check, whether the basename is already used */
        $i = 0;
        while (mgd_get_article_by_name($this->_topic->id, $name) !== false) 
        {
            $i++;
            $parts = explode(".", $realname);
            $count = count($parts);
            if ($count == 1) 
            {
                $parts[0] .= "_{$i}";
            } 
            else 
            {
                $parts[$count - 2] .= "_{$i}";
            }
            $name = implode(".", $parts);
        }
        
        /* Create an empty article first */
        $midgard = mgd_get_midgard();
        $article = mgd_get_article();
        $article->topic = $this->_topic->id;
        $article->author = $midgard->user;
        $article->name = midcom_generate_urlname_from_string($name);
        
        $id = $article->create();
        if (! $id) 
        {
            $this->errstr = "Failed to create an empty article: " . mgd_errstr();
            debug_add($this->errstr, MIDCOM_LOG_ERROR);
            debug_print_r("Article object was:", $article);
            debug_pop();
            return false;
        }
        debug_add("Created article ID {$id}");
        
        $this->article = mgd_get_article($id);
        
        /*** Store the datamanager stuff ***/
        $this->article->parameter("midcom.helper.datamanager", "layout", $this->_config->get("schemadb_picture"));
        if (! $this->datamanager->init($this->article)) 
        {
            debug_add("Failed to initialize the datamanager.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        switch ($this->datamanager->process_form()) 
        {
            case MIDCOM_DATAMGR_EDITING:
                debug_add("Note, that the DM returned edititng state. We ignore this deliberatly, treating as SAVED.",
                    MIDCOM_LOG_WARN);
                /* Fall-Through to MIDCOM_DATAMGR_SAVED*/
                
            case MIDCOM_DATAMGR_SAVED:
                break;
            
            case MIDCOM_DATAMGR_CANCELLED:
                debug_add("Datamanager returned CANCELLED state, this is a bug, and must not happen.",
                    MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
                
            case MIDCOM_DATAMGR_FAILED:
                debug_add("Datamanger failed to process the upload. Aborting.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
                
            default:
                debug_add("Datamanger returnd an unknown state. Aborting.", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
        }

        /*** Process the uploaded file and update the article accordingly ***/
        if ($this->_config->get("filter_chain") != "") 
        {
            debug_add("Filter chain is set, executing");
            $filter = new net_siriux_photos_imagefilter();
            if (!$filter->set_file($filename)) 
            {
                $this->errstr = "Failed to apply post processing filters: set_file failed.";
                debug_add($this->errstr, MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            if (!$filter->process_chain($this->_config->get("filter_chain")))
            {
                $this->errstr = "Failed to apply post processing filters: proces_chain failed.";
                debug_add($this->errstr, MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }
        
        $this->fullscale = $this->_storeImageAttachment($filename, "fullscale_{$name}");
        $this->_generateImages($filename, $name);
        
        $this->_getExifData($filename);
        
        if (! $this->_storeImageGuids())
        {
            debug_pop();
            return false;
        }

        $this->index();
        
        debug_pop();
        return true;
    }
        
    /**
     * Generatres the preview and thumbnail JPEG image attachements.
     *
     * @param string filename  The file on-disk to use
     * @param string name      The name to use for creating the attachment.
     */
    function _generateImages ($filename, $name) 
    {
        /* Resize (if neccessary) and attach the view scale image */
        if ($this->_checkForResize($filename, $this->_config->get("view_x"), $this->_config->get("view_y"))) 
        {
            $this->view = $this->_resizeAndStoreImage($filename, $this->_config->get("view_x"), 
                $this->_config->get("view_y"), "view_{$name}");
            
            if (! $this->view) 
            {
                $this->errstr .= "Failed to convert the upload to a preview JPEG image. Setting it to NULL.<br/>\n";
                debug_add("Could not create preview JPEG for {$name}.", MIDCOM_LOG_ERROR);
                $this->view = "";
            }
            
        } 
        else 
        {
            // just use the original, it's small enough anyway
			$this->view = $this->fullscale;
        }
        
        /* Resize (if neccessary) and attach the thumbnail image */
        if ($this->_checkForResize($filename, $this->_config->get("thumb_x"), $this->_config->get("thumb_y"))) 
        {
            $this->thumbnail = $this->_resizeAndStoreImage($filename, $this->_config->get("thumb_x"), 
                $this->_config->get("thumb_y"), "thumbnail_{$name}");
            
            if (! $this->thumbnail) 
            {
                $this->errstr .= "Failed to convert the upload to a thumbnail JPEG image. Setting it to NULL.<br/>\n";
                debug_add("Could not create thumbnail JPEG for {$name}.", MIDCOM_LOG_ERROR);
                $this->thumbnail = "";
            }
            
        } 
        else 
        {
            // just use the original, it's small enough anyway
			$this->thumbnail = $this->fullscale;
        }
    }
    
    /**
     * General helper, that stores an image file as attachment and sets
     * size parameters size.x, size.y, size.bytes, size.mimetype
     *
     * @param string filename_disk    Filename on disk (absolute path)
     * @param string filename_att     Filename of attachment
     * @return GUID of new attachment if successful, FALSE otherwise
     */
    function _storeImageAttachment ($filename_disk, $filename_att) 
    {
        $attid = $this->article->createattachment($filename_att, $filename_att, "");
        if (! $attid) {
            $this->errstr = "Could not create Attachment for Article '".$this->article->id."'";
            return FALSE;
        }
        $fh_to = $this->article->openattachment($filename_att, "w");
        if (! $fh_to) {
            $this->errstr = "Could not open Attachment '$filename_att' (Article '".$this->article->id."') for writing";
            return FALSE;
        }
        
        $fh_from = @fopen($filename_disk, "r");
        if (! $fh_from) {
            $this->errstr = "Could not open '$filename_disk'";
            return FALSE;
        }
        
        while (! feof($fh_from)) {
            $buffer = fread($fh_from, 131072); /* 128 kB */
            fwrite($fh_to, $buffer);
        }
        
        fclose($fh_from);
        fclose($fh_to);

        // Complete Attachment object.
        $attobj = mgd_get_attachment($attid);
        
        $attobj->parameter("size", "bytes", filesize($filename_disk));
        
        /* Try to get image sizes, fail silently */
        $sizes = @getimagesize($filename_disk);
        if ($sizes) 
        {
            $attobj->parameter("size", "x", $sizes[0]);
            $attobj->parameter("size", "y", $sizes[1]);
            if (function_exists("index_type_to_mime_type")) 
            {
                $attobj->mimetype = index_type_to_mime_type($sizes[2]);
            } 
            else 
            {
                switch ($sizes[2]) 
                {
                    case IMAGETYPE_GIF:
                        $attobj->mimetype = "image/gif";
                        break;
                    case IMAGETYPE_PNG:
                        $attobj->mimetype = "image/png";
                        break;
                    case IMAGETYPE_JPEG:
                        $attobj->mimetype = "image/jpeg";
                        break;
                    default:
                        $attobj->mimetype = "application/octet-stream";
                        break;
                }
            }
            $attobj->parameter("size", "mimetype", $attobj->mimetype);
        } 
        else 
        {
            $attobj->mimetype = "application/octet-stream";
        }
        $attobj->update();
        
        $guid = $attobj->guid();
        
        debug_print_r("This is the final attachment object (GUID: {$guid}):", $attobj);
        
        return $guid;
    }
    
    /**
     * General helper, that updates an image file in an existing attachment 
     * and resets size parameters size.x, size.y, size.bytes, size.mimetype
     *
     * @param string filename_disk    Filename on disk (absolute path)
     * @param MidgardAttachmenbt att_obj  The attachment to update
     * @return bool Indicating success
     */
    function _updateImageAttachment ($filename_disk, $attobj) {
        $fh_to = mgd_open_attachment($attobj->id, "w");
        if (! $fh_to) {
            $this->errstr = "Could not open Attachment '{$attobj->id}' (Article '{$this->article->id}') for writing";
            return FALSE;
        }
        
        $fh_from = @fopen($filename_disk, "r");
        if (! $fh_from) {
            $this->errstr = "Could not open '$filename_disk'";
            return FALSE;
        }
        
        while (! feof($fh_from)) {
            $buffer = fread($fh_from, 131072); /* 128 kB */
            fwrite($fh_to, $buffer, 131072);
        }
        
        fclose($fh_from);
        fclose($fh_to);

        // Complete Attachment object.
        $attobj->parameter("size", "bytes", filesize($filename_disk));
        
        /* Try to get image sizes, fail silently */
        $sizes = @getimagesize($filename_disk);
        if ($sizes) {
            $attobj->parameter("size", "x", $sizes[0]);
            $attobj->parameter("size", "y", $sizes[1]);
            if (function_exists("index_type_to_mime_type")) {
                $attobj->mimetype = index_type_to_mime_type($sizes[2]);
            } else {
                switch ($sizes[2]) {
                    case IMAGETYPE_GIF:
                        $attobj->mimetype = "image/gif";
                        break;
                    case IMAGETYPE_PNG:
                        $attobj->mimetype = "image/png";
                        break;
                    case IMAGETYPE_JPEG:
                        $attobj->mimetype = "image/jpeg";
                        break;
                    default:
                        $attobj->mimetype = "application/octet-stream";
                        break;
                }
            }
            $attobj->parameter("size", "mimetype", $attobj->mimetype);
        } else {
            $attobj->mimetype = "application/octet-stream";
        }
        $attobj->update();
        
        return true;
    }
    
    /**
     * Checks, if you need to resize the image file no meet the constraints
     * of the image. If the image can't be recognized by getimagesize, the
     * function will always return true to force an imagemagic conversion 
     * attempt.
     *
     * @param string filename    The full name of the file to be checked.
     * @param int width          The maximum width
     * @param int height         The maximum height
     * @returns true, if the image need to be rescaled
     */
    function _checkForResize ($filename, $width, $height) {
        $sizes = @getimagesize($filename);
        if (! is_array($sizes)) {
            debug_add("We must resize the image $filename, could not getimagesize. Let imagemagick think about it.");
            return true;
        }
        debug_add("Image size is {$sizes[0]}x{$sizes[1]}, checking against {$width}x{$height}.");
        if ($sizes[0] > $width || $sizes[1] > $height) {
            debug_add("We have to scale this.");
            return true;
        }
        
        debug_add("Size is OK.");
        return false;
    }
    
    /**
     * Scales the image in the Attachment to match the given constraints
     * and store ist as $attname on the current article.
     *
     * Images will be postprocessed with imagemagick using these scaling filters:
     * -unsharp 1x5 -modulate 100,110,100 -gamma 0.95
     *
     * @param string filename    The full name of the file to be checked.
     * @param int width          The maximum width
     * @param int height         The maximum height
     * @param string attname     The name of the attachment to create.
     * @return GUID of the created attachment on success, false on failure.
     */
    function _resizeAndStoreImage ($filename, $width, $height, $attname) {
        $geometry = escapeshellarg("{$width}x{$height}");
        $tmpfile = tempnam ("/tmp", "net_siriux_photos");
        $cmd = "{$GLOBALS['midcom_config']['utility_imagemagick_base']}convert " . escapeshellarg($filename) 
            . " -geometry $geometry -quality 80 +profile '*' -colorspace RGB"
            . ' -unsharp 1x5 -modulate 100,110,100 -gamma 0.95'
            . " jpeg:{$tmpfile}";
        
        debug_add("Executing: ${cmd}");
        exec($cmd, $output, $exit_code);
        debug_print_r("Imagemagick returned with {$exit_code} and produced this output:", $output);
        
        $result = $this->_storeImageAttachment($tmpfile, $attname);
        
        unlink($tmpfile);
        return $result;
    }
    
    /**
     * Stores the image GUIDs to the extra2 field of the current article.
     * They will be separated by pipes and in the order fullscale, view,
     * thumbnail.
     *
     * @returns true on success, false otherwise.
     */
    function _storeImageGuids() {
        $this->article->extra2 = "{$this->fullscale}|{$this->view}|{$this->thumbnail}";
        debug_add("Trying to save the GUIDs to extra2: {$this->article->extra2}");
        if (! $this->article->update()) {
            debug_print_r("Failed to save the Guids: " . mgd_errstr(), $this->article);
            return false;
        }
        return true;
    }
    
    
    /**
     * Writes a copy of the original image to a temporary file for
     * processing with tools like EXIF-Reader or imagemagick.
     *
     * The callee is responsible for unlinking the file after usage.
     * 
     * This is a wrapper for writeAttachmentTmpFile().
     * 
     * @return string filename
     * @see siriux_photos_Photo::writeAttachmentTmpFile()
     */
    function _writeFullscaleFile()
    {
        return $this->_writeAttachmentTmpFile($this->fullscale);
    }
    
    /**
     * Writes a copy of an image to a temporary file for
     * processing with tools like EXIF-Reader or imagemagick.
     *
     * The callee is responsible for unlinking the file after usage.
     * 
     * @param mixed $guid The Guid of the image to write out, defaults to the fullscale file. 
     * 					  A string is interpreted as a GUID, alternativly you can pass a MidgardAttachment object.
     * @return string filename
     */
    function _writeAttachmentTmpFile($attachment) {
        if (is_string($attachment)) 
        {
            $fsobj = mgd_get_object_by_guid($this->fullscale);
        }
        else if (is_a($attachment, 'MidgardAttachment'))
        {
            $fsobj = $attachment;
        }
        
        $fpin = mgd_open_attachment($fsobj->id, "r");
        if (! $fpin) 
        {
            $this->errstr = "Could not open fullscale Attachment '".$fsobj->id."'";
            return FALSE;
        }
        
        $file = tempnam("/tmp", "net_siriux_photos_");
        $fpout = fopen($file, "w");
        if (! $fpout) 
        {
            $this->errstr = "";
            return FALSE;
        }
        
        while (! feof($fpin)) 
        {
            $buffer = fread($fpin, 131072); /* 128 kB */
            fwrite($fpout, $buffer, 131072);
        }
        
        fclose($fpin);
        fclose($fpout);
        
        return $file;
    }
    
    /**
     * This function synchronizes the article created timestamp with the
     * value entered in the URL field, which holds the EXIF taken timestamp.
     * Synchronization is only done, if that timestamp is non-zero.
     */
    function syncArticleCreated($autoindex = true) 
    {
        if ($this->article && $this->article->url)
        {
            mgd_update_article_created($this->article->id, $this->article->url);
            // Update the index.
            if ($autoindex)
            {
                $this->index();
            }
        }
    }
    
    /**
     * This function rereads the EXIF data accociated with this image.
     * Any data currently set will be overwritten.
     *
     * If no image is set (e.g. empty or not loaded article), this function
     * will fail silently.
     */
    function rereadExif() {
        if (! $this->article) {
            debug_add("Cannot reread EXIF Data, we don't have an article set.", MIDCOM_LOG_WARN);
            return;
        }
        if (! $this->fullscale) {
            debug_add("Cannot reread EXIF Data, we don't have a fullscale image set for article {$this->article->id}", MIDCOM_LOG_WARN);
            return;
        }
        $this->_getExifData();
        
        // Update the Index.
        $this->index();
    }
    
    /**
     * Gets EXIF data from the fullscale image and stores it
     * in the Photo Article using the mapping configured in the component.
     *
     * The corresponding configuration value is of the form 
     * exiftag:dmfieldname. It supports only parameters as storage, so
     * if you write "FNumber:fnumber" it will take the value of the EXIF
     * Tag "FNumber" and store it in the parameter "data_fnumber" in the
     * Domain "midcom.helper.datamanager". You can then define a field
     * in the datamanager namend "fnumber" with the storage location
     * "parameter", and the DM will be able to work with it.
     *
     * The configuration key may have multiple, comma-separated pairs.
     *
     * Note, that the taken value, part of the default schema and stored
     * in the URL field of the record, will automatically be populated with
     * the timestamp stored in FileDateTime EXIF Tag.
     * 
     * This function requires the EXIF Functions of PHP available. 
     * (read_exif_data).
     * 
     * @param string file   Filename of file on disk (opt.). If it is omitted,
     *   the attachment will be written to a temporary file so that the various
     *   handler functions can work with it.
     * @return bool Indicating success.
     */
    function _getExifData($file = FALSE) 
    {
        if (! $file) 
        {
            $tmpfile = $this->_writeFullscaleFile();
            if (! $tmpfile)
                return FALSE;
        } 
        else 
        {
            $tmpfile = $file;
        }
        
        $mode = "timestamp";
        $have_jhead = ! is_null($GLOBALS['midcom_config']['utility_jhead']);
        $have_php = function_exists("read_exif_data");
        
        /* Detect operation mode, first check for PHP Version */
        if (substr(phpversion(),3) == "4.0" || version_compare(phpversion(), "4.2.0", "<")) 
        {
            /* We have a version piror 4.2.0. exifread might be broken there. */
            if ($have_jhead)
            {
                $mode = "jhead";
            }
            else if ($have_php)
            {
                $mode = "php";
            }
        } 
        else 
        {
            if ($have_php)
            {
                $mode = "php";
            }
            else if ($have_jhead)
            {
                $mode = "jhead";
            }
        }
        
        /* Check for an enforcement, but check wether this is actually available before
         * overriding auto-detect.
         */
        $force = $this->_config->get("force_exif_reader");
        if ($force == "php" && $have_php)
        {
            $mode = "php";
        }
        else if ($force == "jhead" && $have_jhead)
        {
            $mode = "jhead";
        }
        
        debug_add("Trying EXIF Reader mode $mode");
        
        switch ($mode) 
        {
            case "php":
                $this->_getExifDataPHP($tmpfile);
                if (! $this->_config->get("disable_autosync_created"))
                {
                    $this->syncArticleCreated(false);
                }
                break;
                
            case "jhead":
                $this->_getExifDataJhead($tmpfile);
                if (! $this->_config->get("disable_autosync_created"))
                {
                    $this->syncArticleCreated(false);
                }
                break;
                
            case "timestamp":
                $this->_getExifDataTimestamp($tmpfile);
                break;
                
            default:
                die ("Unknown EXIF Reader selected, this should not happen.");
        }
        
        /* In case the selected reader did not deliver anything, we
         * fall back to the timestamp method.
         */
        if (! $this->article->url && $mode != "timestamp") 
        {
            $this->_getExifDataTimestamp($tmpfile);
        }
        
        if (! $file && $tmpfile)
        {
            unlink($tmpfile);
        }
        
        return true;
    }
    
    /** 
     * This is the absolute Fallback in case no EXIF Readers are
     * available. It uses the current Timestamp as a value.
     *
     * Note, that since this only works during upload, not later,
     * the function will not overwrite any value already in place.
     *
     * Always safe.
     *
     * It does not support the exif mapping feature, only the
     * taken timestamp is extracted here.
     *
     * @param string file The file from which to get the EXIF data.
     */
    function _getExifDataTimestamp($tmpfile) {
        if ($this->article->url) {
            debug_add("The URL field is not empty, we already have a timestamp, doing nothing.");
            return;
        }
        $this->article->url = time();
        $this->article->update();
    }
    
    /** 
     * This is the JHead Version of the EXIF Extractor.
     * Safe to use prior to PHP 4.2.0
     *
     * It does not support the exif mapping feature, only the
     * taken timestamp is extracted here.
     *
     * @param string file The file from which to get the EXIF data.
     */
    function _getExifDataJhead($file) 
    {
        exec("{$GLOBALS['midcom_config']['utility_jhead']} " . escapeshellarg($file), $return, $status);
        $exif = Array ();
        if ($status == 0) 
        {
            foreach ($return as $key => $value)
            {
                if (trim($value)) 
                {
                    $explodeReturn = explode(':', $value, 2);
                    if (isset($exif[trim($explodeReturn[0])])) 
                    {
                        $exif[trim($explodeReturn[0])] .= "<br>" . trim($explodeReturn[1]);
                    } 
                    else 
                    {
                        $exif[trim($explodeReturn[0])] = trim($explodeReturn[1]);
                    }
                }
            }            
        } 
        else 
        {
            debug_add("No EXIF Data found, so not setting anything.");
            $taken = 0;
        }

        if (! array_key_exists("Date/Time", $exif)) 
        {
            debug_add("EXIF Date not found, so not setting it.");
            $taken = 0;
        }
        
        // replace silly colons in EXIF dates
        $date = preg_replace("/(\d{4}):(\d{2}):(\d{2}) (\d{2}):(\d{2}):(\d{2})/", '\1-\2-\3 \4:\5:\6', $exif["Date/Time"]);
        if (! $date) 
        {
            // unkown date format
            debug_add("Could not transform EXIF-Date into an ISO Timestamp, ignoring it.");
            $taken = 0;
        }
        
        $this->article->url = $taken;
        $this->article->update();
    }
    
    /** 
     * This is the PHP Version of the EXIF Extractor.
     * Safe to use as of PHP 4.2.0
     *
     * @param string tmpfile The file from which to get the EXIF data.
     */
    function _getExifDataPHP($tmpfile) {
        // Hide errors of unknown file types, we catch this later.
        $exif = @read_exif_data($tmpfile);
        debug_print_r("We got this EXIF data:", $exif);
        
        $taken_is_string = false;
        $taken = 0;
        
        if (!is_array($exif))
        {
            debug_add("The EXIF-Parser does not seem to support the file type of {$tmpfile}, creating an empty array to indicate this.");
            $exif = Array();
        }
        
        if (array_key_exists("DateTimeOriginal", $exif)) {
            $taken = $exif["DateTimeOriginal"];
            $taken_is_string = true;
        } else if (array_key_exists("DateTime", $exif)) {
            $taken = $exif["DateTime"];
            $taken_is_string = true;
        } else if (array_key_exists("FileDateTime", $exif)) {
            $taken = $exif["FileDateTime"];
        } else {
            // No known EXIF tag or unsupported file type. Setting taken to 0 to disable
            // the article timestamp sync function.
            $taken = 0;
        }
        
        if ($taken_is_string) {
            debug_add("We try to evaluate {$taken} to get a timestamp.");
            /* Replace colons with dashes */
            $taken = preg_replace("/(\d{4}):(\d{2}):(\d{2}) (\d{2}):(\d{2}):(\d{2})/", '\1-\2-\3 \4:\5:\6', $taken);
            if (! $taken) {
                debug_add("Could not transform EXIF-Date into an ISO Timestamp, ignoring it.");
                $taken = 0;
            }
            $taken = strtotime($taken);
        }
        
        // Photo can't be taken later than it was updated
        if ($taken > $this->article->created) {
          $taken = $this->article->created;
        }

        $this->article->url = $taken;
        $this->article->update();
        
        $exiftags = explode(",", $this->_config->get("exif_custom_fields"));
        
        foreach ($exiftags as $exiftag) {
            $tmp = explode(":", $exiftag);
            if (count($tmp) != 2) {
                debug_add("The EXIV Tag >{$exiftag}< could not be parsed, skipping it.", MIDCOM_LOG_WARN);
                continue;
            }
            $key = $tmp[0];
            $param = $tmp[1];
            if (! array_key_exists($key, $exif)) {
                debug_add("The Key $key does not exist in the EXIF array, skipping it.", MIDCOM_LOG_WARN);
                continue;
            }
            $value = $exif[$key];
            debug_add("Storing '{$value}' at the parameter 'data_{$param}' in the domain 'midcom.helper.datamanager'");
            $this->article->parameter("midcom.helper.datamanager", "data_{$param}", $value);
        }
    }
    

    /**
     * Deletes the current Photo article and all its attachments
     * @return bool   true if successful, FALSE otherwise
     */
    function delete() {
        $article = mgd_get_article($this->id);
        if (! $article) {
            $this->errstr = "Could not get Article '".$this->id."'";
            return FALSE;
        }

        // Save the GUID to update the Index later.
        $guid = $article->guid();
        
        if (!mgd_delete_extensions($article)) 
        {
            $this->errstr = "Could not delete Article $id extensions: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_add($this->_errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return FALSE;
        }

        if (!$article->delete()) 
        {
            $this->errstr = "Could not delete Article $id: ".mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_add($this->_errstr, MIDCOM_LOG_ERROR);
            debug_pop();
            return FALSE;
        }
        
        // Update the index
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        $indexer->delete($guid);
        
        return true;
    }


    /**
     * Returns the GUID of the current Photo article
     * @return GUID of the current Photo article
     */
    function guid() {
        return $this->article->guid();
    }


    /**
     * Approve the Photo using the Metadata framework.
     * 
     * This is a backwards compatibility function and should no longer be used.
     * 
     * @return TRUE on success, FALSE else
     * @deprecated Use the metadata framework instead.
     */
    function approve() 
    {
        $metadata =& midcom_helper_metadata::retrieve($this->article);
        return $metadata->approve();
    }

    /**
     * Unapprove the Photo using the metadata framework
     * 
     * This is a backwards compatibility function and should no longer be used.
     * 
     * @return TRUE on success, FALSE else
     * @deprecated Use the metadata framework instead.
     */
    function unapprove() 
    {
        $metadata =& midcom_helper_metadata::retrieve($this->article);
        return $metadata->unapprove();
    }
    
    /**
     * Rotate the view and thumbnail image. 
     * This does *not* affect the original image.
     * 
     * Any error will trigger an immediate error page.
     * 
     * @param int $degrees The amount in degrees the image should be rotated clockwise, negative amounts possible.
     */
    function rotate ($degrees)
    {
        debug_add('Photo:Rotate: Creating imagefilter...');
        $filter = new net_siriux_photos_imagefilter();
        
        foreach (Array($this->thumbnail, $this->view) as $guid)
        {
            $attachment = mgd_get_object_by_guid($guid);
            if (! $attachment)
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    'Could not open Photo attachment, this should not happen. Last Midgard error was: ' . mgd_errstr());
                // This will exit
            }
            
            $tmpfile = $this->_writeAttachmentTmpFile($attachment);
            if (! $tmpfile)
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    'ROTATE: Failed to generate temporary file.');
                // This will exit
            }
            
            debug_print_r("Setting image filter to temporary file {$tmpfile} of this attachment:", $attachment);
            $filter->set_file($tmpfile);
            if (! $filter->rotate($degrees))
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    'Failed to rotate image, see debug log for details.');
                // This will exit
            }
            
            debug_add('Updating Image attachment...');
            if (! $this->_updateImageAttachment($tmpfile, $attachment))
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    'Failed to update image Attachment.');
                // This will exit
            }
            
            unlink($tmpfile);
        }
        
        // Update the Index, the Thumbnail has changed.
        $this->index();
        
        debug_add('Photo:Rotate: Finshed.');
    }
    
    /**
     * This function will regenerate view and thumbnail images of the current
     * Photo.
     */
    function rebuildThumbs()
    {
        // Delete thumbnail and view if different images from the original fullscale one.
        if (! is_null($this->thumbnail) && $this->thumbnail != $this->fullscale)
        {
            if (! midcom_helper_purge_object($this->thumbnail))
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    "rebuildThumbs: Failed to delete thumbnail image {$this->thumbnail}: " . mgd_errstr());
                // This will exit
            }
        }
        if (! is_null($this->view) && $this->view != $this->fullscale)
        {
            if (! midcom_helper_purge_object($this->view))
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                    "rebuildThumbs: Failed to delete thumbnail image {$this->view}: " . mgd_errstr());
                // This will exit
            }
        }
        
        $tmpfile = $this->_writeAttachmentTmpFile($this->fullscale);
        // We have to fool ourselves a bit, attachment name handling is not really good at the moment.
        $this->_generateImages($tmpfile, $this->name);
        if (! $this->_storeImageGuids())
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "rebuildThumbs: Failed to update the Image GUIDS for Article {$this->article->id}: " . mgd_errstr());
            // This will exit
        }
        unlink($tmpfile);
        
        // Update the Index record, the GUID of the abstract has changed.
        $this->index();
    }
    
    /**
     * Indexes the article if the MidCOM indexer is enabled, otherwise, this is a
     * null operation.
     * 
     * It will override the abstract automatically generated by the datamanager
     * prepending it with an img link to the thumbnail.
     * 
     * As a side effect, it will invalidate the content cache modules related to 
     * this photo.
     */
    function index()
    {
        if ($GLOBALS['midcom_config']['indexer_backend'] !== false)
        {
            // Before we start, reinitialize the DM, so that it is working with the 
            // most current set of data.
            $this->datamanager->init($this->article);

            $indexer =& $GLOBALS['midcom']->get_service('indexer');
            $document = $indexer->new_document($this->datamanager);
            
            // Generate the abstract.
            $thumb_url = "{$GLOBALS['midcom_config']['midcom_site_url']}midcom-serveattachmentguid-{$this->thumbnail}/thumbnail_{$this->article->name}.jpg";
            $document->abstract = "<img src='{$thumb_url}' />";
            
            if (array_key_exists('abstract', $this->datamanager->data))
            {
            	$document->abstract .= "<br />\n{$this->datamanager->data['abstract']}";
            }
            
            $indexer->index($document);
        }
        
        $GLOBALS['midcom']->cache->invalidate($this->article->guid());
    }
    
    /**
     * Loads the XML-formatted notes as-is
     */
    function load_notes()
    {
        // Check if there is a notes file
        if ($this->article->getattachment("notes.xml"))
        {
            $notes_fh = $this->article->openattachment("notes.xml","r");
            $notes = '';
            while (!feof($notes_fh))
            {
                $notes .= fread($notes_fh, 512);
            }
            fclose($notes_fh);
            return $notes;
        }
        return false;
    }    
 
    
    /**
     * Stores the XML-formatted notes as-is
     * TODO: Parse the XML into data? or at least index it
     */
    function save_notes($notes)
    {
        $notes_blob = null;
    
        // Check if there is an old notes file
        if ($this->article->getattachment("notes.xml"))
        {
            $notes_blob = $this->article->getattachment("notes.xml");
        }
        else
        {
            // Create new notes.xml
            $notes_blob = $this->article->createattachment("notes.xml","XML notes","text/xml");
        }
        
        if ($notes_blob)
        {
            $notes_fh = $this->article->openattachment("notes.xml","w");
            $result = fwrite($notes_fh,$notes);
            fclose($notes_fh);
            return $result;
        }
        return false;
    }    
    
} // Photo

?>
