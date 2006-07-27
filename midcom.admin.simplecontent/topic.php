<?php
/**
 * Created on Aug 3, 2005
 * @package midcom.admin.simplecontent
 *
 * Create, edit and delete topics
 * urls:
 * simplecontent/$id = view topic $id
 * simplecontent/edit/$id edit topic $id
 * /simplecontent/create/$topic/$schema create new topic in topiv $topic
 * /simplecontent/delete/$id delete topic with id.
 * 
 * $this->_topic is set from the current topic or from argv[0] in the case of create. 
 */
 
class midcom_admin_simplecontent_topic extends midcom_baseclasses_components_handler  {
/**
     * The schema database accociated with the topic, defaults to the one in the config dir.
     * 
     * @var Array
     * @access private
     */
    var $_schemadb_topic = Array();
    /**
     * An index over the schema database accociated with the topic mapping
     * schema keys to their names. For ease of use.
     * 
     * @var Array
     * @access private
     */
    var $_schemadb_topic_index = Array();
    
    /**
     * The topic to show, or null in case that there is no topic set at this time.
     * The request data key 'topic' is set to a reference to this member during 
     * class startup.
     * 
     * @var midcom_baseclasses_database_topic
     * @access private
     */
    var $_topic = null;
    
    /**
     * The datamanager instance controlling the topic to show, or null in case there
     * is no topic at this time. The request data key 'datamanager' is set to a 
     * reference to this member during class startup.
     * 
     * @var midcom_helper_datamanager
     * @access private
     */
    var $_datamanager = null;
    
    
    var $_config = array();
    
    /**
     * Local configuration. 
     */
    var $_localconfig = array();
    
   /**
     * Pointer to the requestdata from the main class
     * TODO: remove
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
      * If this is a midcom ,we'll need som more information.'
      */
    var $_midcom_component = '';
    
    
	function midcom_admin_simplecontent_topic  () {
	         parent::midcom_baseclasses_components_handler();
	}
	
	function _on_initialize() {
		// Populate the request data with references to the class members we might need
        
        $data = midcom_get_snippet_content('file:///midcom/admin/simplecontent/config/config.inc');
        eval("\$this->_localconfig = Array ({$data}\n);");
        
        $this->_request_data['datamanager'] =& $this->_datamanager;
	    $this->_load_schema_database();
        
        /* used by styles to know what to show */
        $this->_request_data['object_type'] = 'topic';
        $this->_request_data['toolbars']    = & midcom_helper_toolbars::get_instance();
        
        $this->_path = $this->_request_data['aegir_interface']->current;
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.simplecontent');
        
	} 

    /**
     * This internal helper loads the topic identified by the passed argument from the database.
     * When returning false, it sets errstr and errcode accordingly, you justt have to pass the result
     * to the handle callee.
     * 
     * In addition, it will set the currently active leaf to the set ID.
     * 
     * @param mixed $id A valid topic identifier that can be used to load an topic from the database. 
     *     This can either be an ID or a GUID.
     * @return bool Indicating success.
     * @access private 
     */
    function _load_topic($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
    	debug_add("Trying to load the topic with the ID {$id}.");
        
        $this->_topic = new midcom_db_topic();
        if (! $this->_topic->get_by_id($id))
        {
            $this->errstr = "Failed to load the topic with the id {$id[0]}: This usually means that the topic was not found. (See the debug level log for more information.)";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        
        // todo: should this be null?
        $this->_component_data['active_leaf'] = $id;
            
        
        $this->_request_data['aegir_interface']->set_current_node($this->_topic->id);
        $this->_request_data['aegir_interface']->generate_location_bar();
        /*
        $component_nav->set_current_node($this->_topic->id);
        $component_nav = & $this->_request_data['aegir_interface']->get_navigation();
        $nodepath = $component_nav->get_breadcrumb_array();
       
        for ($i = count($nodepath) -1; $i >= 0;$i--) {
            $this->_request_data['toolbars']->aegir_location->add_item(
            array (
                MIDCOM_TOOLBAR_URL =>  $nodepath[$i][MIDCOM_NAV_URL],
                MIDCOM_TOOLBAR_LABEL => $nodepath[$i][MIDCOM_NAV_NAME],
                MIDCOM_TOOLBAR_HELPTEXT => '',
                MIDCOM_TOOLBAR_ICON => '',
                MIDCOM_TOOLBAR_ENABLED => true,
                MIDCOM_TOOLBAR_HIDDEN => false 
                )
            );
        }
        */
        debug_pop();
        return true;
    }    /**
     * Prepares the datamanager for the loaded topic. When returning false, 
     * it sets errstr and errcode accordingly.
     * 
     * @return bool Indicating success
     * @access private
     * @param bool view to signal to datamanager that it will show the edit form. 
     */
    function _prepare_datamanager($view = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_datamanager = new midcom_helper_datamanager($this->_schemadb_topic);
        if (! $this->_datamanager)
        {
            $this->errstr = 'Could not create the datamanager instance, see the debug level logfile for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        $this->_datamanager->set_show_javascript($view);
        if (! $this->_datamanager->init($this->_topic, 'topic')) 
        {
            $this->errstr = 'Could not initialize the datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }
    
    /**
     * Prepares the datamanager for creation of a new topic. When returning false, 
     * it sets errstr and errcode accordingly.
     * 
     * @param string $schema The name of the schema to initialize for
     * @return bool Indicating success
     * @access private
     */
    function _prepare_creation_datamanager($schema)
    {
        $this->_datamanager = new midcom_helper_datamanager($this->_schemadb_topic);
        if (! $this->_datamanager)
        {
            $this->errstr = 'Failed to create a datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT; 
            return false;
        }
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
        /*the topic toolbar is global.. */
        $this->prepare_topic_toolbar();
        return;
        
    }
    /**
     * This function adds all of the standard items (configuration and create links)
     * to the topic toolbar.
     * Static function (!)
     * Either use the params of the function, or add them in the call.
     * @param integer topic_id
     * @param string topic_guid
     * @access public (article.php must be able to access it)
     */
    
    function prepare_topic_toolbar($topic_id = null, $topic_guid = null)
    {
        $request_data =& $_MIDCOM->get_custom_context_data('request_data');
        $request_data['aegir_interface']->prepare_toolbar();
        return true;
        $nav = &$request_data['aegir_interface']->get_navigation();
        
        if (is_null($topic_id) && is_null($topic_guid)) {
            $current_topic = $request_data['aegir_interface']->get_current_node();
            $topic = new midcom_db_topic ($current_topic);
            
            $current_topic_guid = $topic->guid;
        } else {
            $current_topic = $topic_id;
            $current_topic_guid = $topic_guid;
        }
        
        $toolbar = &midcom_helper_toolbars::get_instance();

        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => $this->_path . "/topic/configure/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n_midcom"]->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $request_data["l10n_midcom"]->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => 
            (
                   ! $_MIDCOM->auth->can_do('midgard:update', $current_topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $current_topic)
            )
        ));
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => $this->_path . "/topic/edit/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("edit topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit-folder.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $this->_topic)
               || $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
              )
            
        ));
       // this should be in article.php. 
        if (0) foreach (array_reverse($this->_schemadb_article_index, true) as $name => $desc) 
        { 
            $text = sprintf($request_data["l10n_midcom"]->get('create %s'), $desc);
            $toolbar->top->add_item(
                Array 
                (
                    MIDCOM_TOOLBAR_URL => $this->_path . "/create/{$name}/topic.html", 
                    MIDCOM_TOOLBAR_LABEL => $text,
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $current_topic) == false)
                ), 0);
        }
    
        // topic stuff
         
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => $this->_path . "/topic/create/{$current_topic}/topic.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("create subtopic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:create', $this->_topic)
              )
            
        ));
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => $this->_path . "/topic/score/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("edit order"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => ! $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
            
        )); 
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => $this->_path . "/topic/delete/{$current_topic_guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("delete topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:delete', $this->_topic)
              )
            
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => $this->_path . "/topic/move/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n_midcom"]->get('move'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $current_topic) == false))
        ));
        /* todo make attachmentshandler... */
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => $this->_path . "/topic/attachment/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("topic attachments"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midgard:attachments', $this->_topic)
               && $_MIDCOM->auth->can_do('midgard:update', $this->_topic)
              )
            
        ));
   
        if (!is_null($topic_guid)) {
            $toolbar->top->add_item(Array(
                MIDCOM_TOOLBAR_URL => "rcs/history/{$current_topic_guid}.html",
                MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("topic revisions"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
                MIDCOM_TOOLBAR_ENABLED => true
            ));
        }
 
               
    }
    
    /**
     * Renders the welcome page.
     * 
     * @access private
     */
    function _show_welcome ($handler_id, &$data)
    {
        $data['schemadb_index'] = $this->_schemadb_topic_index;
        midcom_show_style('admin_welcome');
    }
 
  /**
     * Internal helper, loads the configured schema database into the class.
     * It is not yet evaluated by a datamanager, only the file is loaded.
     * 
     * @see $_schemadb_topic
     * @see $_schemadb_topic_index
     * @access private
     */
    function _load_schema_database()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $path = $this->_localconfig['schemadb_topic'];
        
        $data = midcom_get_snippet_content($path);
        eval("\$this->_schemadb_topic = Array ({$data}\n);");
        // This is a compatibility value for the configuration system
        //$GLOBALS['de_linkm_taviewer_schemadb_topics'] =& $this->_schemadb_topics;
        
        if (is_array($this->_schemadb_topic))
        {
            if (count($this->_schemadb_topic) == 0)
            {
                debug_add('The schema database was empty, we cannot use this.', MIDCOM_LOG_ERROR);
                debug_print_r('Evaluated data was:', $data);
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Could not load the schema database accociated with this topic: The schema DB was empty.');
                // This will exit.
            }
            foreach ($this->_schemadb_topic as $schema)
            {
                $this->_schemadb_topic_index[$schema['name']] = $schema['description'];
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
     * Locates the topic to view and prepares everything for the view run.
     * This includes the toolbar preparations and the preparation of the
     * topic and datamanager instances.
     * 
     * @access private
     */
    function _handler_topic($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Try to load the topic and to prepare its datamanager.
        if (count($args ) == 0 ) {
			// todo: add a reloacte here!        
        
            }
        if (   ! $this->_load_topic($args[0])
            || ! $this->_prepare_datamanager())
        {
            debug_pop();
            return false;
        }
        
        
        $this->_prepare_local_toolbar();
        
        debug_pop();
        return true;
    }
    
    /**
     * Renders the selected topic using the datamnager view mode.
     * 
     * @access private
     */
    function _show_topic ($handler_id, &$data)
    {
        midcom_show_style('admin_view');
    }
    
    /**
     * Locates the topic to edit and sets everything up. When processing the
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
        
        // Try to load the topic and to prepare its datamanager.
        
        if (   ! $this->_load_topic($args[0])
            || ! $this->_prepare_datamanager())
        {
            debug_pop();
            return false;
        }
        
        $this->_prepare_local_toolbar(false);
        
                
        //$this->_request_data['toolbars']->top->disable_item("simplecontent/topic/edit/{$this->_topic->id}.html");
        //$this->_request_data['toolbars']->top->disable_item("simplecontent/topic/delete/{$this->_topic->guid}.html");
        //$toolbars->disable_view_page();

        // Patch the active schema, see there for details.
        $this->_patch_active_schema();
        
        // Now launch the datamanger processing loop
        switch ($this->_datamanager->process_form()) 
        {
            case MIDCOM_DATAMGR_EDITING:
                break;

            case MIDCOM_DATAMGR_SAVED:
                if (   $this->_topic->name == '' 
                    || $this->_missing_index)
                {
                    // Empty URL name or missing index topic, generate it and 
                    // refresh the DM, so that we can index it.
                    $this->_topic = $this->_generate_urlname($this->_topic);
                    $this->_datamanager->init($this->_topic, 'topic');
                } 
                
                // Reindex the topic 
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);
                
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("simplecontent/topic/{$this->_topic->id}.html");
                // This will exit()

            case MIDCOM_DATAMGR_CANCELLED:
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("simplecontent/topic/{$this->_topic->id}.html");
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
     * Renders the selected topic using the datamnager view mode.
     * 
     * @access private
     */
    function _show_edit ($handler_id, &$data)
    {
        midcom_show_style('admin_edit');
    }
    
    /**
     * Locates the topic to delete and prepares everything for the view run,
     * there the user has to confirm the deletion. This includes the toolbar 
     * preparations and the preparation of the
     * topic and datamanager instances.
     * 
     * @access private
     */
    function _handler_delete ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Try to load the topic and to prepare its datamanager.
        $obj = mgd_get_new_object_by_guid($args[0]);
        if (   ! $this->_load_topic($obj->id)
            || ! $this->_prepare_datamanager(false))
        {
            debug_pop();
            return false;
        }

        $up = $this->_topic->up;
        // Prepare the toolbars        
        $this->_prepare_local_toolbar();
        $toolbars = &midcom_helper_toolbars::get_instance();
        $toolbars->top->disable_item("simplecontent/topic/delete/{$this->_topic->guid}.html");
        $this->_request_data['title'] = $this->_request_data['l10n']->get('delete topic') .': '. htmlspecialchars($this->_topic->name);
        $this->_request_data['id'] = $this->_topic->guid;
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
                $_MIDCOM->relocate("simplecontent/topic/{$this->_topic->up}.html");
                // This will exit()
            } 
        }
        
        debug_pop();
        return true;
    }
    
    /**
     * Renders the selected topic using the datamnager view mode.
     * 
     * @access private
     */
    function _show_delete ($handler_id, &$data)
    {
        midcom_show_style('admin_deletecheck');
    }
    
    /**
     * Prepares everything to create a new topic. When processing the
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
        // Try to load the topic and to prepare its datamanager.
        if (   ! $this->_load_topic($args[0]) || $this->_prepare_local_toolbar(false) ) 
        {
            debug_pop();
            return false;
        }
        // Prepare the topic toolbar, the local toolbar stays empty at this point.
        // Disable all toolbar items while editing:
        $toolbar =& midcom_helper_toolbars::get_instance();
        
        $toolbar->top->disable_item("simplecontent/topic/create/{$this->_topic->id}/topic.html");
        $toolbar->top->disable_item("simplecontent/topic/edit/{$this->_topic->id}.html");
        $toolbar->top->disable_item("simplecontent/topic/move/{$this->_topic->id}.html");
        $toolbar->top->disable_item("simplecontent/topic/score/{$this->_topic->id}.html");
        $toolbar->top->disable_item("simplecontent/topic/delete/{$this->_topic->guid}.html");
        $toolbar->top->disable_item("simplecontent/topic/attachment/{$this->_topic->id}.html");
        // todo, this should probably be fixed some other way sometime in the future.
        if (0) foreach ($this->_schemadb_topic_index as $name => $desc) 
        { 
            //$toolbar->bottom->disable_item("create/{$this->_topic->id}/{$name}.html");
        }
        //$toolbar->disable_view_page();

               
        // If applicable, patch the schema database for the index topic creation.
        if (   array_key_exists('create_index', $_REQUEST) 
            && $_REQUEST['create_index'] == 1) 
        {
            debug_add('We are creating a new index topic, thus we modify the schema a bit and display an notification.');
            $this->_schemadb_topic[$schema]['fields']['name']['default'] = 'index';
            $this->_schemadb_topic[$schema]['fields']['name']['readonly'] = true;
            $GLOBALS['view_contentmgr']->msg .= $this->_l10n->get('no index topic') . "<br />\n";
        }
        
        // Initialize sessioning first        
        $session = new midcom_service_session();

        // Start up the Datamanager in the usual session driven create loop
        // (create mode if seesion is empty, otherwise regular edit mode)
        if (! $session->exists('admin_create_id'))
        {
            debug_add('We do not currently have a content object, entering creation mode.');
            
            $this->_topic = null;
            $this->_schemadb_topic[$schema]['fields']['sitegroup']['default'] = $this->_topic->sitegroup;
            $this->_schemadb_topic[$schema]['fields']['sitegroup']['hidden'] = true;
            $this->_schemadb_topic[$schema]['fields']['id']['hidden'] = true;
            $this->_schemadb_topic[$schema]['fields']['guid']['hidden'] = true;
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
            debug_add("We have found the topic id {$id} in the session, loading object and entering regular edit mode.");
            
            // Try to load the topic and to prepare its datamanager.
	        if (   ! $this->_load_topic($id)
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
                    $id = $this->_topic->id;
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
                if ($this->_topic->name == "" || $this->_missing_index)
                {
                    // Empty URL name or missing index topic, generate it
                    $this->_topic = $this->_generate_urlname($this->_topic);
                }
                $session->remove('admin_create_id');
                
                // Reindex the topic 
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);
                
                $_MIDCOM->relocate("simplecontent/topic/{$this->_topic->id}.html");
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
                    $_MIDCOM->relocate("simplecontent/topic/{$args[0]}.html");
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
                    if (! mgd_delete_extensions($this->_topic) || ! $this->_topic->delete())
                    {
                        $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                            'Failed to remove temporary topic or its dependants.');
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
     * Renders the selected topic using the datamnager view mode.
     * 
     * @access private
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('admin_create');
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
        //TODO : Ask torben what the point of this one is.
    	$GLOBALS['de_linkm_taviewer_schemadb_topics'] = array_merge
        (
            Array
            (
                '' => $this->_l10n->get('default setting')
            ),
            $this->_config->get('schemadbs')
        );
        
    }
    
    
    /**
     * Internal helper, creates a valid name for a given topic. It calls
     * generate_error on any failure.
     * 
     * @param midcom_baseclasses_database_topic $topic The article to process, if omitted, the currently selected article is used instead.
     * @return midcom_baseclasses_database_topic The updated topic.
     * @access private
     */
    function _generate_urlname($topic = null) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (!$topic) 
        {
            $topic = $this->_topic;
        }
        
        $updated = false;
        
        if (   $this->_missing_index 
            && ! $this->_config->get('autoindex')) 
        {
            // Note that this code-block probably executes very seldomly, as the missing
            // index is caught during topic creation. It could only happen if
            // you rename an index topic forcefully, so this check should stay
            // here.
            $topic->name = 'index';
            $updated = $topic->update();
        } 
        else 
        {
            $tries = 0;
            $maxtries = 99;
            while(    ! $updated 
                  && $tries < $maxtries) 
            {
                $topic->name = midcom_generate_urlname_from_string($topic->title);
                if ($tries > 0) 
                {
                    // Append an integer if topics with same name exist
                    $topic->name .= sprintf("-%03d", $tries);
                }
                $updated = $topic->update();
                $tries++;
            }
        }
        
        if (! $updated) 
        {
            debug_print_r('Failed to update the Article with a new URL, last topic state:', $topic);
            $_MIDCOM->generate_error('Could not update the topic\'s URL Name: ' . mgd_errstr());
            // This will exit()
        }
        
        debug_pop();
        return $topic;
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
        
        
        $this->_topic = new midcom_baseclasses_database_topic();
        
        if (0 &&    array_key_exists('create_index', $_REQUEST) 
            && $_REQUEST['create_index'] == 1) 
        {
            $this->_topic->name = 'index';
        }
        //$this->_topic->topic = $this->_topic->id;
        $this->_topic->author = $_MIDCOM->user->id;
        if (! $this->_topic->create()) 
        {
            debug_add('Could not create topic: ' . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_add('Could not create topic {$this->_topic->name} , {$this->_article->topic}: ' . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }
        
        if ( $this->_config->get('auto_approved') == true ) 
        {
            $meta =& midcom_helper_metadata::retrieve($this->_topic);
            $meta->approve();
        }
        
        $result['storage'] =& $this->_topic;
        debug_pop();
        return $result;
    }

    /**
     * Deletes the currently active topic and all of its extensions.
     * On success, it will return to the welcome page, on failure, it returns false. 
     * 
     * @return bool Indicating success
     * @access private
     */
    function _delete_record() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $up = $this->_topic->up;    
        $guid = $this->_topic->guid;
        
        if (! $this->_topic->delete()) 
        {
            debug_add("Could not delete topic $id ($guid) because: " . mgd_errstr());
            $this->errstr = "Could not delete Article {$this->_topic->id}: " . mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_pop();
            return false;
        }
        
        // Update the index
        $indexer =& $_MIDCOM->get_service('indexer');
        $indexer->delete($guid);
        
        // Invalidate the cache modules
        $_MIDCOM->cache->invalidate($guid);
        
        // Redirect to parent topic.
        if ($up > 0) {
            $_MIDCOM->relocate("simplecontent/topic/{$up}.html");
        } else {
            $_MIDCOM->relocate("simplecontent/");
        }
        // This will exit()
    }


    /**
     * Return the metadata of the current topic.
     */
    function get_metadata() 
    {
        if (is_null($this->_topic)) 
        {
            return false;
        }
        return array (
            MIDCOM_META_CREATOR => $this->_topic->creator,
            MIDCOM_META_EDITOR  => $this->_topic->revisor,
            MIDCOM_META_CREATED => $this->_topic->created,
            MIDCOM_META_EDITED  => $this->_topic->revised
        );
    }

    /**
     * Internal helper, called before the edit form is shown.
     * 
     * This is a rather bloody hack to modify the schema while the datamanager
     * is already up and running. It will make the url name field read-only 
     * if the current user is not a power user or admin and if we are looking
     * at the index topic.
     * 
     * @todo Move the API to use the new MidCOM ACL stuff (and add acl studd)
     * @access private
     */
    function _patch_active_schema() 
    {
        if (   $this->_topic->name == 'index' )
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
        if (   ! $this->_load_topic($args[0])) // removed DM start here -> not needed!
        {
            debug_add("Could not load topic with id {$args[0]}. Aborting.");
            debug_pop();
            return false;
        }
        
        /* use this to pass params between relocates etc. */
        $this->_session = new midcom_service_session();
        $this->_prepare_local_toolbar();

        /* f_copyto might not be set if the user pressed false */
        if (array_key_exists('f_moveto', $_POST) ) {
            $topic = new midcom_baseclasses_database_topic($_POST['f_moveto']);
            // you must have write priveledges to the topic you are moving to.
            
            if ($_MIDCOM->auth->can_do('midcom:create', $topic)) {
                $this->_topic->up = $_POST['f_moveto'];
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                if ($this->_topic->update()) {
                    $this->_session->set('msg', $this->_l10n->get('Topic moved sucessfully.'));
                    
                    debug_add("Article {$this->_topic->title}, id. {$this->_topic->title} moved to topic {$topic->name}, id: {$topic->id}", MIDCOM_LOG_ERROR);
                    $_MIDCOM->relocate($prefix . 'simplecontent/topic/'. $this->_topic->id);
                } else {
                    // todo : add errorlog.
                    $this->_session->set('msg', $this->_l10n->get('Topic not moved'));
                    debug_add("Topic {$this->_topic->title}, id. {$this->_topic->title} _NOT_ moved to topic {$topic->name}, id: {$topic->id}", MIDCOM_LOG_ERROR);
                }
            } else {
                /* copyto not set this means that the user pressed cancel. */
                $this->_session->set('msg', $this->_l10n->get('Access denied. You cannot move the topic there.') );
                debug_add('Move Access denied to topic' . $topic->id . " " . mgd_errstr(), MIDCOM_LOG_WARN);
                
                $this->_request_data['first_run'] = true;
            }
        } else {
            
            $this->_request_data['first_run'] = true;
        }
        $this->_request_data['topic_id'] = $this->_topic->id;
        $this->_request_data['topic_name'] = $this->_topic->name;
        
        
        debug_pop();
        return true;
    }
    
    function _show_move() 
    {
    
        if ($this->_request_data['first_run']) {
            midcom_show_style('topic-move');
        }
    
    }
    
    function _handler_index() 
    {
        
        return true;
    } 
   
    function _show_index() 
    {
       midcom_show_style('index');
    }
    
}
?>
