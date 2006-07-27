<?php
/**
 * @package de.linkm.taviewer
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAViewer AIS interface class
 * 
 * This is a complete rewrite of the topic-article viewer the has been made for MidCOM 2.6.
 * It incorporates all of the goodies current MidCOM has to offer and can serve as an 
 * example component therefore.
 * 
 * @package de.linkm.taviewer
 */

class de_linkm_taviewer_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     * 
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;
    
    /**
     * The schema database accociated with the topic.
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
    var $_schemadb_index = Array();
    
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
     * Flag indicating wether an index article is present.
     * 
     * @var bool
     * @access private
     */
    var $_missing_index = false;
    
    function de_linkm_taviewer_admin($topic, $config) 
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }
    
    /**
     * Set the content topic to use. This will check against the configuration setting 'symlink_topic'.
     * 
     * @access protected
     */
    function _determine_content_topic() 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $guid = $this->_config->get('symlink_topic');
        if (is_null($guid)) 
        {
            // No symlink topic
            // Workaround, we should talk to an DBA object automatically here in fact. 
            $this->_content_topic = new midcom_db_topic($this->_topic->id);
            debug_pop();
            return;
        }
        
        $this->_content_topic = new midcom_db_topic($guid);

        // Validate topic.
                
        if (! $this->_content_topic) 
        {
            debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: ' 
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            $_MIDCOM->generate_error('Failed to open symlink content topic.');
            // This will exit.
        }
        
        if ($this->_content_topic->get_parameter('midcom', 'component') != 'de.linkm.taviewer')
        {
            debug_print_r('Retrieved topic was:', $this->_content_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Symlink content topic is invalid, see the debug level log for details.');
            // This will exit.
        }
        
        debug_pop();
    }

    /**
     * The initialization determines the real content topic (in case we have been
     * symlinked) and sets up the request switch. It will also load the Datamanger
     * schema database, which is used here and there for the toolbars etc.
     * 
     * @access private
     */
    function _on_initialize()
    {
        // Load the content topic and the schema DB
        $this->_determine_content_topic();
        $this->_load_schema_database();
        
        // Populate the request data with references to the class members we might need
        $this->_request_data['article'] =& $this->_article;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        
        // Configure request switch
        // Welcome Page
        $this->_request_switch[] = Array
        (
            'handler' => 'welcome',
        );
        
        // View article
        $this->_request_switch[] = Array
        (
            'handler' => 'view',
            'fixed_args' => Array('view'),
            'variable_args' => 1,
        );
        
        // Edit article
        $this->_request_switch[] = Array
        (
            'handler' => 'edit',
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
        );
        
        // Delete article
        $this->_request_switch[] = Array
        (
            'handler' => 'delete',
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );
        
        // Create article
        $this->_request_switch[] = Array
        (
            'handler' => 'create',
            'fixed_args' => Array('create'),
            'variable_args' => 1,
        );
        
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'fixed_args' => Array('config'),
            'schemadb' => 'file:/de/linkm/taviewer/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );
    }
    
    /**
     * Internal helper, loads the configured schema database into the class.
     * It is not yet evaluated by a datamanager, only the file is loaded.
     * 
     * @see $_schemadb
     * @see $_schemadb_index
     * @access private
     */
    function _load_schema_database()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $path = $this->_config->get('schemadb');
        $data = midcom_get_snippet_content($path);
        eval("\$this->_schemadb = Array ({$data}\n);");
        
        // This is a compatibility value for the configuration system
        $GLOBALS['de_linkm_taviewer_schemadbs'] =& $this->_schemadbs;
        
        if (is_array($this->_schemadb))
        {
            if (count($this->_schemadb) == 0)
            {
                debug_add('The schema database was empty, we cannot use this.', MIDCOM_LOG_ERROR);
                debug_print_r('Evaluated data was:', $data);
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Could not load the schema database accociated with this topic: The schema DB was empty.');
                // This will exit.
            }
            foreach ($this->_schemadb as $schema)
            {
                $this->_schemadb_index[$schema['name']] = $schema['description'];
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
     * This function adds all of the standard items (configuration and create links)
     * to the topic toolbar.
     * 
     * @access private
     */
    function _prepare_topic_toolbar()
    {
        $this->_topic_toolbar->add_item(Array(
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
        
        $hide_create = ($_MIDCOM->auth->can_do('midgard:create', $this->_topic) == false);
        foreach (array_reverse($this->_schemadb_index, true) as $name => $desc) 
        { 
            $text = sprintf($this->_l10n_midcom->get('create %s'), $desc);
            $this->_topic_toolbar->add_item(
            	Array 
                (
	                MIDCOM_TOOLBAR_URL => "create/{$name}.html", 
	                MIDCOM_TOOLBAR_LABEL => $text,
	                MIDCOM_TOOLBAR_HELPTEXT => null,
	                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
	                MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_HIDDEN => $hide_create
                ), 0);
        }
    }
   
    /**
     * Welcome page handler.
     * 
     * It shows a list of avaialbe schemas with create links unless there is no 
     * index article and autoindex mode is disabled, in which case it redirects
     * into the create mode.
     * 
     * @access private
     */
    function _handler_welcome ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (! $this->_config->get('autoindex')) 
        {
            $this->_check_for_missing_index();
            if ($this->_missing_index)
            {
                $schemas = array_keys($this->_schemadb);
                $_MIDCOM->relocate("create/{$schemas[0]}.html?create_index=1");
                // This will exit.
            }
        }
        
        debug_pop();
        return true;
    }
    
    /**
     * Renders the welcome page.
     * 
     * @access private
     */
    function _show_welcome ($handler_id, &$data)
    {
        $data['schemadb_index'] = $this->_schemadb_index;
        midcom_show_style('admin_welcome');
    }
    
    /**
     * This internal helper loads the article identified by the passed argument from the database.
     * When returning false, it sets errstr and errcode accordingly, you jsut have to pass the result
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
        if ($this->_article->topic != $this->_content_topic->id)
        {
            $this->errstr = "Failed to load the article with the id {$id}: The article was not in the right tree.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        
        $this->_component_data['active_leaf'] = $id;
        
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
    function _prepare_datamanager()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_datamanager = new midcom_helper_datamanager($this->_schemadb);
          
        if (! $this->_datamanager)
        {
            $this->errstr = 'Could not create the datamanager instance, see the debug level logfile for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }

        if (! $this->_datamanager->init($this->_article)) 
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
     * Prepares the datamanager for creation of a new article. When returning false, 
     * it sets errstr and errcode accordingly.
     * 
     * @param string $schema The name of the schema to initialize for
     * @return bool Indicating success
     * @access private
     */
    function _prepare_creation_datamanager($schema)
    {
        $this->_datamanager = new midcom_helper_datamanager($this->_schemadb);
        if (! $this->_datamanager)
        {
            $this->errstr = 'Failed to create a datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT; 
            return false;
        }

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
        
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "edit/{$this->_article->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            MIDCOM_TOOLBAR_ENABLED => true,
			MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:update', $this->_article) == false)
        ));
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "delete/{$this->_article->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true,
			MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:delete', $this->_article) == false)
        ));
        
    }
    
    /**
     * Locates the article to view and prepares everything for the view run.
     * This includes the toolbar preparations and the preparation of the
     * article and datamanager instances.
     * 
     * @access private
     */
    function _handler_view ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Try to load the article and to prepare its datamanager.
        if (   ! $this->_load_article($args[0])
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
     * Renders the selected article using the datamnager view mode.
     * 
     * @access private
     */
    function _show_view ($handler_id, &$data)
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
            || ! $this->_prepare_datamanager())
        {
            debug_pop();
            return false;
        }
        
        $_MIDCOM->auth->require_do('midgard:update', $this->_article);
        
        $this->_prepare_local_toolbar();
        
        // Disable all toolbar items while editing:
        $this->_topic_toolbar->disable_item('config.html');
        foreach ($this->_schemadb_index as $name => $desc) 
        { 
            $this->_topic_toolbar->disable_item("create/{$name}.html");
        }
        $this->_local_toolbar->disable_item("edit/{$this->_article->id}.html");
        $this->_local_toolbar->disable_item("delete/{$this->_article->id}.html");
        $this->_local_toolbar->disable_view_page();

        // Patch the active schema, see there for details.
        $this->_patch_active_schema();
        
        // Check for missing index articles
        $this->_check_for_missing_index();
        
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
                    $this->_datamanager->init($this->_article);
                } 
                
                // Reindex the article 
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);
                
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("view/{$this->_article->id}.html");
                // This will exit()

            case MIDCOM_DATAMGR_CANCELLED:
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("view/{$this->_article->id}.html");
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
        if (   ! $this->_load_article($args[0])
            || ! $this->_prepare_datamanager())
        {
            debug_pop();
            return false;
        }
        
        $_MIDCOM->auth->require_do('midgard:delete', $this->_article);

        // Prepare the toolbars        
        $this->_prepare_local_toolbar();
        $this->_local_toolbar->disable_item("delete/{$this->_article->id}.html");
        
        if (array_key_exists('de_linkm_taviewer_deleteok', $_REQUEST)) 
        {
            return $this->_delete_record();
            // This will redirect to the welcome page on success or
            // returns false on failure setting the corresponding error members.
        } 
        else 
        {
            if (array_key_exists('de_linkm_taviewer_deletecancel', $_REQUEST)) 
            {
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("view/{$this->_article->id}.html");
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
        
        $_MIDCOM->auth->require_do('midgard:create', $this->_topic);
        
        // Prepare the topic toolbar, the local toolbar stays empty at this point.
        // Disable all toolbar items while editing:
        $this->_topic_toolbar->disable_item('config.html');
        foreach ($this->_schemadb_index as $name => $desc) 
        { 
            $this->_topic_toolbar->disable_item("create/{$name}.html");
        }
        $this->_local_toolbar->disable_view_page();

        // Read the schema name from the args
        $schema = $args[0];
                
        // If applicable, patch the schema database for the index article creation.
        if (   array_key_exists('create_index', $_REQUEST) 
            && $_REQUEST['create_index'] == 1) 
        {
            debug_add('We are creating a new index article, thus we modify the schema a bit and display an notification.');
            $this->_schemadb[$schema]['fields']['name']['default'] = 'index';
            $this->_schemadb[$schema]['fields']['name']['readonly'] = true;
            $GLOBALS['view_contentmgr']->msg .= $this->_l10n->get('no index article') . "<br />\n";
        }
        
        // Check for a missing index.
		$this->_check_for_missing_index();
        
        // Initialize sessioning first        
        $session = new midcom_service_session();

        // Start up the Datamanager in the usual session driven create loop
        // (create mode if seesion is empty, otherwise regular edit mode)
        if (! $session->exists('admin_create_id'))
        {
            debug_add('We do not currently have a content object, entering creation mode.');
            
            $this->_article = null;
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
                if (   $this->_article->name == "" 
                    || $this->_missing_index)
                {
                    // Empty URL name or missing index article, generate it
                    $this->_article = $this->_generate_urlname($this->_article);
                }
                $session->remove('admin_create_id');
                
                // Reindex the article 
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);
                
                $_MIDCOM->relocate("view/{$this->_article->id}.html");
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
        
        $midgard = $_MIDCOM->get_midgard();
        $this->_article = new midcom_baseclasses_database_article();
        if (   array_key_exists('create_index', $_REQUEST) 
            && $_REQUEST['create_index'] == 1) 
        {
            $this->_article->name = 'index';
        }
        $this->_article->topic = $this->_content_topic->id;
        $this->_article->author = $midgard->user;
        if (! $this->_article->create()) 
        {
            debug_add('Could not create article: ' . mgd_errstr(), MIDCOM_LOG_WARN);
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
     * @todo Move the API to use the new MidCOM ACL stuff
     * @access private
     */
    function _patch_active_schema() 
    {
        if (   $this->_article->name == 'index'
            && $this->_config->get('autoindex') == false) 
        {
            $midgard = $_MIDCOM->get_midgard();
            $poweruser = false;
            if ($midgard->admin) 
            {
                $poweruser = true;
            } 
            else 
            {
                $person = mgd_get_person($midgard->user);
                if (   $person != false 
                    && $person->parameter('Interface', 'Power_User') != 'NO')
                {
                    $poweruser = true;
                }
            }
            if (! $poweruser) 
            {
                // This is unclean, but we have to live with it.
                $this->_datamanager->_fields["name"]["readonly"] = true;
            }
            
        }
    }
    
    /**
     * This function checks wether the index article is missing. It initializes
     * the $_missing_index member variable.
     */
    function _check_for_missing_index()
    {
    	$qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', 'index');
        $result = $qb->execute();
        $this->_missing_index = (count($result) == 0);
    }
}

function de_linkm_taviewer_symlink_topic_list() {
    $midgard = mgd_get_midgard();
    $sg = $midgard->sitegroup;
    
    $param = Array (
        "result" => Array("" => $_MIDCOM->i18n->get_string('symlink_topic disabled')),
        "stack" => Array(),
        "last_level" => 0,
        "last_topic" => null,
        "glue" => " > ",
        "sg" => $sg,
    );
    
    debug_push("de.linkm.taviewer symlink_topic");
    debug_add("Start building symlink_topic list", $param);
    // debug_print_r("Initial parameters:", $param);
    mgd_walk_topic_tree("de_linkm_taviewer_symlink_topic_list_loop", 0, 99, &$param, true, "name");
    debug_pop();
    
    return $param["result"];
}

function de_linkm_taviewer_symlink_topic_list_loop ($topicid, $level, &$param) {
    if ($topicid == 0) {
        // debug_add("Topic ID is 0, skipping this one, it is the lists root.");
        return;
    }
    $topic = mgd_get_topic($topicid);
    if ($param["sg"] != 0 && $topic->sitegroup != $param["sg"]) {
        debug_add("Skipping topic $topicid, it is in SG {$topic->sitegroup}, which is wrong.");
        return;
    }
    
    debug_add("Processing Topic $topicid in level $level");
    if ($level > $param["last_level"]) {
        if ($param["last_level"] != 0)
            array_push($param["stack"], $param["last_topic"]);
        $param["last_level"] = $level;
    } else if ($level < $param["last_level"]) {
        for ($i = $param["last_level"]; $i > $level; $i--)
            array_pop($param["stack"]);
        $param["last_level"] = $level;
    }
    
    $guid = $topic->guid();
    if ($topic->extra != "")
        $topicname = substr($topic->extra, 0, 30);
    else 
        $topicname = substr($topic->name, 0, 30);
    $param["last_topic"] = $topicname;
    
    if ($topic->parameter("midcom", "component") != "de.linkm.taviewer") {
        debug_add("Skipping topic $topicid, wrong component");
        return;
    }
    
    if ($level > 1)
        $param["result"][$guid] = implode($param["glue"], $param["stack"]) . $param["glue"] . $topicname;
    else
        $param["result"][$guid] = $topicname;
    // debug_print_r("Processing complete, parameters are updated to:", $param);
}

?>
