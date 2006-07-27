<?php

/**
 * Request class for handling simple topics and articles.
 * @package midcom.admin.simplecontent
 * 
 */

class midcom_admin_simplecontent_viewer  extends midcom_baseclasses_components_request {
    
    var $msg;

	/* the current topic we are in 
	 * @var current_topic
	 * @access public 
	 **/
	var $_current_topic = 0;
    /* pointer to midcom_session_object */
    var $_session = null;

    /* Should we be aegir now?  */
    var $_is_aegir = false;
    
    /* the toolbar here for now ;)*/
    var $toolbar = null;
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     * 
     * @var midcom_baseclasses_database_topic
     * @access private
     */
    var $_content_topic = null;
    
    /**
     * The schema database accociated with articles.
     * 
     * @var Array
     * @access private
     */
    var $_schemadb = Array();
    
    /**
     * An index over the schema database accociated with the topic mapping
     * schema keys to their names. For ease of use.
     * 
     * @var Array
     * @access private
     */
    var $_schemadb_article_index = Array();
    

	function midcom_admin_simplecontent_viewer($topic, $config) 
    {
    	//$page = mgd_get_object_by_guid($config->get("root_page"));
        parent::midcom_baseclasses_components_request($topic, $config);
        
        $this->msg = "";

        $this->_session = new midcom_service_session();
        if ($this->_session->exists('midcom_admin_simplecontent_aegir')  ) {
            $this->_is_aegir = $this->_session->get('midcom_admin_simplecontent_aegir');
        }
        
    }

	function _on_initialize() {
		
                   
       	$_MIDCOM->cache->content->no_cache();
		$_MIDCOM->auth->require_valid_user();
		
		$this->_request_data['localconfig'] = array ('is_aegir' => true, 'msg' => $this->msg);
		/* Check for aegir or normal style: */
        $style_attributes = array ( 'rel'   =>  "stylesheet" ,
                                    'type'  =>  "text/css" ,
                                    'media' =>  "screen"
                                    );
        
        if ($this->_is_aegir) {
        	//TODO add all scripts and fix style!!
        	$_MIDCOM->skip_page_style = true;
            $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/ais.css";
        	$_MIDCOM->add_link_head( $style_attributes); 
            $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/midcom_toolbar.css";
            $_MIDCOM->add_link_head( $style_attributes);
            $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/aegir_style.css";
            $_MIDCOM->add_link_head( $style_attributes);
        } else {
            $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/ais.css";
            $_MIDCOM->add_link_head( $style_attributes);
            $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/midcom_toolbar.css";
            $_MIDCOM->add_link_head( $style_attributes);
            $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/simple_style.css";
            $_MIDCOM->add_link_head( $style_attributes);
            
            $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL ."/midcom.admin.content/navigation_functions.js");                                    
        }
        // argv has the following format: topic_id/mode
		$this->_request_switch[] = Array
        (
            'handler' => 'index',
            
            // No further arguments, we have neither fixed nor variable arguments.
        );
		$this->_request_switch[] = Array
        (
        	'fixed_args' => 'article',
            'handler' => array('midcom_admin_simplecontent_article','article'),
            'variable_args' => 1,
        );
		$this->_request_switch[] = Array
        (
        	'fixed_args' => array('article','edit'),
            'handler' => array('midcom_admin_simplecontent_article','edit'),
            'variable_args' => 1,
        );
       	$this->_request_switch[] = Array
        (
        	'fixed_args' => array('article','create'),
            'handler' => array('midcom_admin_simplecontent_article','create'),
            'variable_args' => 2,
        );
		$this->_request_switch[] = Array
        (
        	'fixed_args' => array('article','delete'),
            'handler' => array('midcom_admin_simplecontent_article','delete'),
            'variable_args' => 1,
        );
        $this->_request_switch[] = Array
        (
            'fixed_args' => array('article','move'),
            'handler' => array('midcom_admin_simplecontent_article','move'),
            'variable_args' => 1,
        );             
        /* disabled until further notice.
        $this->_request_switch[] = Array
        (
            'fixed_args' => array('article','copy'),
            'handler' => array('midcom_admin_simplecontent_article','copy'),
            'variable_args' => 1,
        );             
        */
        $this->_request_switch[] = Array
        (
        	'fixed_args' => 'topic',
            'handler' => array('midcom_admin_simplecontent_topic','topic'),
            'variable_args' => 1,
        );
        
                $this->_request_switch[] = Array
        (
            'fixed_args' => array('topic','edit'),
            'handler' => array('midcom_admin_simplecontent_topic','edit'),
            'variable_args' => 1,
        );
        $this->_request_switch[] = Array
        (
            'fixed_args' => array('topic','create'),
            'handler' => array('midcom_admin_simplecontent_topic','create'),
            'variable_args' => 2,
        );
        $this->_request_switch[] = Array
        (
            'fixed_args' => array('topic','delete'),
            'handler' => array('midcom_admin_simplecontent_topic','delete'),
            'variable_args' => 1,
        );
        $this->_request_switch[] = Array
        (
            'fixed_args' => array('topic','move'),
            'handler' => array('midcom_admin_simplecontent_topic','move'),
            'variable_args' => 1,
        );
        
        $this->_request_switch[] = Array
        (
            'fixed_args' => 'topic',
            'handler' => array('midcom_admin_simplecontent_topic','index'),
            'variable_args' => 0,
        ); 
        
        /*
         * get the rcs requestarray.
         */
        
        $rcs_array =  no_bergfald_rcs_handler::get_request_array();
        foreach ($rcs_array as $key => $switch) {
            $this->_request_switch[] = $switch;
        }
        $this->_request_data['toolbars'] = & midcom_helper_toolbars::get_instance();
        /* add a nice prefix */
        $this->_request_data['toolbars']->aegir_location->add_item(
            array (
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => "Topics and Articles",
                MIDCOM_TOOLBAR_HELPTEXT => '',
                MIDCOM_TOOLBAR_ICON => '',
                MIDCOM_TOOLBAR_ENABLED => false,
                MIDCOM_TOOLBAR_HIDDEN => false 
                )
            );
        /* the way to add a callback for toolbars etc
         */
         $this->_request_data['bergfald_rcs_callback'] = array('midcom_admin_simplecontent_viewer', '_bergfald_rcs_callback');
       
		//$this->_prepare_toolbar();
    }
    /**
     * Callback from the rcs_handler to controll other toolbar and location 
     * optons
     */
    function _bergfald_rcs_callback(&$object) 
    {
        $request_data =& $_MIDCOM->get_custom_context_data('request_data');
        
        $component_nav = new midcom_admin_simplecontent_navigation_new();
         if (is_a($object, 'midcom_baseclasses_database_article')) {
              
              $component_nav->set_current_leaf($object->id);
               @midcom_admin_simplecontent_topic::prepare_topic_toolbar($object->topic);
               
         } else {
            
            $component_nav->set_current_node($object->id);
            @midcom_admin_simplecontent_topic::prepare_topic_toolbar($object->id);
         
         }
         
         
        
        $nodepath = $component_nav->get_breadcrumb_array();
       
        for ($i = count($nodepath) -1; $i >= 0;$i--) {
            $request_data['toolbars']->aegir_location->add_item(
            array (
                MIDCOM_TOOLBAR_URL => $nodepath[$i][MIDCOM_NAV_URL],
                MIDCOM_TOOLBAR_LABEL => $nodepath[$i][MIDCOM_NAV_NAME],
                MIDCOM_TOOLBAR_HELPTEXT => '',
                MIDCOM_TOOLBAR_ICON => '',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_HIDDEN => false 
                )
            );
         }
         //print_r($request_data['toolbars']->aegir_location->items);
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
            MIDCOM_TOOLBAR_URL => "simplecontent/topic/create/{$current_topic}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("create subtopic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/topic/edit/{$current_topic}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("edit topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit-folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/topic/score/{$current_topic}",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("edit order"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true
        )); 
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/topic/delete/{$current_topic}",
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
