<?php

/**
 * @package net.nemein.calendar
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Calendar Admin interface class.
 * 
 * @package net.nemein.calendar
 */

class net_nemein_calendar_admin extends midcom_baseclasses_components_request_admin
{
    /**
     * The event to show, or null in case that there is no event set at this time.
     * The request data key 'event' is set to a reference to this member during 
     * class startup.
     * 
     * @var midcom_baseclasses_database_event
     * @access private
     */
    var $_event = null;

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
     * The datamanager instance controlling the event to show, or null in case there
     * is no event at this time. The request data key 'datamanager' is set to a 
     * reference to this member during class startup.
     * 
     * @var midcom_helper_datamanager
     * @access private
     */
    var $_datamanager = null;
    
    /**
     * The root event to use with this topic.
     * 
     * @var midcom_baseclasses_database_event
     * @access private
     */
    var $_root_event = null;
    
    /**
     * The master event ID to use as _root_event::up.
     * 
     * @var int
     * @access private
     */
    var $_master_event = null;    

    function net_nemein_calendar_admin($topic, $config) 
    {
        parent::midcom_baseclasses_components_request_admin($topic, $config);
    }

    /**
     * The initialization tries to load the root event and will create one if
     * it couldn't be found. It will also load the Datamanager schema database,
     * which is used here and there for the toolbars etc.
     * 
     * The root event will only be auto-created, if this is no request to the
     * component config. That way you can manually set an root event, if you need
     * one
     * 
     * @access private
     */
    function _on_initialize()
    {
        // Initialize the root event if this is not a request to the config screen
        // This is a bit of a hack as we access $argv directly (this should really
        // be in on_handle, but that's something for the 2.5 rewrite).
        if (   $GLOBALS['argc'] != 4
            || $GLOBALS['argv'][3] != 'config')
        {
            $this->_init_root_event();
        }
        
        // Load schema databases
        $this->_load_schema_database();
        
        // Populate the request data with references to the class members we might need
        $this->_request_data['event'] =& $this->_event;
        $this->_request_data['datamanager'] =& $this->_datamanager;
        
        // Set up the URL space
        
        // Welcome Page
        $this->_request_switch[] = Array
        (
            'handler' => 'welcome',
        );
        
        // Edit event
        $this->_request_switch[] = Array
        (
            'handler' => 'edit',
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
        );
        
        // View event
        $this->_request_switch[] = Array
        (
            'handler' => 'view',
            'fixed_args' => Array('view'),
            'variable_args' => 1,
        );        
        
       // Set repeat for event
        $this->_request_switch[] = Array
        (
            'handler' => 'repeat',
            'fixed_args' => Array('repeat'),
            'variable_args' => 1,
        );

        // Create event
        $this->_request_switch[] = Array
        (
            'handler' => 'create',
            'fixed_args' => Array('create'),
            'variable_args' => 1,
        );

        // Delete event
        $this->_request_switch[] = Array
        (
            'handler' => 'delete',
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );
        
        // Configuration
        $this->_request_switch[] = Array
        (
            'handler' => 'config_dm',
            'fixed_args' => Array('config'),
            'schemadb' => 'file:/net/nemein/calendar/config/schemadb_config.inc',
            'schema' => 'config',
            'disable_return_to_topic' => false
        );                             
    }

    /**
     * Load the root event from database or create it
     *
     * @access private
     */
    function _init_root_event() 
    {
        $master_event_guid = $this->_config->get('master_event');
        if (is_null($master_event_guid))
        {
            $this->_master_event = 0;
        }
        else
        {
            $master_event = new midcom_db_event($master_event_guid);
            if ($master_event)
            {
                $this->_master_event = $master_event->id;
            }
            else
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, 'Configured master event was not found');
                // This will exit
            }
        }
        
        $root_event_guid = $this->_config->get('root_event');
  
        if (!is_null($root_event_guid))
        {
            $this->_root_event = new midcom_db_event($root_event_guid);
            
            // Did we get the root event instanced?
            if (is_object($this->_root_event)) 
            {
                // Note: this means single request can contain only one n.n.calendar instance
                if (defined('__NNC_ROOTEVENT'))
                {
                    return false;
                }
                
                define('__NNC_ROOTEVENT', $this->_root_event->id);
        
                // Correct topic linkage if needed
                $rootevent_topic = $this->_root_event->parameter("net.nemein.calendar","topic");
                if ($rootevent_topic != $this->_topic->guid())
                {
                    // FIXME: This might cause issues with the symlinking stuff
                    $this->_root_event->parameter("net.nemein.calendar","topic",$this->_topic->guid());
                }
            } 
            else 
            {
                $this->_root_event = null;
            }
        }
    
        // Create the root event if needed
        if (!$this->_root_event)
        {
            $event = mgd_get_event();
            $event->owner = $this->_topic->owner;
            $event->title = $this->_topic->extra;
            $event->busy = 0;
            $event->extra = '';
            $event->description = '';
            $event->up = $this->_master_event;
            $event->start = 0;
            $event->end = 1;
            $event->type = 1;
            $stat = $event->create();
            if ($stat) 
            {
                $event = mgd_get_event($stat);
                $this->_topic->parameter("net.nemein.calendar","root_event",$event->guid());
                $event->parameter("net.nemein.calendar","topic",$this->_topic->guid());
                $this->_root_event = $event;
            }
            else
            {
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRFORBIDDEN, "Could not create Root Event: " . mgd_errstr());
                // This will exit
            }
        }
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
        $GLOBALS['net_nemein_calendar_schemadbs'] =& $this->_schemadbs;
        
        if (is_array($this->_schemadb))
        {
            if (count($this->_schemadb) == 0)
            {
                debug_add('The schema database was empty, we cannot use this.', MIDCOM_LOG_ERROR);
                debug_print_r('Evaluated data was:', $data);
                $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
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
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                'Could not load the schema database accociated with this topic. The schema DB was no array.');
            // This will exit.
        }
        debug_pop();
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
     * This function adds all of the standard items (configuration and create links)
     * to the topic toolbar.
     * 
     * @access private
     */
    function _prepare_topic_toolbar()
    {
        $this->_topic_toolbar->add_item(
            Array 
            (
                MIDCOM_TOOLBAR_URL => "config.html", 
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            ), 
            0
        );    
            
        foreach (array_reverse($this->_schemadb_index, true) as $name => $desc) 
        { 
            $text = sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($desc));
            $this->_topic_toolbar->add_item(
            	Array 
                (
	                MIDCOM_TOOLBAR_URL => "create/{$name}.html", 
	                MIDCOM_TOOLBAR_LABEL => $text,
	                MIDCOM_TOOLBAR_HELPTEXT => null,
	                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
	                MIDCOM_TOOLBAR_ENABLED => true,
                ), 
                0
            );
        }        
    }
    
    /**
     * Prepares the datamanager for the loaded event. When returning false, 
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

        if (! $this->_datamanager->init($this->_event)) 
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
     * Prepares the datamanager for creation of a new event. When returning false, 
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
        
        $midgard = mgd_get_midgard();
        
        error_reporting(E_WARNING);
        $event = mgd_get_event();
        $event->up = $this->_root_event->id;
      
        // Default events to not busy
        $event->busy = 0;
      
        $stat = $event->create();
        error_reporting(E_ALL);

        if (! $stat) 
        {
            debug_add('Could not create event: ' . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }
        
        $this->_event = mgd_get_event($stat);
        $result['storage'] =& $this->_event;
        debug_pop();
        return $result;
    }    
    
    /**
     * This internal helper loads the event identified by the passed argument from the database.
     * When returning false, it sets errstr and errcode accordingly, you jsut have to pass the result
     * to the handle callee.
     * 
     * In addition, it will set the currently active leaf to the set ID.
     * 
     * @param mixed $id A valid event identifier that can be used to load an event from the database. 
     *     This can either be an ID or a GUID.
     * @return bool Indicating success.
     * @access private 
     */
    function _load_event($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
    	debug_add("Trying to load the event with the ID {$id}.");
        
        $this->_event = mgd_get_event($id);
        //$this->_event = new MidgardEvent($id);
        if (! $this->_event)
        {
            $this->errstr = "Failed to load the event with the id {$id}: This usually means that the event was not found. (See the debug level log for more information.)";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        if ($this->_event->up != $this->_root_event->id)
        {
            $this->errstr = "Failed to load the event with the id {$id}: The event was not in the right tree.";
            $this->errcode = MIDCOM_ERRNOTFOUND;
            debug_pop();
            return false;
        }
        
        $this->_component_data['active_leaf'] = $id;
        
        debug_pop();
        return true;
    }
    

    /**
     * This internal helper adds the edit and delete links to the local toolbar.
     * 
     * @access private
     */
    function _prepare_local_toolbar()
    {
        $this->_local_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "edit/{$this->_event->id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        $this->_local_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => "repeat/{$this->_event->id}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('repeat'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/recurring.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );        
        $this->_local_toolbar->add_item(Array(
            MIDCOM_TOOLBAR_URL => "delete/{$this->_event->id}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('delete'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true,
        ));
    }    

    
    /**
     * Welcome page handler.
     * 
     * @access private
     */
    function _handler_welcome ($handler_id, $args, &$data)
    {    
        return true;
    }
    
    /**
     * Renders the welcome page.
     * 
     * @access private
     */
    function _show_welcome ($handler_id, &$data)
    {
        midcom_show_style('admin_welcome');
    }
    
    /**
     * Prepares everything to create a new event. When processing the
     * DM results, it will redirect to the view mode on the save event, and to the
     * welcome page otherwise. It uses sessioning to keep track of the newly created
     * event ID.
     *  
     * Preparation include the toolbar setup.
     * 
     * @access private
     */
    function _handler_create($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Read the schema name from the args
        $schema = $args[0];
                        
        // Initialize sessioning first        
        $session = new midcom_service_session();

        // Start up the Datamanager in the usual session driven create loop
        // (create mode if seesion is empty, otherwise regular edit mode)
        if (! $session->exists('admin_create_id'))
        {
            debug_add('We do not currently have a content object, entering creation mode.');
            
            $this->_event = null;
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
            debug_add("We have found the event id {$id} in the session, loading object and entering regular edit mode.");
            
            // Try to load the event and to prepare its datamanager.
	        if (   ! $this->_load_event($id)
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
                    $id = $this->_event->id;
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
                $session->remove('admin_create_id');
                
                // Reindex the event 
                $indexer =& $GLOBALS['midcom']->get_service('indexer');
                $indexer->index($this->_datamanager);
                
                $GLOBALS['midcom']->relocate("view/{$this->_event->id}.html");
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
                    $GLOBALS['midcom']->relocate('');
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
                    if (! mgd_delete_extensions($this->_event) || ! $this->_event->delete())
                    {
                        $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                            'Failed to remove temporary event or its dependants.');
                        // This will exit
                    }
                    $session->remove('admin_create_id');
                    $GLOBALS['midcom']->relocate('');
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
     * Renders the selected event using the datamnager view mode.
     * 
     * @access private
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('admin_create');
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
        if (   ! $this->_load_event($args[0])
            || ! $this->_prepare_datamanager())
        {
            debug_pop();
            return false;
        }
        
        $this->_prepare_local_toolbar();
                
        // Now launch the datamanger processing loop
        switch ($this->_datamanager->process_form()) 
        {
            case MIDCOM_DATAMGR_EDITING:
                break;

            case MIDCOM_DATAMGR_SAVED:                
                // Reindex the event
                $indexer =& $GLOBALS['midcom']->get_service('indexer');
                $indexer->index($this->_datamanager);
                
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("view/{$this->_event->id}.html");
                // This will exit()

            case MIDCOM_DATAMGR_CANCELLED:
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("view/{$this->_event->id}.html");
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
     * Locates the event to view and prepares everything for the view run.
     * This includes the toolbar preparations and the preparation of the
     * article and datamanager instances.
     * 
     * @access private
     */
    function _handler_view($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Try to load the event and to prepare its datamanager.
        if (   ! $this->_load_event($args[0])
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
     * Renders the selected article using the datamanager view mode.
     * 
     * @access private
     */
    function _show_view($handler_id, &$data)
    {
        midcom_show_style('admin_view');
    }    
    
    function _handler_repeat($handler_id, $args, &$data)
    {
        // TODO: Move to the n.n.repeathandler class
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (!$this->_load_event($args[0]))
        {
            debug_pop();
            return false;
        }        
        
        if ($this->_event->parameter('net.nemein.repeathandler', 'master_guid'))
        {
            // This is a repeating instance, redirect user to edit repetition rules
            // of master event instead
            $master = new midcom_db_event($this->_event->parameter('net.nemein.repeathandler', 'master_guid'));
            $GLOBALS['midcom']->relocate("repeat/{$master->id}.html");
        }
        
        // Load repetition information from DB and set defaults
        $repeat_rule = Array();
        $repeat_params = $this->_event->listparameters('net.nemein.repeathandler');
        if ($repeat_params)
        {
            while ($repeat_params->fetch())
            {
                if (substr($repeat_params->name, 0, 5) == 'rule.')
                {
                    if ($repeat_params->name == 'rule.days')
                    {
                        $repeat_rule = $this->_event->parameter('net.nemein.repeathandler', $repeat_params->name);
                        
                        // Check whether the weekday is set to be repeated or not
                        for ($i = 0; $i <= 6; $i++)
                        {
                            if (ereg($i, $repeat_rule))
                            {
                                $repeat_rule['days'][$i] = TRUE;
                            }
                            else
                            {
                                $repeat_rule['days'][$i] = FALSE;
                            }
                        }
// This gives completely wrong type of an array
//                        $repeat_rule['days'] = explode(',', $this->_event->parameter('net.nemein.repeathandler', $repeat_params->name));
                    }
                    else
                    {
                        $repeat_rule[substr($repeat_params->name, 5)] = $this->_event->parameter('net.nemein.repeathandler', $repeat_params->name);
                    }
                }
            }
        }     
                
        $this->_prepare_local_toolbar();
        
        // Store repeats
        if (   array_key_exists('net_nemein_calendar_Repeat_rule', $_POST)
            && array_key_exists('net_nemein_calendar_Repeat_useend', $_POST))
        {
            
            foreach ($_POST['net_nemein_calendar_Repeat_rule'] as $field => $value)
            {
                if ($field == 'from')
                {
                    $repeat_rule[$field] = @strtotime($value);
                }
                elseif ($field == 'to')
                {
                    if ($_POST['net_nemein_calendar_Repeat_useend'] != 'to')
                    {
                        // This event isn't "repeating until date", skip field
                        $repeat_rule[$field] = null;                        
                        continue;
                    }
                    $repeat_rule[$field] = @strtotime($value);
                }
                elseif ($field == 'num')
                {
                    if ($_POST['net_nemein_calendar_Repeat_useend'] != 'num')
                    {
                        // This event isn't "repeating N times", skip field
                        $repeat_rule[$field] = null;
                        continue;
                    }
                    $repeat_rule[$field] = $value; 
                }
                elseif ($field == 'days')
                {
                    if ($_POST['net_nemein_calendar_Repeat_rule']['type'] != 'weekly_by_day')
                    {
                        // This event isn't repeating "weekly by day", skip days
                        continue;
                    }
                    $repeat_rule[$field] = $value;
                }
                else
                {
                    $repeat_rule[$field] = $value;                    
                }
            }
            debug_add("Repeat_rule is ".serialize($repeat_rule));
            
            // Store the rules to DB
            foreach ($repeat_rule as $key => $value)
            {
                if (is_array($value))
                {
                    $selected_fields = array();
                    foreach ($value as $field => $selected)
                    {
                        $selected_fields[] = $field;
                    }
                    $this->_event->parameter('net.nemein.repeathandler', "rule.{$key}", implode(',', $selected_fields));
                }
                else
                {
                    $this->_event->parameter('net.nemein.repeathandler', "rule.{$key}", $value);
                }
            }

            $stat = false;
            $this->_event->repeat_rule = $repeat_rule;
            $this->_event->update();
            
            $repeat_calculator = new net_nemein_repeathandler_calculator(&$this->_event, $repeat_rule);
            $instances = $repeat_calculator->calculate_instances();
            
            $repeat_handler = new net_nemein_repeathandler(&$this->_event);
            $repeat_handler->delete_stored_repeats($this->_event->guid());
            
            foreach ($instances as $date => $instance)
            {
                if (array_key_exists('guid', $instance))
                {
                    $previous_guid = $instance['guid'];
                }
                else
                {
                    // These are the instances we must create
                    $previous_guid = $repeat_handler->create_event_from_instance($instance, $previous_guid);
                    if ($previous_guid)
                    {
                        $instance['guid'] = $previous_guid;
                        $instances[$date] = $instance;
                    }
                }
            }
            
            $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());
            
            if ($stat)
            {
                $GLOBALS['midcom']->cache->invalidate($this->_topic->guid());
                $GLOBALS['midcom']->relocate('');
                // This will exit
            }
        }
        
        $this->_request_data['repeat_rule'] = &$repeat_rule;
        
        debug_pop();
        return true;
    }
    
    function _show_repeat($handler_id, &$data)
    {
        midcom_show_style('admin_repeat');
    }
  
    function _handler_delete($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        // Try to load the article and to prepare its datamanager.
        if (   ! $this->_load_event($args[0])
            || ! $this->_prepare_datamanager())
        {
            debug_pop();
            return false;
        }  
        if (array_key_exists('net_nemein_calendar_deleteok', $_REQUEST)) 
        {
            return $this->_delete_record();
            // This will redirect to the welcome page on success or
            // returns false on failure setting the corresponding error members.
        } 
        else 
        {
            if (array_key_exists('net_nemein_calendar_deletecancel', $_REQUEST)) 
            {
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("view/{$this->_event->id}.html");
                // This will exit()
            } 
        }  
        
        debug_pop();
        return true;        
    }
    
    /**
     * Renders the selected article using the datamanager view mode.
     * 
     * @access private
     */
    function _show_delete($handler_id, &$data)
    {
        midcom_show_style('admin_deletecheck');
    }    

    function _delete_record()
    {
    
        // Backup the GUID first, this is required to update the index later.
        $guid = $this->_event->guid();
        
        // Clean up repeats
        $repeat_handler = new net_nemein_repeathandler(&$this->_event);
        $repeat_handler->prepare_deletion($this->_event->id);        
        
        // Remove dependants
        $stat = midcom_helper_purge_object($guid);
        
        if (!$stat)
        {
            $this->errstr = "Could not delete event $this->_event->id: ".mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_add($this->errstr, MIDCOM_LOG_ERROR);
            return false;
        }
        
        // Update the index
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        $indexer->delete($guid);
            
        // Invalidate the cache modules
        $GLOBALS['midcom']->cache->invalidate($guid);
        
        // Redirect to welcome page.
        $GLOBALS['midcom']->relocate($GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
        // This will exit()
    }
  
  /*
      // REPEAT FORM: SET REPEAT
    if (array_key_exists ($this->form_prefix . "setrepeat", $_REQUEST)) {
      debug_add ("Setting repeat rules");
      $this->_repeat_to_storage();
      // Set repeat
      error_reporting(E_WARNING);
      $stat = $this->_event->update_repeat("all");
      error_reporting(E_ALL);

      $GLOBALS['midcom']->cache->invalidate($this->_event->guid());
      debug_add("Invalidated Midcom Cache.");

      debug_pop();
      $this->_view = "view";     
      debug_add ("Repeat handler returned $stat");
      return $stat;
    } 
    */

}
?>
