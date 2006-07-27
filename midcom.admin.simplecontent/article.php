<?php
/**
 * Created on Aug 3, 2005
 *
 * Create, edit and delete articles
 * urls:
 * /$id = view article $id
 * /edit/$id edit article $id
 * /create/$topic/$schema create new article in topiv $topic
 * /delete/$id delete article with id.
 * 
 * $this->_topic is set from the current article or from argv[0] in the case of create. 
 * @package midcom.admin.simplecontent
 */
 
 
class midcom_admin_simplecontent_article extends midcom_baseclasses_components_handler  {
/**
     * The schema database accociated with the topic, defaults to the one in the config dir.
     * 
     * @var Array
     * @access private
     */
    var $_schemadb_article = Array();
    /**
     * An index over the schema database accociated with the topic mapping
     * schema keys to their names. For ease of use.
     * 
     * @var Array
     * @access private
     */
    var $_schemadb_article_index = Array();
    
    /**
     * The article to show, or null in case that there is no article set at this time.
     * The request data key 'article' is set to a reference to this member during 
     * class startup.
     * 
     * @var midcom_baseclasses_database_article
     * @access private
     */
    var $_article = null;
    
    /**
     * The datamanager instance controlling the article to show, or null in case there
     * is no article at this time. The request data key 'datamanager' is set to a 
     * reference to this member during class startup.
     * 
     * @var midcom_helper_datamanager
     * @access private
     */
    var $_datamanager = null;
    
   /**
     * The topic in which to look for articles. This defaults to the current content topic
     * 
     * @var midcom_baseclasses_database_topic
     * @access private
     */
    var $_topic = null;
    
    var $_config = array();
   /**
     * Pointer to the requestdata from the main class
     * @var midcom_requestdata (?)
     * @access private  
     */
    var $_request;
    /**
     * Pointer to the toolbarobject
     * @access private
     * @var midcom_helper_toolbars 
     */
     var $_toolbars = null;
    /**
     * Session object used for passing messages.
     */
    var $_session = null;
    
    /** 
     * Is the current topic a midcom? If so let's do some magic :)
     * @var  boolean is it a topic?
     * @access private
     */
     var $_is_midcom = false;
    
	function midcom_admin_simplecontent_article  () 
    {
	         parent::midcom_baseclasses_components_handler();
	}
	
	function _on_initialize() 
    {
		// Populate the request data with references to the class members we might need
        $this->_request_data['article'] =& $this->_article;
        $this->_request_data['datamanager'] = & $this->_datamanager;
	    $this->_load_schema_database();
        $this->_request_data['object_type'] = 'article';
        $this->_request_data['is_midcom']   = false;
        $this->_request_data['toolbars']    = & midcom_helper_toolbars::get_instance();        
        
	} 

    /**
     * This internal helper loads the article identified by the passed argument from the database.
     * When returning false, it sets errstr and errcode accordingly, you justt have to pass the result
     * to the handle callee.
     * 
     * In addition, it will set the currently active leaf to the set ID.
     * 
     * @param mixed $id A valid article identifier that can be used to load an article from the database. 
     *     This can either be an ID or a GUID.
     * @return bool Indicating success.
     * @access private 
     */
    function _load_article($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
    	debug_add("Trying to load the article with the ID {$id}.");
        
        $this->_article = new midcom_baseclasses_database_article($id);
        if (! $this->_article)
        {
            $this->errstr = "Failed to load the article with the id {$args[0]}: This usually means that the article was not found. (See the debug level log for more information.)";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        
        $this->_topic = new midcom_baseclasses_database_topic($this->_article->topic);
        $this->_request_data['aegir_interface']->set_current_leaf( $this->_article->id);
        if ( ($this->_midcom_component = $this->_topic->get_parameter('midcom','component') ) != '') {
            $this->_is_midcom = true;
            $this->_request_data['is_midcom'] = true;
        }
        $this->_component_data['active_leaf'] = $id;
        if (array_key_exists('aegir_interface', $this->_request_data)) {
            $component_nav = $this->_request_data['aegir_interface']->get_navigation();
            $nodepath = $component_nav->get_breadcrumb_array();
           
            for ($i = count($nodepath) -1; $i >= 0;$i--) {
                $this->_request_data['toolbars']->aegir_location->add_item(
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
        }
        debug_pop();
        return true;
    }    
    
    /**
     * Prepares the datamanager for the loaded article. When returning false, 
     * it sets errstr and errcode accordingly.
     * 
     * @return bool Indicating success
     * @access private
     */
    function _prepare_datamanager($view = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_datamanager = new midcom_helper_datamanager($this->_schemadb_article);
        if (! $this->_datamanager)
        {
            $this->errstr = 'Could not create the datamanager instance, see the debug level logfile for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        $this->_datamanager->set_show_javascript($view);
        if (! $this->_datamanager->init($this->_article, 'article')) 
        {
            $this->errstr = 'Could not initialize the datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        
        debug_pop();
        return true;
    }
        //TODO : remove
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
     * Prepares the datamanager for creation of a new article. When returning false, 
     * it sets errstr and errcode accordingly.
     * 
     * @param string $schema The name of the schema to initialize for
     * @return bool Indicating success
     * @access private
     */
    function _prepare_creation_datamanager($schema)
    {
        $this->_datamanager = new midcom_helper_datamanager($this->_schemadb_article);
        if (! $this->_datamanager)
        {
            $this->errstr = 'Failed to create a datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT; 
            return false;
        }
        // show js if the editor needs it.
        $this->_datamanager->set_show_javascript(true);
        if (! $this->_datamanager->init_creation_mode($schema, $this))
        {
            $this->errstr = "Failed to initialize the datamanger in creation mode for schema '{$schema}'.";
            $this->errcode = MIDCOM_ERRCRIT; 
            return false;
        }
        return true;
    }
    
    /**
     * This internal helper adds the edit and delete links to the local toolbar.
     * 
     * @access private
     */
    function _prepare_local_toolbar()
    {
        /* call static instance to make sure the topic toolbar is also added. */
        midcom_admin_simplecontent_topic::prepare_topic_toolbar();
		$this->_toolbars = &midcom_helper_toolbars::get_instance();
        $this->_toolbars->bottom->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/article/edit/{$this->_article->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true,
			MIDCOM_TOOLBAR_HIDDEN => (0 &&($_MIDCOM->auth->can_do('midgard:update', $this->_article) == false))
        ));
        $this->_toolbars->bottom->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/article/delete/{$this->_article->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (0 &&($_MIDCOM->auth->can_do('midgard:delete', $this->_article) == false))
        ));
        /* disabled until further notice.
        $this->_toolbars->bottom->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/article/copy/{$this->_article->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('copy'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (0 &&($_MIDCOM->auth->can_do('midgard:copy', $this->_article) == false))
        ));
        */
        $this->_toolbars->bottom->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/article/move/{$this->_article->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('move'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $this->_article) == false))
        ));
        foreach (array_reverse($this->_schemadb_article_index, true) as $name => $desc) 
        { 
            $text = sprintf($this->_l10n_midcom->get('create %s'), $desc);
            $this->_toolbars->bottom->add_item(
            	Array 
                (
	                MIDCOM_TOOLBAR_URL => "simplecontent/article/create/{$this->_topic->id}/{$name}.html", 
	                MIDCOM_TOOLBAR_LABEL => $text,
	                MIDCOM_TOOLBAR_HELPTEXT => null,
	                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
	                MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $this->_topic) == false)
                ), 0);
        }
        $this->_toolbars->bottom->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/parameters/{$this->_article->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_request_data["l10n"]->get("Edit article parameters"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => !( $_MIDCOM->auth->can_do('midgard:update', $this->_article->guid)
                                     && $_MIDCOM->auth->can_do('midgard:parameters', $this->_article->guid))
            
        ));
        $this->_toolbars->bottom->add_item(Array(
            MIDCOM_TOOLBAR_URL => "rcs/midcom.admin.simplecontent/{$this->_article->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('History'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $this->_article) == false))
        ));
        
    }
    
    /**
     * Renders the welcome page.
     * 
     * @access private
     */
    function _show_welcome ($handler_id, &$data)
    {
        $data['schemadb_index'] = $this->_schemadb_article_index;
        midcom_show_style('admin_welcome');
    }
 
  /**
     * Internal helper, loads the configured schema database into the class.
     * It is not yet evaluated by a datamanager, only the file is loaded.
     * 
     * @see $_schemadb_article
     * @see $_schemadb_article_index
     * @access private
     */
    function _load_schema_database()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (array_key_exists('aegir_interface', $this->_request_data)) {
            $path = $this->_request_data['aegir_interface']->module_config->get('schemadb_article');
        } else {
            $path = $this->_request_data['config']->get('schemadb_article');
        }
        $data = midcom_get_snippet_content($path);
        eval("\$this->_schemadb_article = Array ({$data}\n);");
        
        // This is a compatibility value for the configuration system
        //TODO: remove
        //$GLOBALS['de_linkm_taviewer_schemadbs'] =& $this->_schemadbs;
        
        if (is_array($this->_schemadb_article))
        {
            if (count($this->_schemadb_article) == 0)
            {
                debug_add('The schema database was empty, we cannot use this.', MIDCOM_LOG_ERROR);
                debug_print_r('Evaluated data was:', $data);
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Could not load the schema database accociated with this topic: The schema DB was empty.');
                // This will exit.
            }
            foreach ($this->_schemadb_article as $schema)
            {
                $this->_schemadb_article_index[$schema['name']] = $schema['description'];
            }
        }
        else
        {
            debug_add('The schema database was no array, we cannot use this.', MIDCOM_LOG_ERROR);
            debug_print_r('Evaluated data was:', $data);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Could not load the schema database accociated with this topic. The schema DB was no array.');
            // This will exit.
        }
        debug_pop();
    }
    
         /**
     * Locates the article to view and prepares everything for the view run.
     * This includes the toolbar preparations and the preparation of the
     * article and datamanager instances.
     * 
     * @access private
     */
    function _handler_article($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Try to load the article and to prepare its datamanager.
        if (count($args ) == 0 ) {
			// todo: add a reloacte here!        
        
        }
        if (   ! $this->_load_article($args[0])
            || ! $this->_prepare_datamanager(false))
        {
            debug_pop();
            return false;
        }
        
        
        $this->_prepare_local_toolbar();
        
        debug_pop();
        return true;
    }
    
    /**
     * Renders the selected article using the datamnager view mode.
     * 
     * @access private
     */
    function _show_article ($handler_id, &$data)
    {
        midcom_show_style('admin_view');
    }
    
    /**
     * Locates the article to edit and sets everything up. When processing the
     * DM results, it will redirect to the view mode on both the save and cancel
     * events.
     *  
     * Preparation include the toolbar setup.
     * 
     * @access private
     */
    function _handler_edit ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Try to load the article and to prepare its datamanager.
        if (   ! $this->_load_article($args[0])
            || ! $this->_prepare_datamanager(true))
        {
            debug_pop();
            return false;
        }
        
        $this->_prepare_local_toolbar();
        $toolbars = &midcom_helper_toolbars::get_instance();
        // Disable all toolbar items while editing:
        if (0 && $this->_is_midcom) {
            $toolbars->top->disable_item("simplecontent/topic/config/{$this->_topic->id}.html");
        }
        foreach ($this->_schemadb_article_index as $name => $desc) 
        { 
            //$toolbars->top->disable_item("simplecontent/article/create/{$name}.html");
        }
        $toolbars->bottom->disable_item("simplecontent/article/edit/{$this->_article->id}.html");
        $toolbars->bottom->disable_item("simplecontent/article/delete/{$this->_article->id}.html");
        //$toolbars->disable_view_page();

        // Patch the active schema, see there for details.
        $this->_patch_active_schema();
        
        // Now launch the datamanger processing loop
        switch ($this->_datamanager->process_form()) 
        {
            case MIDCOM_DATAMGR_EDITING:
                break;

            case MIDCOM_DATAMGR_SAVED:
                if (   $this->_article->name == '' 
                    || $this->_missing_index)
                {
                    // Empty URL name or missing index article, generate it and 
                    // refresh the DM, so that we can index it.
                    $this->_article = $this->_generate_urlname($this->_article);
                    $this->_datamanager->init($this->_article, 'article');
                } 
                
                // Reindex the article 
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);
                
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("simplecontent/article/{$this->_article->id}.html");
                // This will exit()

            case MIDCOM_DATAMGR_CANCELLED:
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("simplecontent/article/{$this->_article->id}.html");
                // This will exit()
                
            case MIDCOM_DATAMGR_FAILED:
                $this->errstr = "The Datamanager failed critically while processing the form, see the debug level log for more details.";
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }
        
        debug_pop();
        return true;
    }
    
    /**
     * Renders the selected article using the datamnager view mode.
     * 
     * @access private
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('admin_edit');
    }
    
    /**
     * Locates the article to delete and prepares everything for the view run,
     * there the user has to confirm the deletion. This includes the toolbar 
     * preparations and the preparation of the
     * article and datamanager instances.
     * 
     * @access private
     */
    function _handler_delete ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Try to load the article and to prepare its datamanager.
        $obj = mgd_get_new_object_by_guid($args[0]);
        if (   ! $this->_load_article($obj->id)
            || ! $this->_prepare_datamanager(true))
        {
            debug_pop();
            return false;
        }

        // Prepare the toolbars        
        $this->_prepare_local_toolbar();
        $this->_toolbars->bottom->disable_item("simplecontent/article/delete/{$this->_article->id}.html");
        $this->_request_data['title'] = $this->_request_data['l10n']->get('delete article') .': '. htmlspecialchars($this->_article->title);
        if (array_key_exists('admin_content_simplecontent_deleteok', $_REQUEST)) 
        {
            return $this->_delete_record();
            // This will redirect to the welcome page on success or
            // returns false on failure setting the corresponding error members.
        } 
        else 
        {
            if (array_key_exists('admin_content_simplecontent_deletecancel', $_REQUEST)) 
            {
                // Redirect to view page.
                $_MIDCOM->relocate("simplecontent/article/{$this->_article->id}.html");
                // This will exit()
            } 
        }
        
        debug_pop();
        return true;
    }
    
    /**
     * Renders the selected article using the datamnager view mode.
     * 
     * @access private
     */
    function _show_delete ($handler_id, &$data)
    {
        midcom_show_style('admin_deletecheck');
    }
    
    /**
     * Prepares everything to create a new article. When processing the
     * DM results, it will redirect to the view mode on the save event, and to the
     * welcome page otherwise. It uses sessioning to keep track of the newly created
     * acrticle ID.
     *  
     * Preparation include the toolbar setup.
     * 
     * @access private
     */
    function _handler_create ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Read the schema name from the args
        $schema = $args[1];
        $this->_topic = new midcom_baseclasses_database_topic($args[0]);
        // Prepare the topic toolbar, the local toolbar stays empty at this point.
        // Disable all toolbar items while editing:
        $toolbar =& midcom_helper_toolbars::get_instance();
        if ($this->_is_midcom) {
            $toolbars->top->disable_item("simplecontent/topic/config/{$this->_topic->id}.html");
        }
        foreach ($this->_schemadb__article_index as $name => $desc) 
        { 
            //$toolbar->bottom->disable_item("create/{$this->_topic->id}/{$name}.html");
        }
        //$toolbar->disable_view_page();

               
        // If applicable, patch the schema database for the index article creation.
        if (   array_key_exists('create_index', $_REQUEST) 
            && $_REQUEST['create_index'] == 1) 
        {
            debug_add('We are creating a new index article, thus we modify the schema a bit and display an notification.');
            $this->_schemadb_article[$schema]['fields']['name']['default'] = 'index';
            $this->_schemadb_article[$schema]['fields']['name']['readonly'] = true;
            $GLOBALS['view_contentmgr']->msg .= $this->_l10n->get('no index article') . "<br />\n";
        }
        
        // Initialize sessioning first        
        $session = new midcom_service_session();

        // Start up the Datamanager in the usual session driven create loop
        // (create mode if seesion is empty, otherwise regular edit mode)
        if (! $session->exists('admin_create_id'))
        {
            debug_add('We do not currently have a content object, entering creation mode.');
            
            $this->_article = null;
            $this->_schemadb_article[$schema]['fields']['sitegroup']['default'] = $this->_topic->sitegroup;
            $this->_schemadb_article[$schema]['fields']['sitegroup']['hidden'] = true;
            $this->_schemadb_article[$schema]['fields']['id']['hidden'] = true;
            $this->_schemadb_article[$schema]['fields']['guid']['hidden'] = true;
            if (! $this->_prepare_creation_datamanager($schema))
            {
                debug_pop();
                return false;
            }

            $create = true;
        }
        else
        {
            $id = $session->get('admin_create_id');
            debug_add("We have found the article id {$id} in the session, loading object and entering regular edit mode.");
            
            // Try to load the article and to prepare its datamanager.
	        if (   ! $this->_load_article($id)
	            || ! $this->_prepare_datamanager())
	        {
                $session->remove('admin_create_id');
	            debug_pop();
	            return false;
	        }
            
            $create = false;
        }

        // Ok, we have a go.        
        switch ($this->_datamanager->process_form()) 
        {
            case MIDCOM_DATAMGR_CREATING:
                if (! $create) 
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMANAGER_CREATING unknown for non-creation mode.';
                    debug_pop();
                    return false;
                } 
                else 
                {
                    debug_add('First call within creation mode');
                    $this->_view = 'create';
                    break;
                }
            
            case MIDCOM_DATAMGR_EDITING:
                if ($create) 
                {
                    $id = $this->_article->id;
                    debug_add("First time submit, the DM has created an object, adding ID {$id} to session data");
                    $session->set('admin_create_id', $id);
                } 
                else 
                {
                    debug_add('Subsequent submit, we already have an id in the session space.');
                }
                $this->_view = 'create';
                break;
            
            case MIDCOM_DATAMGR_SAVED:
                debug_add('Datamanger has saved, relocating to view.');
                if ($this->_article->name == "" || $this->_missing_index)
                {
                    // Empty URL name or missing index article, generate it
                    $this->_article = $this->_generate_urlname($this->_article);
                }
                $session->remove('admin_create_id');
                
                // Reindex the article 
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);
                
                $_MIDCOM->relocate("simplecontent/article/{$this->_article->id}.html");
                // This will exit

            
            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                if (! $create) 
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED_NONECREATED unknown for non-creation mode.';
                    debug_pop();
                    return false;
                } 
                else 
                {
                    debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                    $_MIDCOM->relocate('');
                    // This will exit
                }
            
            case MIDCOM_DATAMGR_CANCELLED:
                if ($create) 
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                    debug_pop();
                    return false;
                } 
                else 
                {
                    debug_add('Cancel with a temporary object, deleting it and redirecting to the welcome screen.');
                    if (! mgd_delete_extensions($this->_article) || ! $this->_article->delete())
                    {
                        $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                            'Failed to remove temporary article or its dependants.');
                        // This will exit
                    }
                    $session->remove('admin_create_id');
                    $_MIDCOM->relocate('');
                    // This will exit
                }
            
            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanger failed to process the request, see the debug level log for details.';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            
            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;
            
        }
        
        debug_pop();
        return true;
    }
    
    /**
     * General request initialization, which populates the topic toolbar.
     */
    function _on_handle($handler_id, $args)
    {
        $this->_prepare_topic_toolbar();
        return true;
    }
    
    /**
     * Populate a single global variable with the current schema database, so that the
     * configuration schema works again.
     * 
     * @todo Rewrite this to use the real schema select widget, which is based on some
     *     other field which contains the URL of the schema.
     */
    function _on_handler_config_dm_preparing() 
    {
    	$GLOBALS['de_linkm_taviewer_schemadbs'] = array_merge
        (
            Array
            (
                '' => $this->_l10n->get('default setting')
            ),
            $this->_config->get('schemadbs')
        );
        
    }
    
    /**
     * Renders the selected article using the datamnager view mode.
     * 
     * @access private
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('admin_create');
    }
    
    /**
     * Internal helper, creates a valid name for a given article. It calls
     * generate_error on any failure.
     * 
     * @param midcom_baseclasses_database_article $article The article to process, if omitted, the currently selected article is used instead.
     * @return midcom_baseclasses_database_article The updated article.
     * @access private
     */
    function _generate_urlname($article = null) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (!$article) 
        {
            $article = $this->_article;
        }
        
        $updated = false;
        
        if (   $this->_missing_index 
            && ! $this->_config->get('autoindex')) 
        {
            // Note that this code-block probably executes very seldomly, as the missing
            // index is caught during article creation. It could only happen if
            // you rename an index article forcefully, so this check should stay
            // here.
            $article->name = 'index';
            $updated = $article->update();
        } 
        else 
        {
            $tries = 0;
            $maxtries = 99;
            while(    ! $updated 
                  && $tries < $maxtries) 
            {
                $article->name = midcom_generate_urlname_from_string($article->title);
                if ($tries > 0) 
                {
                    // Append an integer if articles with same name exist
                    $article->name .= sprintf("-%03d", $tries);
                }
                $updated = $article->update();
                $tries++;
            }
        }
        
        if (! $updated) 
        {
            debug_print_r('Failed to update the Article with a new URL, last article state:', $article);
            $_MIDCOM->generate_error('Could not update the article\'s URL Name: ' . mgd_errstr());
            // This will exit()
        }
        
        debug_pop();
        return $article;
    }

    /**
     * Callback for the datamanager create mode.
     * 
     * @access protected
     */
    function _dm_create_callback(&$datamanager) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $result = Array 
        (
            'success' => true,
            'storage' => null,
        );
        
        
        $this->_article = new midcom_baseclasses_database_article();
        
        if (0 &&    array_key_exists('create_index', $_REQUEST) 
            && $_REQUEST['create_index'] == 1) 
        {
            $this->_article->name = 'index';
        }
        //$this->_article->topic = $this->_topic->id;
        $this->_article->author = $_MIDCOM->auth->user->id;
        if (! $this->_article->create()) 
        {
            debug_add('Could not create article: ' . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_add('Could not create article {$this->_article->name} , {$this->_article->topic}: ' . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }
        
        if ( $this->_config->get('auto_approved') == true ) 
        {
            $meta =& midcom_helper_metadata::retrieve($this->_article);
            $meta->approve();
        }
        
        $result['storage'] =& $this->_article;
        debug_pop();
        return $result;
    }

    /**
     * Deletes the currently active article and all of its extensions.
     * On success, it will return to the welcome page, on failure, it returns false. 
     * 
     * @return bool Indicating success
     * @access private
     */
    function _delete_record() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->_article->delete()) 
        {
            $this->errstr = "Could not delete Article {$this->_article->id}: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_pop();
            return false;
        }
        
        // Update the index
        $indexer =& $_MIDCOM->get_service('indexer');
        $indexer->delete($guid);
        
        // Invalidate the cache modules
        $_MIDCOM->cache->invalidate($guid);
        
        // Redirect to welcome page.
        $GLOBALS['midcom']->relocate('');
        // This will exit()
    }


    /**
     * Return the metadata of the current article.
     */
    function get_metadata() 
    {
        if (is_null($this->_article)) 
        {
            return false;
        }
        return array (
            MIDCOM_META_CREATOR => $this->_article->creator,
            MIDCOM_META_EDITOR  => $this->_article->revisor,
            MIDCOM_META_CREATED => $this->_article->created,
            MIDCOM_META_EDITED  => $this->_article->revised
        );
    }

    /**
     * Internal helper, called before the edit form is shown.
     * 
     * This is a rather bloody hack to modify the schema while the datamanager
     * is already up and running. It will make the url name field read-only 
     * if the current user is not a power user or admin and if we are looking
     * at the index article.
     * 
     * @todo Move the API to use the new MidCOM ACL stuff (and add acl studd)
     * @access private
     */
    function _patch_active_schema() 
    {
        if (   $this->_article->name == 'index' )
        {
                $this->_datamanager->_fields["name"]["readonly"] = true;
            
        }
    }

    
    function _handler_move($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (count($args ) == 0 ) {
            // todo: add a relocate here!
            // (though you should never end up here. )        
        }
        if (   ! $this->_load_article($args[0])) // removed DM start here -> not needed!
        {
            debug_add("Could not load article with id {$args[0]}. Aborting.");
            debug_pop();
            return false;
        }
        
        /* use this to pass params between relocates etc. */
        $this->_session =  new midcom_service_session();
        $this->_prepare_local_toolbar();

        /* f_copyto might not be set if the user pressed false */
        if (array_key_exists('f_moveto', $_POST) ) {
            $topic = new midcom_baseclasses_database_topic($_POST['f_moveto']);
            // you must have write priveledges to the topic you are moving to.
            
            if ($_MIDCOM->auth->can_do('midcom:create', $topic)) {
                $this->_article->topic = $_POST['f_moveto'];
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                if ($this->_article->update()) {
                    $this->_session->set('msg', $this->_l10n->get('Article moved sucessfully.'));
                    debug_add("Article {$this->_article->title}, id. {$this->_article->title} moved to topic {$topic->name}, id: {$topic->id}", MIDCOM_LOG_ERROR);
                    $_MIDCOM->relocate($prefix . 'simplecontent/article/'. $this->_article->id);
                } else {
                    // todo : add errorlog.
                    $this->_session->set('msg', $this->_l10n->get('Article not moved'));
                    debug_add("Article {$this->_article->title}, id. {$this->_article->title} _NOT_ moved to topic {$topic->name}, id: {$topic->id}", MIDCOM_LOG_ERROR);
                }
            } else {
                /* copyto not set this means that the user pressed cancel. */
                $this->_session->set('msg', $this->_l10n->get('Access denied. You cannot move the article there.') );
                debug_add('Move Access denied to object' . $topic->id . " " . mgd_errstr(), MIDCOM_LOG_WARN);
                
                $this->_request_data['first_run'] = true;
            }
        } else {
            
            $this->_request_data['first_run'] = true;
        }
        $this->_request_data['aegir_interface']->set_current_leaf = $this->_article->guid;
        $this->_request_data['article_name'] = $this->_article->title;
        
        
        debug_pop();
        return true;
    }
    
    function _show_move() {
    
        if ($this->_request_data['first_run']) {
            midcom_show_style('article-move');
        }
    
    }
}
?>
