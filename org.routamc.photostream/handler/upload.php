<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: upload.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * photo database upload photo handler
 *
 * @package org.routamc.photostream
 */
class org_routamc_photostream_handler_upload extends midcom_baseclasses_components_handler
{
    /**
     * The photowhich has been uploaded
     *
     * @var org_routamc_photostream_photo_dba
     * @access private
     */
    var $_photo = null;

    /**
     * The Controller of the article used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The defaults to use for the new article.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    /**
     * Simple default constructor.
     */
    function org_routamc_photostream_handler_upload()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['indexmode'] =& $this->_indexmode;
        $this->_request_data['schema'] =& $this->_schema;
        $this->_request_data['schemadb'] =& $this->_schemadb;
    }

    /**
     * Loads and prepares the schema database.
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];

        if ($_MIDCOM->auth->user)
        {
            $user = $_MIDCOM->auth->user->get_storage();
            $this->_defaults['photographer'] = $user->id;
        }

        $this->_to_gallery_defaults();
    }

    /**
     * Helper to handle various schema operations related to
     * the ?to_gallery=<id> upload mode 
     */
    function _to_gallery_defaults()
    {
        if (   !isset($_REQUEST['to_gallery'])
            || empty($_REQUEST['to_gallery']))
        {
            // No need to do anything
            return;
        }
        $this->_defaults['to_gallery'] = $_REQUEST['to_gallery'];
        if (!$this->_config->get('to_gallery_append_static_options'))
        {
            // Advanced handling disabled, return now
            return;
        }
        $gallery = new midcom_db_topic($_REQUEST['to_gallery']);
        if (   !$gallery
            || !isset($gallery->id)
            || empty($gallery->id))
        {
            // Could not find gallery, PONDER: Should we clear the _defaults value as well ?
            return;
        }

        if (   !isset($this->_schemadb['upload'])
            || !is_object($this->_schemadb['upload'])
            || !isset($this->_schemadb['upload']->fields['to_gallery']))
        {
            // Schema does not contain 'to_gallery' field
            return;
        }
        // Muck aout with the schema to get the gallery to options list
        $field =& $this->_schemadb['upload']->fields['to_gallery'];
        // Append gallery to options list (all select-compatible widgets will benefit from this)
        if (!isset($field['type_config']['options'][$gallery->id]))
        {
            $field['type_config']['options'][$gallery->id] = $gallery->extra;
        }
        // For universalchooser add to static options list
        if ($field['widget'] == 'universalchooser')
        {
            if (!isset($field['widget_config']['static_options']))
            {
                $field['widget_config']['static_options'] = array(0 => 'none');
            }
            if (!isset($field['widget_config']['static_options'][$gallery->id]))
            {
                $field['widget_config']['static_options'][$gallery->id] = $gallery->extra;
            }
        }
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'upload';
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 upload controller.");
            // This will exit.
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function &dm2_create_callback(&$controller)
    {
        $this->_photo = new org_routamc_photostream_photo_dba();
        $this->_photo->node = $this->_topic->id;
        if (! $this->_photo->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_photo);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to upload a new photo, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        // Creation callback function
        if ($this->_config->get('create_callback_function'))
        {
            if ($this->_config->get('create_callback_snippet'))
            {
                $eval = midcom_get_snippet_content($this->_config->get('create_callback_snippet'));

                if ($eval)
                {
                    eval("?>{$eval}<?php");
                }
            }

            $callback = $this->_config->get('create_callback_function');
            $callback($this->_article, $this->_content_topic);
        }
        
        return $this->_photo;
    }
    
    /**
     * Send a notification message when a new photo has been uploaded
     * 
     * @access private
     */
    function _send_moderation_notification()
    {
        $_MIDCOM->componentloader->load('org.openpsa.mail');
        $mail = new org_openpsa_mail();
        $mail->to = $this->_config->get('moderator_email');
        $mail->from = $this->_config->get('system_mailer_address');
        
        // Use a sane default if there is no from address
        if (!$mail->from)
        {
            $mail->from = "www-data@{$_SERVER['SERVER_NAME']}";
        }
        
        $mail->subject = $this->_l10n->get($this->_config->get('moderator_mail_subject'));
        $mail->body = $this->_l10n->get($this->_config->get('moderator_mail_body'));
        
        // Parse the mail variables
        if (   $this->_config->get('mail_variables')
            && is_array($this->_config->get('mail_variables')))
        {
            // Each __$key__ will be replaced by $value
            foreach ($this->_config->get('mail_variables') as $key => $value)
            {
                $mail->body = str_replace("__{$key}__", $value, $mail->body);
            }
        }
        
        if (!$mail->send())
        {
            return false;
        }
        
        return true;
    }

    function _copy__files($as, $from)
    {
        if (array_key_exists($as, $_FILES))
        {
            return false;
        }
        $adder = array();
        foreach ($from as $key => $data)
        {
            $adder[$key] = array();
            foreach ($data as $k => $v)
            {
                if ($key == 'tmp_name')
                {
                    // copy file and mpodify value
                    $new_v = "{$v}_{$as}";
                    $cmd = 'cp ' . escapeshellarg($v) . ' ' . escapeshellarg($new_v);
                    exec($cmd, $output, $ret);
                    if ($ret != 0)
                    {
                        // Failed to copy, problem!!
                        return false;
                    }
                    $v = $new_v;
                }
                $adder[$key][$k] = $v;
            }
        }
        $_FILES[$as] = $adder;
        return true;
    }

    function _batch_handler_cleanup($tmp_dir, $new_name)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called with: '{$tmp_dir}', '{$new_name}'");
        if (   empty($tmp_dir)
            || $tmp_dir === '/'
            /* TODO: better tmp dir matching */
            || !preg_match('|^/tmp/|', $tmp_dir)
            )
        {
            // Do somethign ? we cannot return as there's more work to do...
        }
        else
        {
            $cmd = "rm -rf {$tmp_dir}";
            debug_add("executing '{$cmd}'");
            exec($cmd, $output, $ret);
        }
        if (   empty($new_name)
            /* TODO: better tmp dir matching */
            || !preg_match('|^/tmp/|', $new_name)
            )
        {
            debug_pop();
            return;
        }
        $cmd = "rm -f {$new_name}";
        debug_add("executing '{$cmd}'");
        exec($cmd, $output, $ret);
        debug_pop();
    }

    function _batch_handler($extension, $file_data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $photo_field = false;
        foreach ($this->_request_data['schemadb']['upload']->fields as $name => $field)
        {
            if ($field['type'] == 'photo')
            {
                $photo_field = $name;
            }
        }
        if ($photo_field === false)
        {
            // could not resolve correct field for photo...
            unlink($file_data['tmp_name']);
            debug_pop();
            return false;
        }
        $batch_number = "{$_MIDGARD['user']}:" . time();
        $tmp_name =& $file_data['tmp_name'];
        $new_name = "{$tmp_name}.{$extension}";
        $mv_cmd = "mv -f {$tmp_name} {$new_name}";
        exec($mv_cmd, $output, $ret);
        if ($ret != 0)
        {
            // Move failed
            debug_add("failed to execute '{$mv_cmd}'", MIDCOM_LOG_ERROR);
            @unlink($tmp_name);
            debug_pop();
            return false;
        }
        $tmp_dir = "{$tmp_name}_extracted";
        if (!mkdir($tmp_dir))
        {
            // Could not create temp dir
            debug_add("failed to create directory '{$tmp_dir}'", MIDCOM_LOG_ERROR);
            $this->_batch_handler_cleanup(false, $new_name);
            debug_pop();
            return false;
        }
        $zj = false;
        switch (strtolower($extension))
        {
            case 'zip':
                $extract_cmd = "unzip -q -b -L -o {$new_name} -d {$tmp_dir}";
                break;
            case 'tgz':
            case 'tar.gz':
                $zj = 'z';
            case 'tar.bz2':
                if (!$zj)
                {
                    $zj = 'j';
                }
            case 'tar':
                $extract_cmd = "tar -x{$zj} -C {$tmp_dir} -f {$new_name}";
                break;
            default:
                // Unknown extension (we should never hit this)
                debug_add("unusable extension '{$extension}'", MIDCOM_LOG_ERROR);
                $this->_batch_handler_cleanup($tmp_dir, $new_name);
                debug_pop();
                return false;
        }
        debug_add("executing '{$extract_cmd}'");
        exec($extract_cmd, $output, $ret);
        if ($ret != 0)
        {
            // extract failed
            debug_add("failed to execute '{$extract_cmd}'", MIDCOM_LOG_ERROR);
            $this->_batch_handler_cleanup($tmp_dir, $new_name);
            debug_pop();
            return false;
        }
        $files = array();
        // Handle archives with subdirectories correctly
        $this->_batch_handler_get_files_recursive($tmp_dir, $files);

        foreach ($files as $file)
        {
            // PONDER: Output something so browser won't timeout ??
            $this->_load_controller();
            $result = $this->_controller->process_form();
            switch ($result)
            {
                // TODO: Check for cancel way before we get this far
                case 'cancel':
                    $this->_batch_handler_cleanup($tmp_dir, $new_name);
                    $_MIDCOM->relocate('');
                    // This will exit.
                case 'save':
                    // Change schema on the fly
                    $this->_photo->parameter('midcom.helper.datamanager2', 'schema_name', 'photo');
                    $this->_check_link_photo_gallery();
                    // Set batch number
                    $this->_photo->parameter('org.routamc.photostream', 'batch_number', $batch_number);
                    // Set image
                    $basename = basename($file);
                    if (!$this->_controller->datamanager->types[$photo_field]->set_image($basename, $file, $basename))
                    {
                        // Failed to set_image ? what to do ??
                    }
                    $this->_photo->update_attachment_links(false);
                    $this->_photo->read_exif_data(true);
                    $this->_photo->force_exif = true;
                    $this->_photo->update();
                    $this->index_uploaded_photo();
                    break;
                default:
                    debug_add("got unsupported result '{$result}' from this->_controller->process_form(), aborting", MIDCOM_LOG_ERROR);
                    $this->_batch_handler_cleanup($tmp_dir, $new_name);
                    debug_pop();
                    return false;
                    break;
            }
        }

        $this->_batch_handler_cleanup($tmp_dir, $new_name);
        debug_pop();
        // Redirect to batch list
        $number_encoded = rawurlencode($batch_number);
        $_MIDCOM->relocate("batch/{$number_encoded}/");
        // this will exit();
    }

    function _batch_handler_get_files_recursive($path, &$files)
    {
        $dp = @opendir($path);
        if (!$dp)
        {
            return;
        }
        while (($file = readdir($dp)) !== false)
        {
            if (preg_match('/(^\.)|(~$)/', $file))
            {
                // ignore dotfiles and backup files
                continue;
            }
            $filepath = "{$path}/{$file}";
            if (is_dir($filepath))
            {
                // It's a directory, recurse
                $this->_batch_handler_get_files_recursive($filepath, $files);
                continue;
            }
            if (is_link($filepath))
            {
                // Is a symlink, we can't do anything sensible with it
                continue;
            }
            if (!is_readable($filepath))
            {
                // for some weird reason the file *we* extracted is not readable by us...
                continue;
            }
            $files[] = $filepath;
        }
    }

    function index_uploaded_photo()
    {
        $indexer =& $_MIDCOM->get_service('indexer');
        org_routamc_photostream_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);
    }

    function _check_link_photo_gallery()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('Called');
        
        if (!isset($this->_controller->datamanager->types['to_gallery']))
        {
            debug_add('Could not find to_gallery-field in datamanager->types');
            debug_pop();
            return false;
        }
        $type =& $this->_controller->datamanager->types['to_gallery'];
        $gallery = (int) $type->convert_to_storage();
        if (empty($gallery))
        {
            debug_add("to_gallery value ({$gallery}) is empty, skipping");
            debug_pop();
            return false;
        }
        $_MIDCOM->componentloader->load_graceful('org.routamc.gallery');
        if (!class_exists('org_routamc_gallery_photolink_dba'))
        {
            debug_add('Required class org_routamc_gallery_photolink_dba not available (could not load component org.routamc.gallery ?)', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $link = new org_routamc_gallery_photolink_dba();
        $link->node = $gallery;
        $link->photo = $this->_photo->id;
        if (!$link->create())
        {
            debug_add("Could not link photo #{$this->_photo->id} to gallery #{$gallery}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        debug_add("Photo #{$this->_photo->id} linked to gallery #{$gallery}", MIDCOM_LOG_INFO);
        debug_pop();

        return $gallery;
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If upload privileges apply, we relocate to the index creation article
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_upload($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');

        // TODO: Figure out a solid way to detect the correct key in _FILES array based on the schema data
        if (   array_key_exists('photo_file', $_FILES)
            && preg_match('/\.(zip|tar(\.gz|\.bz2)?|tgz)$/', strtolower($_FILES['photo_file']['name']), $extension_matches))
        {
            // PHP5-TODO: This must be copy-by-value
            $copy = $_FILES['photo_file'];
            unset($_FILES['photo_file']);
            if (!$this->_batch_handler($extension_matches[1], $copy))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'The batch handler failed critically, see debug log for details');
                // This will exit
            }
        }

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Change schema on the fly from 'upload' to 'photo'
                $this->_photo->parameter('midcom.helper.datamanager2', 'schema_name', 'photo');
                $this->_photo->read_exif_data(true);
                $this->_photo->update();
                $gallery = $this->_check_link_photo_gallery();
                $this->index_uploaded_photo();
                if ($gallery)
                {
                    $nap = new midcom_helper_nav();
                    $gallery_node = $nap->get_node($gallery);
                    if ($gallery_node)
                    {
                        $_MIDCOM->relocate("{$gallery_node[MIDCOM_NAV_FULLURL]}photo/{$this->_photo->guid}/");
                        // This will exit
                    }
                }

                // Send an email notification of photos requiring moderation
                if (   $this->_config->get('moderate_uploaded_photos')
                    && $this->_config->get('moderator_email'))
                {
                    $this->_send_moderation_notification();
                }
                
                $_MIDCOM->relocate("photo/{$this->_photo->guid}/");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();

        if ($this->_photo)
        {
            $_MIDCOM->set_26_request_metadata($this->_photo->revised, $this->_photo->guid);
        }

        $data['view_title'] = sprintf($this->_l10n->get('upload photos'));
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => '/upload/',
            MIDCOM_NAV_NAME => $data['view_title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the loaded article.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_upload($handler_id, &$data)
    {
        midcom_show_style('photo_upload');
    }
}
?>