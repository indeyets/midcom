<?php
/**
 * Request class for editing styles
 * @package midcom.admin.styleeditor
 *
 */

/**
 *
 * @package midcom.admin.styleeditor
 */
class midcom_admin_styleeditor_admin  extends midcom_baseclasses_components_request_admin {

    var $msg;

	/* the current topic we are in
	 * @var current_topic
	 * @access public
	 **/
	var $_current_topic = 0;
    /* pointer to midcom_session_object */
    var $_session = null;

    /**
     * The schema database associated with articles.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = Array();


	function midcom_admin_styleeditor_viewer($topic, $config)
    {
    	//$page = mgd_get_object_by_guid($config->get("root_page"));
        parent::midcom_baseclasses_components_request($topic, $config);

        $this->msg = "";

        $this->_session = new midcom_service_session();
    }

	function _on_initialize() {


       	$_MIDCOM->cache->content->no_cache();
		$_MIDCOM->auth->require_valid_user();

        // edit/<page>/topic gives you the root style.
        $this->request_switch[] = Array
        (
            'fixed_args' => array('edit'),
            'handler' => array('midcom_admin_styleeditor_style','edit_root_element'),
            'variable_args' => 1,
        );
        // edit/<page>/<topic>/<pageelement>
        $this->request_switch[] = Array
        (
            'fixed_args' => array('edit'),
            'handler' => array('midcom_admin_styleeditor_style','edit_element'),
            'variable_args' => 2,
        );
        $this->request_switch[] = Array
        (
            'fixed_args' => array('configure'),
            'handler' => array('midcom_admin_styleeditor_style','edit'),
            'variable_args' => 1,
        );
        $this->request_switch[] = Array
        (
            'fixed_args' => array('new'),
            'handler' => array('midcom_admin_styleeditor_style','edit'),
            'variable_args' => 1,
        );
    }

    /**
     * This function adds all of the standard items (configuration and create links)
     * to the topic toolbar.
     *
     * @access private
     */
    function _prepare_toolbar()
    {
    	$toolbar = &midcom_helper_toolbars::get_instance();
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'config.html',
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
	        MIDCOM_TOOLBAR_HIDDEN =>
	        (
	               ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
	            || ! $_MIDCOM->auth->can_do('midcom:component_config', $this->_topic)
	        )
        ));
       // this should be in article.php.
        if (0) foreach (array_reverse($this->_schemadb_article_index, true) as $name => $desc)
        {
            $text = sprintf($this->_l10n_midcom->get('create %s'), $desc);
            $toolbar->top->add_item(
            	Array
                (
	                MIDCOM_TOOLBAR_URL => "create/{$name}.html",
	                MIDCOM_TOOLBAR_LABEL => $text,
	                MIDCOM_TOOLBAR_HELPTEXT => null,
	                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
	                MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $this->_topic) == false)
                ), 0);
        }

    	$current_topic = $this->_current_topic;
		// topic stuff
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "styleeditor/topic/create/{$current_topic}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("create subtopic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "styleeditor/topic/edit/{$current_topic}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("edit topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit-folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "styleeditor/topic/score/{$current_topic}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("edit order"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "styleeditor/topic/delete/{$current_topic}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("delete topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        /* todo: make attachemntshandler */
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "attachment/{$current_topic}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("topic attachments"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true
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

        if (mgd_is_topic_owner($topic->id))
        {


            $prefix = "{$this->viewdata['admintopicprefix']}meta/{$nap_obj[MIDCOM_NAV_GUID]}";

            $toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => null,
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('metadata for %s'), $nap_obj[MIDCOM_NAV_NAME]),
                MIDCOM_TOOLBAR_HELPTEXT => "GUID: {$nap_obj[MIDCOM_NAV_GUID]}" ,
                MIDCOM_TOOLBAR_ICON => null,
                MIDCOM_TOOLBAR_ENABLED => false,
            ));

            if ($meta->is_approved())
            {
                $toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "{$prefix}/unapprove.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('unapprove'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('approved'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                ));
            }
            else
            {
                $toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "{$prefix}/approve.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('approve'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('unapproved'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                ));
            }

            if ($meta->get('hide'))
            {
                $toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "{$prefix}/unhide.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('unhide'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('hidden'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/hidden.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                ));
            }
            else
            {
                $toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "{$prefix}/hide.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('hide'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('not hidden'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_hidden.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                ));
            }

            $toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => "{$prefix}/edit.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('edit metadata'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            ));

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
	                $toolbar->add_item(Array(
	                    MIDCOM_TOOLBAR_URL => null,
	                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('scheduled and shown'),
	                    MIDCOM_TOOLBAR_HELPTEXT => $text,
	                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/scheduled_and_shown.png',
	                    MIDCOM_TOOLBAR_ENABLED => false,
	                ));
                }
                else
                {
	                $toolbar->add_item(Array(
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

	/**
     * @return bool Indicating success.
	 */
   	function _handler_index() {

   		return true;
   	}

    function _show_index() {
       midcom_show_style('index');
    }


    /**
     * Display the content, it uses the handler as determined by can_handle.
     * This overrides the basic show method of the class to include the ais style around the component.
     *
     * @see _on_show();
     */
    function show()
    {
     	debug_push_class($this, 'show');



        // Call the event handler
        $result = $this->_on_show($this->_handler['id']);
        if (! $result)
        {
            debug_add('The _on_show event handler returned false, aborting.');
            debug_pop();
            return;
        }

        // Call the handler:
        $handler =& $this->_handler['handler'][0];
        $method = "_show_{$this->_handler['handler'][1]}";

        $handler->$method($this->_handler['id'], $this->_request_data);
        debug_pop();
    }

}

?>
