<?php

/**
 * @package net.siriux.photos
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Photo Gallery Admin Class	
 * 	
 * @todo document
 * 
 * @package net.siriux.photos	
 */
class net_siriux_photos_admin {

    var $_debug_prefix;

    var $_config;
    var $_config_dm;
    var $_topic;
    var $_photo;
    var $_upload_datamanager;

    var $_view;
    var $_view_status;

    var $_numpics;
    var $_startfrom;
    var $_scales;
    var $_enable_notes;
    
    var $_l10n;
    var $_l10n_midcom;

    var $errcode;
    var $errstr;
    
    var $_local_toolbar;
    var $_topic_toolbar;
    
    function net_siriux_photos_admin($topic, $config) {
        $this->_debug_prefix = "net.siriux.photos admin::";

        $this->_startfrom = 0;

        $this->_config = $config;
        $this->_config_dm = null;
        $this->_topic = $topic;
        $this->_photo = false;
        $this->_upload_datamanager = null;
        
        /* show 10 pics per page on index */
        $this->_numpics = 10;

        $this->_view = "";
        $this->_view_status = "";

        $this->_scales = Array (
            "view_x" => $config->get("view_x"),
            "view_y" => $config->get("view_x"),
            "thumb_x" => $config->get("thumb_x"),
            "thumb_y" => $config->get("thumb_y")
        );
        
        $this->_enable_notes = $config->get("enable_flash_notes");
        
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.siriux.photos");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        $GLOBALS["view_l10n"] = $this->_l10n;
        $GLOBALS["view_l10n_midcom"] = $this->_l10n_midcom;
        
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $toolbars =& midcom_helper_toolbars::get_instance();
        $this->_topic_toolbar = &$toolbars->top;
        $this->_local_toolbar = &$toolbars->bottom;
        
    }


    /* functions called by the proxy class (contentadmin) */


    function can_handle($argc, $argv) 
    {
        debug_push($this->_debug_prefix."can_handle");
        
        $GLOBALS["midcom"]->set_custom_context_data("configuration", $this->_config);
        $GLOBALS["midcom"]->set_custom_context_data("l10n", $this->_l10n);
        $GLOBALS["midcom"]->set_custom_context_data("l10n_midcom", $this->_l10n_midcom);
        $GLOBALS["midcom"]->set_custom_context_data("errstr", $this->errstr);
        
        debug_pop();
        
        if ($argc == 0)
        {
            return true;
        }
        
        switch ($argv[0]) 
        {
            case "upload":
            case "config":
            case "rereadexif":
            case "synccreated":
            case 'cleanupnames':
            case 'rebuildthumbs':
            case 'notes_locale':
                return ($argc == 1);
                
            case "edit":
            case "delete":
            case 'rotate_cw':
            case 'rotate_ccw':
            case 'notes_save':
            case 'notes':
                return ($argc == 2);
                
            default:
                return false;
        }
    }


    function handle($argc, $argv) 
    {
        debug_push($this->_debug_prefix . "handle");
        
        /* Add the topic configuration item */
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        /* Add the new article link at the beginning*/
        $this->_topic_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'upload.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('upload photos'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/images.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ), 0);
        
        
        // pass startfrom value
        if (isset($_REQUEST) && array_key_exists("startfrom", $_REQUEST))
        {
            $this->_startfrom = $_REQUEST["startfrom"];
        }
        else
        {
            $this->_startfrom = 0;
        }
        
        if ($this->_startfrom % $this->_numpics > 0)
        { 
            $this->_startfrom -= ($this->_startfrom % $this->_numpics);
        }

        if ($argc == 0) 
        {
            debug_pop();
            return $this->_init_index();
        }

        switch ($argv[0]) {
            case "upload":
                $result = $this->_init_upload();
                break;
            case "config":
                $result = $this->_init_config();
                break;
            case "rereadexif":
                $result = $this->_init_reread_exif_timestamps();
                break;
            case "synccreated":
                $result = $this->_init_sync_article_created();
                break;
            case 'cleanupnames':
                $result = $this->_init_cleanup_names();
                break;
            case 'rebuildthumbs':
                $result = $this->_init_rebuild_thumbs();
                break;
            case "edit":
                $result = $this->_init_edit($argv[1]);
                break;
            case "delete":
                $result = $this->_init_delete($argv[1]);
                break;
            case 'rotate_cw':
                $result = $this->_init_rotate(90, $argv[1]);
                break;
            case 'rotate_ccw':
                $result = $this->_init_rotate(-90, $argv[1]);
                break;
            case 'notes_locale':
                $this->_show_notes_locale();
                break;
            case 'notes_save':
                $result = $this->_init_notes_save($argv[1]);
                break;
            case 'notes':
                $result = $this->_show_notes($argv[1]);
                break;
            default:
                $result = false;
                break;
        }

        debug_pop();
        return $result;
    }
        
    function show() {
        global $view_title;

        // get l10n libraries
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $GLOBALS["view_l10n"] = $i18n->get_l10n("net.siriux.photos");
        $GLOBALS["view_l10n_midcom"] = $i18n->get_l10n("midcom");

        $view_title = $this->_topic->extra;
        
        eval("\$result = \$this->_show_{$this->_view}();");
        return $result;
    }


    /* init functions, handle request and prepare output */
    
    
    /**
     * Rotate image init function, relocates to the index by default. Rotation
     * is controllable through the callee.
     *
     * @param int $degrees Number of degrees to rotate the image.
     * @param int $id The ID of the image to rotate.
     * @return bool false on failure, success relocates back to the AIS index.
     * @todo Make relocation target flexible
     */
    function _init_rotate ($degrees, $id)
    {
        debug_add("Rotating Photo {$id} {$degrees}° clockwise.", MIDCOM_LOG_DEBUG);
        $photo = new siriux_photos_Photo($id);
        if ($photo) 
        {
            debug_add('Photo loaded, executing $photo->rotate');
            $photo->rotate($degrees);
            // Flush MidCOM cache
            debug_add("Invalidating MidCOM cache", MIDCOM_LOG_DEBUG);
            $GLOBALS['midcom']->cache->invalidate($photo->article->guid());
            $GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                . "?startfrom={$this->_startfrom}");
            // This will exit
        } 
        else
        {
            $this->errcode = MIDCOM_ERRCRIT;
            $this->errstr = "Could not load Photo {$id}: " . mgd_errstr();
            return false;
        }
    }
    
    function _init_index() {
        debug_push($this->_debug_prefix . "_init_index");
        $this->_view = "index";

        // handle approval
        if (isset($_REQUEST))
        {
            if (array_key_exists("approve", $_REQUEST)) 
	        {
	            debug_add("Approving Photo " . $_REQUEST["approve"], MIDCOM_LOG_DEBUG);
                $article = mgd_get_article($_REQUEST['approve']);
	            if ($article) 
	            {
	                $meta = midcom_helper_metadata::retrieve($article);
	                $meta->approve();
	            } 
	            else
	            {
	                debug_add("Could not approve Photo " . $_REQUEST["approve"], MIDCOM_LOG_WARN);
	                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Could not approve Photo {$_REQUEST['approve']}: " . mgd_errstr());
	            }            
	        }
	        else if (array_key_exists("unapprove", $_REQUEST)) 
	        {
	            debug_add("Unapproving Photo " . $_REQUEST["unapprove"], MIDCOM_LOG_DEBUG);
                $article = mgd_get_article($_REQUEST['unapprove']);
	            if ($article) 
	            {
	                $meta = midcom_helper_metadata::retrieve($article);
	                $meta->unapprove();
	            } 
	            else
	            {
	                debug_add("Could not unapprove Photo " . $_REQUEST["unapprove"], MIDCOM_LOG_WARN);
	                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, "Could not unapprove Photo {$_REQUEST['unapprove']}: " . mgd_errstr());
	            }            
	        }
        }
        debug_pop();
        return true;
    }

    function _show_index() {
        global $view_ids;
        global $view_prev;
        global $view_next;
        global $view_startfrom;
        
        $GLOBALS['view_thumbs_x'] = 1;
        $GLOBALS['view_thumbs_y'] = $this->_numpics;
        
        $view_ids = false;
        $view_prev = -1;
        $view_next = -1;
        $view_startfrom = $this->_startfrom;

        $articles = mgd_list_topic_articles($this->_topic->id, $this->_config->get("sort_order"));
        if ($articles) {
            $GLOBALS['view_total'] = $articles->N;
            $count = 0;
            while ($articles->fetch()) 
            {
                if (   $count >= $this->_startfrom 
                    && $count < ($this->_startfrom + $this->_numpics))
                {
                    $view_ids[] = $articles->id;
                }
                $count++;
            }
        }
        midcom_show_style("admin_index");
    }


    function _init_upload() {
        debug_push($this->_debug_prefix . "_init_upload");
        $this->_view = "upload";
        
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->disable_view_page();
        
        /* We create a datamanager who's sole purpose is to display an
         * uplaod form here. The specificied callback does not exist,
         * processing the resulting form is not an option here therefore,
         * the upload handler will do some hacks here to do the true
         * upload.
         */
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->_upload_datamanager = new midcom_helper_datamanager($this->_config->get("schemadb"));
        if (! $this->_upload_datamanager) {
            $this->errstr = "Failed to create the upload datamanager instance. Aborting.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        if (! $this->_upload_datamanager->init_creation_mode($this->_config->get("schema_upload"), $this, "xxx")) {
            $this->errstr = "Failed to initialize the upload datamanager instance. Aborting.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        
        if (array_key_exists('upload_complete', $_REQUEST))
        {
            $GLOBALS['view_contentmgr']->msg .= $_REQUEST['upload_complete'];
        }
        
        $this->_view_status = $this->_process_upload();
        debug_pop();
        return true;
    }


    function _process_upload() {
        debug_push($this->_debug_prefix . "_process_upload");
        
        /* This function handles the upload in a rather hacky way:
         *
         * The data, that has been submitted using the upload datamanager
         * will not be evaluated by that datamanager. Instead, the uploaded file
         * will be processed manually, and a new datamanager without the
         * upload field but with (at least) all other fields will be used instead.
         *
         * This is some averagly serious hack of the DM, and should be handled
         * with its due respect. If in doubt, ask me (torben@nehmer.net).
         *
         * If we have mulitple upload files, we display a progress log and redirect
         * to the (now empty) upload form. This is neccessary as Apache would kill
         * us after 2 mins of no-output-time.
         */
        
        /* 1st, we determine if we had a successfully posted request, if not, exit here */
        if (! array_key_exists("midcom_helper_datamanager_submit", $_REQUEST)) {
            debug_add("No upload form was submitted, exiting");
            debug_pop();
            return "";
        }
        
        /* 2nd, do a sanity check against the uploaded file */
        if (! array_key_exists("midcom_helper_datamanager_field_upload_file", $_FILES)) {
            debug_add("No file was uploaded.");
            debug_pop();
            return "No file was uploaded.<br />\n";
        }
        
        /* 3rd, check if we need to create a subtopic */
        $create_subgallery = FALSE;
        if (   array_key_exists("midcom_helper_datamanager_field_create_subgallery", $_REQUEST)
            && $_REQUEST["midcom_helper_datamanager_field_create_subgallery"] == "on")
        {
            $create_subgallery = TRUE;
        }
        
        $midgard = $GLOBALS["midcom"]->midgard;
        $result = "";
        $file = $_FILES["midcom_helper_datamanager_field_upload_file"];
        
        debug_print_r("Processing upload of this file:", $file);
        
        // Ignore client-side aborts for now, to avoid incomplete uploads due to the 
        // Client Timeouts until the upload sequence is complete.
        debug_add('Disabling script abort through client.');
        ignore_user_abort(true);
        
        /* Scan for compressed files using the file name extension */
        $file_extensions = explode(".",strtolower($file["name"]));
        $file_extension = $file_extensions[((count($file_extensions)) - 1)];
        
        $compressed = false;
        $uploadtempdir = "/tmp/net.siriux.photos.upload." . time() . "." . getmypid();
        
        switch ($file_extension) 
        {
            case "zip":
                $compressed = true;
                $unzipcmd = 
                    "{$GLOBALS['midcom_config']['utility_unzip']} "
                    . escapeshellarg($file['tmp_name'])
                    . " -d{$uploadtempdir}";
                break;
                
            case "gz":
            case "tgz": 
                $compressed = true;
                $unzipcmd =
                    "cd {$uploadtempdir} ; {$GLOBALS['midcom_config']['utility_tar']} -xzf "
                    . escapeshellarg($file["tmp_name"]);
                break;
                
            default:
                $compressed = false;
                $uncompresscmd = "";
                break;
        }
        
        /* Depending on wether we have a compressed file, we do now
         * create a list of files to process. (So this could very well
         * be a single file.
         */
        $files = Array();
        
        if ($compressed) 
        {
			$GLOBALS['midcom']->cache->content->enable_live_mode();
            $title = $this->_l10n->get('processing upload, please wait');
?>
<html>
<body>
<h1><?php echo $title; ?>...</h1>
<pre>
Extracting archive...<?php
			flush();
			
			
            debug_add("executing upzip command: $unzipcmd");
            exec("mkdir {$uploadtempdir}");
            exec($unzipcmd);
            
            $find_result = Array();
            exec("{$GLOBALS['midcom_config']['utility_find']} {$uploadtempdir} -type f", $find_result);
            foreach ($find_result as $fullname)
            {
                if (! $this->check_uploaded_image($fullname))
                {
                    // Not a valid image recognized by imagemagick.
                    continue;
                }
                $lastslash = strrpos($fullname, '/');
                // We should always have at least two slashes, see the
                // definition of uploadtempdir above.
                $file = substr($fullname, $lastslash + 1);
                $files[$fullname] = $file;
            }
			
			echo " done\n";
			flush();
        } 
        else 
        {
            $files[$file["tmp_name"]] = $file["name"];
        }
        
        debug_print_r("We have to process these " . count($files) . " files:", $files);
        
        if ($create_subgallery)
        {
            // Create subgallery as user requested
            $subgallery = mgd_get_topic();
            $subgallery->up = $this->_topic->id;
            
            // Figure out a title for the topic
            if ($_REQUEST["midcom_helper_datamanager_field_title"] != "")
            {
                $subgallery->extra = $_REQUEST["midcom_helper_datamanager_field_title"];
            }
            else
            {
                // No user-supplied title for the gallery, use uploaded file's name
                // TODO: Remove file extension from the name
                $subgallery->extra = $_FILES["midcom_helper_datamanager_field_upload_file"]["name"];
            }
            $subgallery->name = midcom_generate_urlname_from_string($subgallery->extra);
            $subgallery_created = $subgallery->create();
            if (!$subgallery_created)
            {
                $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                    "Subgallery creation failed, reason " . mgd_errstr());
                // This will exit
            }
            $subgallery = mgd_get_topic($subgallery_created);
            $subgallery->parameter("midcom","component","net.siriux.photos");
            
            // Copy local gallery configuration
            $local_configuration = $this->_topic->listparameters("net.siriux.photos");
            while (   $local_configuration
                   && $local_configuration->fetch())
            {
                $subgallery->parameter("net.siriux.photos", $local_configuration->name, $this->_topic->parameter("net.siriux.photos", $local_configuration->name));
            }
            
        }
        
        // Handle the upload, process each file in the array. 
        $i = 0;
        $files_to_do = count($files) + 1;
        $file_at_work = 0;
        
        foreach ($files as $filename => $realname) 
        {
            debug_add("Processing image {$realname} from {$filename}", MIDCOM_LOG_INFO);
            $file_at_work++;
			if ($compressed)
			{
				echo "Processing image {$realname} ({$file_at_work}/{$files_to_do})...";
				flush();
			}
            
            // Update script execution time
            set_time_limit(30);
            
            // Instantiate the photo object
            if ($create_subgallery)
            {
                // Create the photos into the newly created subgallery
                $photo = new siriux_photos_Photo(FALSE, null, $subgallery);
            }
            else
            {
                // Create the photos into this gallery
                $photo = new siriux_photos_Photo();
            }
            
            if ($photo->create_from_datamanager($filename, $realname)) 
            {
                $i++;
                if ($photo->article->title != "") 
                {
                    if (count($files) > 1) 
                    {
                        $photo->article->title .= " {$i}";
                    }
                    
                } 
                else 
                {
                    $photo->article->title = $photo->article->name;
                }
                $photo->article->update();
                
                // Recreate URL name from title only if mandated by config.
                if ($this->_config->get("create_urlname_from_title"))
                {
                    $photo->article->name = midcom_generate_urlname_from_string($photo->article->title);
                    $photo->article->update();
                }
                
                debug_add("Sucessfully uploaded {$filename} from {$realname}");
                
                if ($compressed)
                {
                    // Report only failures during batch processing
                    echo " done\n";
                    flush();
                }
                else
                {
                    $result .= "Successfully uploaded {$realname}<br\>\n";
                }
            } 
            else 
            {
                debug_add("Failed to upload {$filename} from {$realname}: {$this->errstr}", MIDCOM_LOG_WARN);
                $result .= "Failed to upload {$realname}: {$this->errstr}<br\>\n";
                if ($compressed)
                {
                    echo " <span style='color:red;'>failed: {$this->errstr}</span>\n";
                    flush();
                }
            }
            
            $photo->datamanager->destroy();
        }
        // Reset script execution time limit to some sane value for the rest
        // of the script.
        set_time_limit(20);
        
        /* Clean up all temporary files we are responsible for. */
        if ($compressed) {
            /* recursivly delete upload directory, I think this is
             * safe as we run as www-data. 
             *
             * QUESTION: Can this be exploited somehow?
             */
            exec("rm -fr {$uploadtempdir}");
        }
        
        // Reactivate user abort and check for any aborted connections (finish processing)
        // at this point then.
        if (connection_aborted())
        {
            debug_add('WARNING: The client has disconnected during the upload sequence.', MIDCOM_LOG_ERROR);
            debug_add('Upload has been finshed though, and we will exit now.', MIDCOM_LOG_ERROR);
            $GLOBALS['midcom']->finish();
            exit();
        }
        debug_add('Enabling script abort through client again.');
        ignore_user_abort(false);
        
        // Invalidate the cache, we don't use the DM Creation mode, so we
        // have to do this manually.
        $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());
         
        if ($compressed)
        {
            // TODO: If subgallery was created we might want to redirect user there
            $dest = $GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) 
                . 'upload.html?upload_complete='
                . urlencode($result);
?>
Upload complete.
</pre>
<script type="text/javascript">
<!--
	window.location.href = "<?php echo $dest; ?>";
-->
</script>
<a href="<?php echo $dest; ?>"><?php echo $this->_l10n->get('click here to continue');?></a>
</body>
</html>
<?php
            debug_add('Batch upload complete, exitting...');
            exit();
        }
        
        debug_pop();
        return $result;
    }

    function check_uploaded_image ($filename)
    {
        $cmd = "{$GLOBALS['midcom_config']['utility_imagemagick_base']}identify " 
            . escapeshellarg($filename) . ' 2>&1';
        debug_add("Executing: {$cmd}");
        exec ($cmd, $output, $return);
        if ($return !== 0)
        {
            debug_add("Check of image {$filename} failed, imagemagick return exit code {$return}", MIDCOM_LOG_INFO);
            debug_print_r('identify output:', $output);
            return false;
        }
        return true;
    }

    function _show_upload() {
        global $view_status;
        $view_status = $this->_view_status;
        $GLOBALS["view_upload_dm"] = $this->_upload_datamanager;
        midcom_show_style("admin_upload");
    }

    function _init_config() {
        debug_push($this->_debug_prefix . "_init_config");
        
        $this->_prepare_config_dm();
        
        /* Add the toolbar items */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'rereadexif.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('reread exif heading'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_refresh.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'synccreated.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('sync article created heading'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_refresh.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'rebuildthumbs.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('rebuild thumbnails heading'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_refresh.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'cleanupnames.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('cleanup article names'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        switch ($this->_config_dm->process_form()) {
            case MIDCOM_DATAMGR_SAVED:
                $this->_check_for_thumb_rebuild();
                // This might exit using a relocate.
                
                // Fall-through
                
            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_CANCELLED:
                // Do nothing here, the datamanager will invalidate the cache.
                // Apart from that, let the user edit the configuration as long
                // as he likes.
                break;

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }
        
        $this->_view = "config";
        debug_pop();
        return true;
    }

    function _check_for_thumb_rebuild()
    {
        // Check for changes in the image dimensions.
        // We can user $this->_config here, as it isn't automatically
        // updated by the DM.
        
        $view_changed = false;
        $thumb_changed = false;
        
        if (   $this->_config->get('view_x') != $this->_config_dm->data['view_x']
            || $this->_config->get('view_y') != $this->_config_dm->data['view_y'])
        {
            $view_changed = true;
        }
        if (   $this->_config->get('thumb_x') != $this->_config_dm->data['thumb_x']
            || $this->_config->get('thumb_y') != $this->_config_dm->data['thumb_y'])
        {
            $thumb_changed = true;
        }
        
        if ($view_changed || $thumb_changed)
        {
            if ($view_changed && $thumb_changed)
            {
                $msg = 'size of thumbnails and views modified';
            }
            else if ($view_changed)
            {
                $msg = 'size of views modified';
            }
            else 
            {
                $msg = 'size of thumbnails modified';
            }
            $url = 'rebuildthumbs.html?net_siriux_photos_msg=' . urlencode($msg);
            $GLOBALS['midcom']->relocate($url);
            // This will exit()
        }
    }    


    function _show_config() {
        global $view_config;
        global $view_topic;
        $view_config = $this->_config_dm;
        $view_topic = $this->_topic;
        midcom_show_style("admin_config");
    }

    function _init_edit($id) {
        debug_push($this->_debug_prefix . "_init_edit");
        
        // get Photo
        $this->_photo = new siriux_photos_Photo($id);
        if (! $this->_photo) {
            debug_add("Can't get Photo with id $id", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        // set nap element
        $GLOBALS['midcom_component_data']['net.siriux.photos']['active_leaf'] = $this->_photo->id;
        
        /* Add the toolbar items */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "?startfrom={$this->_startfrom}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "edit/{$id}.html?startfrom={$this->_startfrom}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => false
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "delete/{$id}.html?startfrom={$this->_startfrom}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        switch ($this->_photo->datamanager->process_form()) {
            case MIDCOM_DATAMGR_SAVED:
                if (! $this->_config->get("disable_autosync_created"))
                {
                    $this->_photo->syncArticleCreated();
                }
                $this->_photo->index();
                // Fall through
                
            case MIDCOM_DATAMGR_CANCELLED:
                $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $location = $prefix . "?startfrom=" . $this->_startfrom;
                $GLOBALS["midcom"]->relocate($location);
                break;
                
            case MIDCOM_DATAMGR_EDITING:
                // Stay in the edit loop.
                break;

            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
        }
        
        $this->_view = "edit";

        debug_pop();
        return true;
    }

    function _show_notes_locale()
    {
        if ($this->_enable_notes)
        {
            debug_add("Showing locale file for image notes Flash", MIDCOM_LOG_DEBUG);
            include(MIDCOM_ROOT . "/net/siriux/photos/style/admin_notes_locale.php");
        }
        else
        {
            return false;
        }
    }

    function _init_notes_save($id)
    {
        if ($this->_enable_notes)
        {
            debug_add("Flash note XML save handler called", MIDCOM_LOG_DEBUG);
            $photo = new siriux_photos_Photo($id);
            if ($photo) 
            {
                debug_add('Photo loaded, executing XML note save handler', MIDCOM_LOG_DEBUG);
            
                if (isset($_REQUEST["notes"]))
                {
                    $result = $photo->save_notes($_REQUEST["notes"]);
                    if ($result)
                    {
                        debug_add('XML note saved', MIDCOM_LOG_DEBUG);
                        echo "error=0&desc=Notes saved\n";
                        exit();
                    }
                    else
                    {
                        echo "error=1&desc=Failed to save notes\n";
                        exit();
                    }
                }
                else
                {
                    debug_add('No "notes" POST variable set', MIDCOM_LOG_DEBUG);
                    return false;
                }
            } 
            else
            {
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Could not load Photo {$id}: " . mgd_errstr();
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    function _show_notes($id)
    {
        if ($this->_enable_notes)
        {
            debug_add("Showing Flash note XML", MIDCOM_LOG_DEBUG);
            $photo = new siriux_photos_Photo($id);
            if ($photo) 
            {
                debug_add('Photo loaded, executing XML note loader handler', MIDCOM_LOG_DEBUG);
                $notes = $photo->load_notes();
                if ($notes)
                {
                    header("Content-type: text/xml; charset=UTF-8");
                    echo $notes;
                    exit();
                }
                return false;
            } 
            else
            {
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Could not load Photo {$id}: " . mgd_errstr();
                return false;
            }
        }
        else
        {
            return false;
        }
    }

    function _show_edit() {
        global $view;
        global $view_startfrom;
        global $view_enable_notes;
        $view = $this->_photo;
        $view_startfrom = $this->_startfrom;
        $view_enable_notes = $this->_enable_notes;
        midcom_show_style("admin_edit");
    }


    function _init_delete($id) {
        debug_push($this->_debug_prefix . "_init_delete");
        
        // get Photo
        $this->_photo = new siriux_photos_Photo($id);

        if (! $this->_photo) {
            debug_add("Can't get Photo with id $id", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        // set nap element
        $GLOBALS['midcom_component_data']['net.siriux.photos']['active_leaf'] = $this->_photo->id;
        
        /* Add the toolbar items */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "?startfrom={$this->_startfrom}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "edit/{$id}.html?startfrom={$this->_startfrom}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "delete/{$id}.html?startfrom={$this->_startfrom}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => false
        ));
        
        // process form
        if (isset($_REQUEST) && (array_key_exists("fdelete_submit", $_REQUEST) or array_key_exists("fdelete_cancel", $_REQUEST))) {
            if (array_key_exists("fdelete_submit", $_REQUEST)) {
                debug_add("Deleting Photo $id", MIDCOM_LOG_INFO);
                // Flush MidCOM cache
	            debug_add("Invalidating MidCOM cache", MIDCOM_LOG_DEBUG);
	            $GLOBALS['midcom']->cache->invalidate($this->_photo->article->guid());
                $this->_photo->delete();
            } else {
                debug_add("Not deleting Photo $id, Cancel was pressed.", MIDCOM_LOG_DEBUG);
            }

            $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            $location = $prefix . "?startfrom=" . $this->_startfrom;
            debug_add("Relocating to $location");
            $GLOBALS["midcom"]->relocate($location);
            /* This will exit */
        }

        $this->_view = "delete";

        debug_pop();
        return true;
    }


    function _show_delete() {
        global $view;
        global $view_startfrom;
        $view = $this->_photo;
        $view_startfrom = $this->_startfrom;
        midcom_show_style("admin_delete");
    }

    function _init_reread_exif_timestamps() {
        debug_push("net.siriux.photos: RereadExif");
        
        /* Add the toolbar items */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        if (array_key_exists("f_submit", $_REQUEST)) {
            $articles = mgd_list_topic_articles($this->_topic->id);
            if ($articles) {
                while ($articles->fetch()) {
                    $photo = new siriux_photos_Photo($articles->id);
                    $photo->rereadExif();
                }
            }
            $this->errstr = $this->_l10n->get("Successfully reread EXIF data.");
            $GLOBALS['midcom']->cache->invalidate_all();
        }
        
        $this->_view = "reread_exif_timestamps";
        debug_pop();
        return true;
    }
    
    function _show_reread_exif_timestamps() {
        global $view_topic;
        global $view_msg;
        $view_msg = $this->errstr;
        $view_topic = $this->_topic;
        midcom_show_style("admin_reread_exif_confirm");
    }
    
    function _init_sync_article_created() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        /* Add the toolbar items */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        if (array_key_exists("f_submit", $_REQUEST)) 
        {
            $articles = mgd_list_topic_articles($this->_topic->id);
            if ($articles) 
            {
                while ($articles->fetch()) 
                {
                    $photo = new siriux_photos_Photo($articles->id);
                    $photo->syncArticleCreated();
                }
            }
            $this->errstr = $this->_l10n->get("Successfully synchronized article created timestamps.");
            $GLOBALS['midcom']->cache->invalidate_all();
        }
        
        $this->_view = "sync_article_created";
        debug_pop();
        return true;
    }
    
    function _init_rebuild_thumbs() {
        debug_push("net.siriux.photos: rebuildthumbs");
        
        /* Add the toolbar items */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        if (array_key_exists("f_submit", $_REQUEST)) 
        {
            ignore_user_abort(true);
            $articles = mgd_list_topic_articles($this->_topic->id);
            if ($articles) 
            {
                while ($articles->fetch()) 
                {
                    // Keep execution timeout up
                    set_time_limit(30);
                    
                    $photo = new siriux_photos_Photo($articles->id);
                    $photo->rebuildThumbs();
                }
                
            }
            $this->errstr = $this->_l10n->get("regenerated thumbs.");
            $GLOBALS['midcom']->cache->invalidate_all();
            ignore_user_abort(false);
        }
        else
        {
            if (array_key_exists('net_siriux_photos_msg', $_REQUEST))
            {
                $this->errstr = $this->_l10n->get($_REQUEST['net_siriux_photos_msg']);
            } 
        }
        
        $this->_view = "rebuild_thumbs";
        debug_pop();
        return true;
    }
    
    function _show_rebuild_thumbs() {
        global $view_topic;
        global $view_msg;
        $view_msg = $this->errstr;
        $view_topic = $this->_topic;
        midcom_show_style("admin_rebuild_thumbs_confirm");
    }
    
    function _init_cleanup_names() {
        debug_push("net.siriux.photos: cleanup_names");
        
        /* Add the toolbar items */
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => '',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('back to index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        if (array_key_exists("f_submit", $_REQUEST)) 
        {
            $articles = mgd_list_topic_articles($this->_topic->id);
            if ($articles) 
            {
                while ($articles->fetch()) 
                {
                    debug_add("Processing Article {$articles->id}");
                    $article = mgd_get_article($articles->id);
                    $article->name = midcom_generate_urlname_from_string($article->name);
                    if (! $article->update())
                    {
                        debug_print_r("Failed to update article: ", $article);
                        $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                            "Failed to update article {$article->id}, last error was: " . mgd_errstr());
                        // This will exit()
                    }
                }
            }
            $this->errstr = $this->_l10n->get("cleaned up all names");
            $GLOBALS['midcom']->cache->invalidate_all();
        }
        
        $this->_view = "cleanup_names";
        debug_pop();
        return true;
    }
    
    function _show_cleanup_names() {
        global $view_topic;
        global $view_msg;
        $view_msg = $this->errstr;
        $view_topic = $this->_topic;
        midcom_show_style("admin_cleanup_names_confirm");
    }
    
    function _show_sync_article_created()
    {
        global $view_topic;
        global $view_msg;
        $view_msg = $this->errstr;
        $view_topic = $this->_topic;
        midcom_show_style("admin_sync_article_created_confirm");
    }

    /* nap functions */


    function get_metadata() {
        return Array (
            MIDCOM_META_CREATOR => 0,
            MIDCOM_META_EDITOR  => 0,
            MIDCOM_META_CREATED => 0,
            MIDCOM_META_EDITED  => 0

        );
    }
    
    /* helpers */
    
    function _prepare_config_dm () {
        /* Set a global so that the schema gets internationalized */
        $GLOBALS["view_l10n"] = $this->_l10n;
        $this->_config_dm = new midcom_helper_datamanager("file:/net/siriux/photos/config/schemadb_config.inc");

        if ($this->_config_dm == false) {
            debug_add("Failed to instantinate configuration datamanager.", MIDCOM_LOG_CRIT);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                                               "Failed to instantinate configuration datamanager.");
        }
        
        if (! $this->_config_dm->init($this->_topic)) {
            debug_add("Failed to initialize the datamanager.", MIDCOM_LOG_CRIT);
            debug_print_r("Topic object we tried was:", $this->_topic);
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT, 
                                               "Failed to initialize configuration datamanager.");
        }
    }
    
} // admin


?>