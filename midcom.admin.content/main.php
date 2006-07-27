<?php
class midcom_admin_content_main {

    var $errcode;
    var $errstr;

    var $_mytopic;
    var $_mycontext;
    var $_topic;
    var $_root_topic;
    var $_command;
    var $_context;

    var $_l10n;
    var $_l10n_midcom;

    var $config;
    var $viewdata;
    var $msg;

    /* pointer to midcom_session_object */
    var $_session = null;

    /* Should we be aegir now?  */
    var $_is_aegir = false;


    function midcom_admin_content_main($mytopic, $config)
    {
        // First check if there is some link prefetching header, if yes, we bail out.
        if (   array_key_exists('HTTP_X_MOZ', $_SERVER)
            && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
        {
            // This is a prefetch request. Block it.
            header('HTTP/1.0 403 Forbidden');
            die('403: Forbidden<br><br>Prefetching not allowed in AIS, it is currently unsafe.');
        }

        global $midcom_cachedir;
        global $midcom_cachehandler;
        global $midcom_cachemultilang;

        // get root_page and root_topic

        $page = new midcom_baseclasses_database_page($config->get("root_page"));
        if (!$page)
        {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "root_page GUID invalid, please check the parameter on the AIS topic");
        }

        $topic = new midcom_db_topic($config->get("root_topic"));
        if (!$topic)
        {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRCRIT,
                "root_topic GUID invalid, please check the parameter on the AIS topic");
        }

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";

        $this->_mytopic = $mytopic;
        $this->_mycontext = $_MIDCOM->get_current_context();
        $this->_root_topic = $topic;
        $this->_command = null;
        $this->_mode = null;

        $this->config = $config;
        $this->viewdata = array();

        $this->msg = "";

        $i18n =& $_MIDCOM->i18n;
        $this->_l10n = $_MIDCOM->i18n->get_l10n("midcom.admin.content");
        $this->_l10n_midcom = $_MIDCOM->i18n->get_l10n("midcom");

    }


    function can_handle ($argc, $argv)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r("We have {$argc} Arguments:", $argv);
        debug_pop();
        if ($argc == 0)
        {
            return true;
        }

        if ($argc == 1)
        {
            return false;
        }

        switch ($argv[1])
        {
            case "data":
            case "topic":
            case "meta":
            case "attachment":
                return true;

            default:
                return false;
        }
    }


    function handle($argc, $argv)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $_MIDCOM->auth->require_valid_user();


        // Disable both MidCOM and Browser-Side Caching
        $_MIDCOM->cache->content->no_cache();

        if ($argc == 0)
        {
            // Redirect to the root topic
            debug_add("No parameters given, redirecting to the root_topic ({$this->_root_topic->id})", MIDCOM_LOG_INFO);
            $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$this->_root_topic->id}/data/");
            // This will exit
        }

        // Prepare the global variables
        $GLOBALS['view_contentmgr'] =& $this;
        /*
        $GLOBALS['midcom_admin_content_toolbar_main'] = new midcom_admin_content_toolbar(false, 'midcom_toolbar midcom_toolbar_ais_main', null);
        $GLOBALS['midcom_admin_content_toolbar_component'] = new midcom_admin_content_toolbar(true, 'midcom_toolbar', null);
        $GLOBALS['midcom_admin_content_toolbar_meta'] = new midcom_admin_content_toolbar(false, 'midcom_toolbar midcom_toolbar_ais_meta', null);
        */
        $toolbars = & midcom_helper_toolbars::get_instance();
        $toolbars->set_prefix($this->config->get('site_prefix')); 
        // Check for aegir or normal style:
        $style_attributes = array
        (
            'rel'   =>  "stylesheet" ,
            'type'  =>  "text/css" ,
            'media' =>  "screen"
        );

        
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/ais.css";
        $_MIDCOM->add_link_head( $style_attributes);
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/midcom_toolbar.css";
        $_MIDCOM->add_link_head( $style_attributes);
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/simple_style.css";
        $_MIDCOM->add_link_head( $style_attributes);

        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL ."/midcom.admin.content/navigation_functions.js");
        

        // argv has the following format: topic_id/mode
        $id = array_shift($argv);
        $this->_topic = new midcom_db_topic($id);

        if (! $this->_topic)
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "This topic could not be loaded (Topic #{$id} not found).";
            return false;
        }

        if (! mgd_is_in_topic_tree($this->_root_topic->id, $this->_topic->id))
        {
            $this->errcode = MIDCOM_ERRNOTFOUND;
            $this->errstr = "This topic could not be loaded (Topic #{$id} is not below #{$this->_root_topic->id}).";
            return false;
        }

        $mode = array_shift($argv);

        // Prepare and enter context:

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$this->_topic->id}/{$mode}/";

        $this->_context = $_MIDCOM->_create_context();
        $oldcontext = $_MIDCOM->get_current_context();
        $_MIDCOM->_set_current_context($this->_context);

        $_MIDCOM->_set_context_data($prefix, MIDCOM_CONTEXT_ANCHORPREFIX);
        $_MIDCOM->_set_context_data("admin", MIDCOM_CONTEXT_SUBSTYLE);
        $_MIDCOM->_set_context_data(MIDCOM_REQUEST_CONTENTADM, MIDCOM_CONTEXT_REQUESTTYPE);
        $_MIDCOM->_set_context_data($this->_root_topic, MIDCOM_CONTEXT_ROOTTOPIC);
        $_MIDCOM->_set_context_data($this->_topic, MIDCOM_CONTEXT_CONTENTTOPIC);
        $_MIDCOM->_set_context_data($this->_topic->parameter("midcom","component"), MIDCOM_CONTEXT_COMPONENT);

        debug_print_r("Context created: ", $_MIDCOM->_context[$this->_context]);

        $this->viewdata["context"] = $this->_context;
        $this->viewdata["adminprefix"] = $_MIDCOM->get_context_data($this->_mycontext, MIDCOM_CONTEXT_ANCHORPREFIX);
        $this->viewdata["admintopicprefix"] = $this->viewdata["adminprefix"] . $this->_topic->id . "/";
        $this->viewdata["adminmode"] = $mode;

        debug_print_r("Local Data Cache: ", $this->viewdata, MIDCOM_LOG_DEBUG);
        switch ($mode) {
            case "data":
                debug_add("We are in Data Mode");
                $this->_command = new midcom_admin_content__cmddata ($argv, $this);
                break;

            case "topic":
                debug_add("We are in Topic Mode.");
                $this->_command = new midcom_admin_content__cmdtopic ($argv, $this);
                break;

            case "attachment":
                debug_add("We are in Attachment Mode.");
                $this->_command = new midcom_admin_content__cmdattachment ($argv, $this);
                break;

            case 'meta':
                debug_add("We are in Metadata Mode.");
                $this->_command = new midcom_admin_content__cmdmeta ($argv, $this);
                break;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Content Administration: Method " . $mode . " unknown.";
                debug_pop();
                return false;
        }
        debug_print_type("Command object has been created: ", $this->_command, MIDCOM_LOG_DEBUG);

        debug_add("Preparing Main Toolbar...");
        $this->_prepare_main_toolbar();

        debug_add("Command executing now.", MIDCOM_LOG_DEBUG);
        $result = $this->_command->execute();
        debug_add("Command execution Result was: " . $result, MIDCOM_LOG_DEBUG);

        debug_add("Preparing Meta Toolbar...");
        $this->_prepare_meta_toolbar();

        $_MIDCOM->_set_current_context($oldcontext);
        debug_pop();
        return $result;
    }

    /**
     * This function prepares the main content admin toolbar, adding
     * the following buttons to it:
     *
     * - Create topic (poweruser or topic owner, restriction possible through restrict_create config directive)
     * - Edit topic (poweruser or topic owner)
     * - Delete topic (poweruser or topic owner, restriction possible through restrict_delete config directive)
     * - Manage topic attachments (poweruser or topic owner)
     */
    function _prepare_main_toolbar()
    {
        
        //$toolbar =& $GLOBALS['midcom_admin_content_toolbar_main'];
        $toolbars =& midcom_helper_toolbars::get_instance();
        

        $toolbars->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}topic/create/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("create subtopic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:create', $this->_topic)
              )
        ));
        $toolbars->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}topic/edit/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("edit topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit-folder.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
              )
        ));
        $toolbars->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}topic/score/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("edit order"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
        ));
        $toolbars->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}topic/delete/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("delete topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:delete', $this->_topic)
              )
        ));
        $toolbars->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "{$this->viewdata['admintopicprefix']}attachment/",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("topic attachments"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midgard:attachments', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
              )
        ));
    }

    function _prepare_meta_toolbar()
    {
        $topic = $GLOBALS['midcom']->get_context_data($GLOBALS["view_contentmgr"]->viewdata["context"], MIDCOM_CONTEXT_CONTENTTOPIC);

        // First, we retrieve a metadata object for the currently active object.
        // We can only create a toolbar if and only if
        $nav = new midcom_helper_nav($this->_context);
        $nap_obj = null;
        if ($nav->get_current_leaf() !== false)
        {
            $nap_obj = $nav->get_leaf($nav->get_current_leaf());
        }
        else
        {
            $nap_obj = $nav->get_node($nav->get_current_node());
        }
        $meta =& midcom_helper_metadata::retrieve($nap_obj);
        if (! $meta)
        {
            debug_print_r("Failed to load the Metadata object for this NAP object, we don't create a toolbar therefore:", $nap_obj);
            return;
        }

        if ($_MIDCOM->auth->can_do('midgard:update', $meta->object))
        {
            $toolbars =& midcom_helper_toolbars::get_instance();
            

            $prefix = "{$this->viewdata['admintopicprefix']}meta/{$nap_obj[MIDCOM_NAV_GUID]}";

            $toolbars->meta->add_item(Array(
                MIDCOM_TOOLBAR_URL => null,
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('metadata for %s'), $nap_obj[MIDCOM_NAV_NAME]),
                MIDCOM_TOOLBAR_HELPTEXT => "GUID: {$nap_obj[MIDCOM_NAV_GUID]}" ,
                MIDCOM_TOOLBAR_ICON => null,
                MIDCOM_TOOLBAR_ENABLED => false,
            ));

            if (! $GLOBALS['midcom_config']['show_unapproved_objects'])
            {
                if ($meta->is_approved())
                {
                    $toolbars->meta->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "{$prefix}/unapprove.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('unapprove'),
                        MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('approved'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                        MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midcom:approve', $meta->object) == false),
                    ));
                }
                else
                {
                    $toolbars->meta->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "{$prefix}/approve.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('approve'),
                        MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('unapproved'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                        MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midcom:approve', $meta->object) == false),
                    ));
                }
            }

            if (! $GLOBALS['midcom_config']['show_hidden_objects'])
            {
                if ($meta->get('hide'))
                {
                    $toolbars->meta->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "{$prefix}/unhide.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('unhide'),
                        MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('hidden'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/hidden.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    ));
                }
                else
                {
                    $toolbars->meta->add_item(Array(
                        MIDCOM_TOOLBAR_URL => "{$prefix}/hide.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('hide'),
                        MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('not hidden'),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_hidden.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    ));
                }
            }

            $toolbars->meta->add_item(Array(
                MIDCOM_TOOLBAR_URL => "{$prefix}/edit.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit metadata'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            ));

            if (! $GLOBALS['midcom_config']['show_hidden_objects'])
            {
                $start = $meta->get('schedule_start');
                $end = $meta->get('schedule_end');
                if ($start || $end)
                {
                    $now = time();

                    $text = '';
                    if ($start && $end)
                    {
                        $text = sprintf($this->_l10n_midcom->get('shown from %s to %s'),
                                        strftime("%x %X", $start),
                                        strftime("%x %X", $end));
                    }
                    else if ($start)
                    {
                        $text = sprintf($this->_l10n_midcom->get('shown from %s'),
                                        strftime("%x %X", $start));
                    }
                    else
                    {
                        $text = sprintf($this->_l10n_midcom->get('shown until %s'),
                                        strftime("%x %X", $end));
                    }

                    if (   (! $start || $start <= $now)
                        && (! $end || $end >= $now))
                    {
                        $toolbars->meta->add_item(Array(
                            MIDCOM_TOOLBAR_URL => null,
                            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('scheduled and shown'),
                            MIDCOM_TOOLBAR_HELPTEXT => $text,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/scheduled_and_shown.png',
                            MIDCOM_TOOLBAR_ENABLED => false,
                        ));
                    }
                    else
                    {
                        $toolbars->meta->add_item(Array(
                            MIDCOM_TOOLBAR_URL => null,
                            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('scheduled but hidden'),
                            MIDCOM_TOOLBAR_HELPTEXT => $text,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/scheduled_but_hidden.png',
                            MIDCOM_TOOLBAR_ENABLED => false,
                        ));
                    }
                }
            }
        }
    }


    function show()
{
        $oldcontext = $_MIDCOM->get_current_context();
        $_MIDCOM->_set_current_context($this->_context);
        if ($this->viewdata["adminmode"] == "data")
        {
            // Enter the style context of the component handling the request
            // Show the style-init element of the corresponding style.
            $_MIDCOM->style->enter_context($this->_context);
            midcom_show_style("style-init");
        }

        $this->_command->show();

        $_MIDCOM->_set_current_context($oldcontext);
        if ($this->viewdata["adminmode"] == "data")
        {
            // Show the style-finish element of the component handling the request.
            // Then leave the corresponding style context.
            midcom_show_style("style-finish");
            $_MIDCOM->style->leave_context();
        }
        $_MIDCOM->_set_current_context($oldcontext);
    }

    /* These functions are deprecated now and for compaitibility only */
    function add_jsfile($url) { $GLOBALS["midcom"]->add_jsfile($url); }
    function add_jscript($script) { $GLOBALS["midcom"]->add_jscript($script); }
    function add_jsonload($method) { $GLOBALS["midcom"]->add_jsonload($method); }
    function print_jscripts() { return $GLOBALS["midcom"]->print_jscripts(); }
    function print_jsonload() { return $GLOBALS["midcom"]->print_jsonload(); }

}

// Declare the globals

/**
 * Global reference to the Conent Manager.
 *
 * Was originally used to gain access to the JScript hooks. Since
 * these functions have been moved into midcom_application, the
 * usage of this global is deprecated.
 *
 * @global midcom_admin_content $GLOBALS['view_contentmgr']
 * @deprecated Since 2.0.0, Use midcom_application's JScript hooks.
 */
$GLOBALS['view_contentmgr'] = null;

/**
 * Global reference to the main content manager toolbar.
 *
 * This toolbar is usually initialized with the topic editing
 * UI (depending on the permissions set) and the view page link.
 *
 * It disables the view-this-page-link feature of midcom_admin_content_toolbar.
 *
 * @global midcom_admin_content_toolbar $GLOBALS['midcom_admin_content_toolbar_main']
 */
$GLOBALS['midcom_admin_content_toolbar_main'] = null;

/**
 * Global reference to the component content manager toolbar.
 *
 * This toolbar is initialized empty and can be set by the component
 * at-will. It is rendered between the heading of the component and the actual
 * content admin class output.
 *
 * It enables the view-this-page-link feature of midcom_admin_content_toolbar.
 *
 * @global midcom_admin_content_toolbar $GLOBALS['midcom_admin_content_toolbar_component']
 */
$GLOBALS['midcom_admin_content_toolbar_component'] = null;


?>
