<?php

/**
 * Created on Aug 3, 2005
 *
 * Create, edit and delete objects
 * urls:
 * objectbrowser/$id = view object $id
 * objectbrowser/edit/$id edit object $id
 * /objectbrowser/create/$object/$schema create new object in topiv $object
 * /objectbrowser/delete/$id delete object with id.
 * 
 * $this->_object is set from the current object or from argv[0] in the case of create. 
 * @package no.bergfald.objectbrowser
 */

class no_bergfald_objectbrowser_object extends midcom_baseclasses_components_handler
{
    /**
     * The schema database accociated with the object, defaults to the one in the config dir.
     * 
     * @var Array
     * @access private
     */
    var $_schemadb_object = Array ();
    /**
     * Index of the schemas we got available. 
     */
    var $_schemadb_object_index = Array ();
    /**
     * Pointer to the schemahandler.
     * The schemahandler handles all operaitons wrt identifying an object and deciding what the 
     * object may do.
     * @var object no_bergfald_objectbrowser_schemahandler
     * @access private
     */
    var $_schema = null;

    /**
     * The object to show, or null in case that there is no object set at this time.
     * The request data key 'object' is set to a reference to this member during 
     * class startup.
     * 
     * @var midcom/midgard_object
     * @access private
     */
    var $_object = null;

    /**
     * Object used during object creation.
     * @var midgard object
     * @access private
     * */
    var $_object_create = null;
    /**
     * Type of the object to be created.
     */
    var $_type_create = '';

    /**
     * objecttype
     * @var string classname of current object
     * @access private
     */
    var $_type = '';
    /**
     * This variable tells if we are dealing with a root , i.e. an objecttype and
     * not a concrete object.
     * @access private
     * @var boolean private
     * 
     */
    var $_is_root = false;
    /**
     * The datamanager instance controlling the object to show, or null in case there
     * is no object at this time. The request data key 'datamanager' is set to a 
     * reference to this member during class startup.
     * 
     * @var midcom_helper_datamanager
     * @access private
     */
    var $_datamanager = null;

    var $_config = array ();

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

    function no_bergfald_objectbrowser()
    {
        parent :: midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        // Populate the request data with references to the class members we might need

        $this->_request_data['datamanager'] = & $this->_datamanager;

        /* used by styles to know what to show */
        $this->_request_data['toolbars'] = & midcom_helper_toolbars :: get_instance();
        $this->_schema = & no_bergfald_objectbrowser_schema::get_instance();

    }

    /**
     * This internal helper loads the object identified by the passed argument from the database.
     * When returning false, it sets errstr and errcode accordingly, you just have to pass the result
     * to the handle calle.
     * 
     * In addition, it will set the currently active leaf/node to the ID.
     * 
     * @param mixed $id A valid object identifier that can be used to load an object from the database. 
     *                  This can either be an ID or a GUID.
     * @return bool Indicating success.
     * @access private 
     */
    function _load_object($guid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Trying to load object with the ID {$guid}.");

        //$this->_object = new midcom_baseclasses_database_object($id);

        $this->_object = mgd_get_new_object_by_guid($guid);
        $this->_type = get_class($this->_object);
        $this->_request_data['object_type'] = $this->_type;
        if (!$this->_object)
        {
            $this->errstr = "Failed to load the object with the id {$guid}: This usually means that the object was not found. (See the debug level log for more information.)";
            $this->errcode = MIDCOM_ERRNOTFOUND;

            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to load the object with the id {$guid}: This usually means that the object was not found. (See the debug level log for more information.)");
            debug_pop();
            return false;
        }
        $this->_schema->set_object(& $this->_object);
        debug_pop();
        return true;
    }

    /**
     * Prepares the datamanager for the loaded object. When returning false, 
     * it sets errstr and errcode accordingly.
     * 
     * @return bool Indicating success
     * @access private
     * @param bool view to signal to datamanager that it will show the edit form. 
     */
    function _prepare_datamanager($view = true)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $schema = $this->_object->parameter('midcom.helper.datamanager', 'layout');
        // if the object has a schema defined use that, else, generate the schema.
        if ($schema == '' || !array_key_exists($schema, $this->_schemadb_object))
        {
            $schema = $this->_type;
            $this->_schemadb_object[$schema] = $this->_schema->get_schema($schema);
        }

        $this->_datamanager = new midcom_helper_datamanager($this->_schemadb_object);
        if (!$this->_datamanager)   
        {
            $this->errstr = 'Could not create the datamanager instance, see the debug level logfile for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            debug_pop();
            return false;
        }
        $this->_datamanager->set_show_javascript($view);
        if (!$this->_datamanager->init($this->_object, $schema))
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
     * Prepares the datamanager for creation of a new object. When returning false, 
     * it sets errstr and errcode accordingly.
     * 
     * @param string $schema The name of the schema to initialize for
     * @return bool Indicating success
     * @access private
     */
    function _prepare_creation_datamanager($schema)
    {
        //debug_print_r("Schemas: " , $this->_schemadb_object);
        debug_add("Using schema : ".$schema);
        
        $this->_datamanager = new midcom_helper_datamanager($this->_schemadb_object);
        if (!$this->_datamanager)
        {
            $this->errstr = 'Failed to create a datamanager instance, see the debug level log for details.';
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        $this->_datamanager->set_show_javascript(true);
        if (!$this->_datamanager->init_creation_mode($schema, $this))
        {
            $this->errstr = "Failed to initialize the datamanger in creation mode for schema '{$schema}'.";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        return true;
    }

    function _prepare_root_toolbar()
    {
        $request_data = & $_MIDCOM->get_custom_context_data('request_data');
        $toolbar =& midcom_helper_toolbars :: get_instance();
        $toolbar->top->add_item(Array (
                    MIDCOM_TOOLBAR_URL => "objectbrowser/create/0/{$this->_type}/{$this->_type}/node.html", 
                    MIDCOM_TOOLBAR_LABEL => sprintf($request_data["l10n_midcom"]->get('create %s'), $this->_type), 
                    MIDCOM_TOOLBAR_HELPTEXT => sprintf($request_data["l10n_midcom"]->get("Create an object of type %s"), $this->_type), 
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png', 
                    MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $this->_type) == false)
        ));
        
        $this->_request_data['aegir_interface']->set_current_node($this->_type);
        $this->_schema->set_current_type( $this->_type);
        //
    }
    /**
     * Prepare the toolbar and generate the locationbar.
     * @param boolean do not make the toolbar (used by handler_create)
     */
    function prepare_object_toolbar($prepare_toolbar = true)
    {
        $this->_request_data['aegir_interface']->set_current_node($this->_object->guid);
        $this->_request_data['aegir_interface']->generate_location_bar();
        if ($prepare_toolbar) 
        {
            $this->_request_data['aegir_interface']->prepare_toolbar();
        }
    }

    /**
     * Locates the object to view and prepares everything for the view run.
     * This includes the toolbar preparations and the preparation of the
     * object and datamanager instances.
     * 
     * @access private
     */
    function _handler_view($handler_id, $args, & $data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if ($this->_schema->objecttype_exists($args[0]))
        {
            $this->_type = $args[0];
            $this->_prepare_root_toolbar();
            $this->_is_root = true;
            $label = sprintf($this->_l10n->get('%s'), $this->_schema->get_type_name($args[0]));
            $this->_request_data['toolbars']->aegir_location->add_item(
                            array (
                                MIDCOM_TOOLBAR_URL => 'objectbrowser/'.$args[0], 
                                MIDCOM_TOOLBAR_LABEL => $label, 
                                MIDCOM_TOOLBAR_HELPTEXT => '', 
                                MIDCOM_TOOLBAR_ICON => null, 
                                MIDCOM_TOOLBAR_ENABLED => true, 
                                MIDCOM_TOOLBAR_HIDDEN => false
                                )
                                );
            
            return true;
        }

        // Try to load the object and to prepare its datamanager.
        if (!$this->_load_object($args[0]) || !$this->_prepare_datamanager())
        {
            debug_pop();
            return false;
        }

        $this->prepare_object_toolbar();
        debug_pop();
        return true;
    }

    /**
     * Renders the selected object using the datamnager view mode.
     * 
     * @access private
     */
    function _show_view($handler_id, & $data)
    {
        if ($this->_is_root)
        {
            midcom_show_style("root_view");
            return;
        }

        midcom_show_style('admin_view');
    }

    /**
     * Locates the object to edit and sets everything up. When processing the
     * DM results, it will redirect to the view mode on both the save and cancel
     * events.
     *  
     * Preparation include the toolbar setup.
     * 
     * @access private
     */
    function _handler_edit($handler_id, $args, & $data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Try to load the object and to prepare its datamanager.

        if (!$this->_load_object($args[0]) || !$this->_prepare_datamanager())
        {
            debug_pop();
            return false;
        }

        $this->prepare_object_toolbar();
        // Disable all toolbar items while editing:

        $this->_request_data['toolbars']->top->disable_item("objectbrowser/edit/{$this->_object->guid}.html");
        $this->_request_data['toolbars']->top->disable_item("objectbrowser/delete/{$this->_object->guid}.html");
        // Patch the active schema, see there for details.
        $this->_patch_active_schema();
        // Now launch the datamanger processing loop
        switch ($this->_datamanager->process_form())
        {
            case MIDCOM_DATAMGR_EDITING :
                break;

            case MIDCOM_DATAMGR_SAVED :
                if ($this->_object->name == '' || $this->_missing_index)
                {
                    // Empty URL name or missing index object, generate it and 
                    // refresh the DM, so that we can index it.
                    $this->_object = $this->_generate_urlname($this->_object);
                    $this->_datamanager->init($this->_object, 'object');
                }

                // Reindex the object 
                $indexer = & $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);

                // Redirect to view page.
                $GLOBALS['midcom']->relocate("objectbrowser/{$this->_object->guid}.html");
                // This will exit()

            case MIDCOM_DATAMGR_CANCELLED :
                // Redirect to view page.
                $GLOBALS['midcom']->relocate("objectbrowser/{$this->_object->guid}.html");
                // This will exit()

            case MIDCOM_DATAMGR_FAILED :
                $this->errstr = "The Datamanager failed critically while processing the form, see the debug level log for more details.";
                $this->errcode = MIDCOM_ERRCRIT;
                return false;
        }

        debug_pop();
        return true;
    }

    /**
     * Renders the selected object using the datamnager view mode.
     * 
     * @access private
     */
    function _show_edit($handler_id, & $data)
    {
        midcom_show_style('admin_edit');
    }

    /**
     * Locates the object to delete and prepares everything for the view run,
     * there the user has to confirm the deletion. This includes the toolbar 
     * preparations and the preparation of the
     * object and datamanager instances.
     * 
     * @access private
     */
    function _handler_delete($handler_id, $args, & $data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Try to load the object and to prepare its datamanager.
        if (!$this->_load_object($args[0]) || !$this->_prepare_datamanager(false))
        {
            debug_pop();
            return false;
        }

        // Prepare the toolbars
        $this->prepare_object_toolbar();
        $toolbars = & midcom_helper_toolbars :: get_instance();
        $toolbars->top->disable_item("objectbrowser/delete/{$this->_object->guid}.html");

        if (array_key_exists('admin_content_aegir_deleteok', $_REQUEST))
        {
            return $this->_delete_record();
            // This will redirect to the welcome page on success or
            // returns false on failure setting the corresponding error members.
        }
        else
        {
            if (array_key_exists('admin_content_aegir_deletecancel', $_REQUEST))
            {
                // Redirect to view page.
                $_MIDCOM->relocate("objectbrowser/{$this->_object->guid}.html");
                // This will exit()
            }
        }

        debug_pop();
        return true;
    }

    /**
     * Renders the selected object using the datamnager view mode.
     * 
     * @access private
     */
    function _show_delete($handler_id, & $data)
    {
        midcom_show_style('admin_deletecheck');
    }

    /**
     * Prepares everything to create a new object. When processing the
     * DM results, it will redirect to the view mode on the save event, and to the
     * welcome page otherwise. It uses sessioning to keep track of the newly created
     * acrticle ID.
     *  
     * Preparation includes the toolbar setup.
     * 
     * @access private
     */
    function _handler_create($handler_id, $args, & $data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Read the schema name from the args
        $schema_name = $args[1];
        $object_type = $args[2];
        $is_leaf = ($args[3] == 'leaf') ? true : false;
        
        if ($args[0] === '0')
        {
            // Big note: roots are always nodes!
            $this->_is_root = true;
            $this->_request_data['object_type'] = $args[2]; // todo : use config name!
            
        }
        else
        {
            // Try to load the object and to prepare its datamanager.
            if (!$this->_load_object($args[0]))
            {
                debug_pop();
                return false;
            }
            // dm prepareed, we can set up the toolbars as well.
            
        }
        $this->prepare_object_toolbar(false);
        
        // Prepare the object toolbar, the local toolbar stays empty at this point.
        // Disable all toolbar items while editing:
        $this->_request_data['toolbars']->aegir_location->add_item(array (
            MIDCOM_TOOLBAR_URL => null, 
            MIDCOM_TOOLBAR_LABEL => "Create ".$schema_name, 
            MIDCOM_TOOLBAR_HELPTEXT => '', 
            MIDCOM_TOOLBAR_ICON => null, 
            MIDCOM_TOOLBAR_ENABLED => false, 
            MIDCOM_TOOLBAR_HIDDEN => false)
            );
        
        $this->_type_create = $this->_schema->get_storage($schema_name);
        
        if (!$this->_type_create)
        {
            if (array_key_exists($schema_name, $_MIDGARD['schema']['types']))
            {
                $this->_schema->create_schema($schema_name);
                $this->_type_create = $schema_name;
            } 
            else
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Storagetype for $schema_name missing. ");
            } 
        }
        $this->_schema->set_current_type($this->_type_create);
        
        debug_add("Trying to create {$this->_type_create} with schema {$schema_name}");
        
        $this->_schemadb_object[$schema_name] = $this->_schema->get_schema($this->_type_create, $schema_name);
        //var_dump($this->_schemadb_object[$schema_name]);
        // Initialize sessioning first        
        $session = new midcom_service_session();
        $this->_session = & $session;

        // Start up the Datamanager in the usual session driven create loop
        // (create mode if seesion is empty, otherwise regular edit mode)
        
        if (!$session->exists('admin_create_id'))
        {
            debug_add('We do not currently have a content object, entering creation mode.');

            $this->_object_create = null;

            $this->_schemadb_object[$schema_name]['fields']['sitegroup']['default'] = $this->_object->sitegroup;
            $this->_schemadb_object[$schema_name]['fields']['sitegroup']['hidden'] = true;
            $this->_schemadb_object[$schema_name]['fields']['id']['hidden'] = true;
            $this->_schemadb_object[$schema_name]['fields']['guid']['hidden'] = true;

            if (!$this->_is_root)
            {
                $up_attribute = $this->_schema->get_up_attribute($this->_type_create);
                $session->set('admin_create_up', $this->_object->id);
                $this->_schemadb_object[$schema_name]['fields'][$up_attribute]['hidden'] = true;
                $this->_schemadb_object[$schema_name]['fields'][$up_attribute]['default'] = $this->_object->id;
                debug_add("Saving up attribute: ", $up_attribute." : ".$this->_object->id);
            }

            if (!$this->_prepare_creation_datamanager($schema_name))
            {
                debug_pop();
                return false;
            }

            $create = true;
        }
        else
        {
            $id = $session->get('admin_create_id');
            debug_add("We have found the object id {$id} in the session, loading object and entering regular edit mode.");

            // Try to load the object and to prepare its datamanager.
            if (!$this->_load_object($id) || !$this->_prepare_datamanager())
            {
                $session->remove('admin_create_id');
                debug_add("We could not find the object with id $id");
                debug_pop();
                return false;
            }

            $create = false;
        }

        // Ok, we have a go.        
        switch ($this->_datamanager->process_form())
        {
            case MIDCOM_DATAMGR_CREATING :
                if (!$create)
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

            case MIDCOM_DATAMGR_EDITING :
                if ($create)
                {
                    $id = $this->_object_create->guid;
                    debug_add("First time submit, the DM has created an object, adding ID {$id} to session data");
                    $session->set('admin_create_id', $id);
                }
                else
                {
                    debug_add('Subsequent submit, we already have an id in the session space.');
                }
                $this->_view = 'create';
                break;

            case MIDCOM_DATAMGR_SAVED :
                debug_add('Datamanger has saved, relocating to view.');

                $session->remove('admin_create_id');

                // Reindex the object 
                $indexer = & $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanager);
                debug_print_r("SOme objects:", $this->_object);
                debug_print_r("obj, create: objects:", $this->_object_create);

                $_MIDCOM->relocate("objectbrowser/".$this->_object->guid.".html");
                // This will exit

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED :
                if (!$create)
                {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED_NONECREATED unknown for non-creation mode.';
                    debug_pop();
                    return false;
                }
                else
                {
                    debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                    $_MIDCOM->relocate('objectbrowser/');
                    // This will exit
                }

            case MIDCOM_DATAMGR_CANCELLED :
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
                    if (!mgd_delete_extensions($this->_object_create) || !$this->_object_create->delete())
                    {
                        $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT, 'Failed to remove temporary object or its dependants.');
                        // This will exit
                    }
                    $session->remove('admin_create_id');
                    $_MIDCOM->relocate('');
                    // This will exit
                }

            case MIDCOM_DATAMGR_FAILED :
            case MIDCOM_DATAMGR_CREATEFAILED :
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanger failed to process the request, see the debug level log for details.';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default :
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;

        }

        debug_pop();
        return true;
    }

    /**
     * Renders the selected object using the datamnager view mode.
     * 
     * @access private
     */
    function _show_create($handler_id, & $data)
    {
        midcom_show_style('admin_create');
    }

    /**
     * General request initialization, which populates the object toolbar.
     */
     /*
    function _on_handle($handler_id, $args)
    {
        return true;
    }
*/
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
        //$GLOBALS['de_linkm_taviewer_schemadb_objects'] = array_merge(Array ('' => $this->_l10n->get('default setting')), $this->_config->get('schemadbs'));

    }

    /**
     * Internal helper, creates a valid name for a given object. It calls
     * generate_error on any failure.
     * 
     * @param midcom_baseclasses_database_object $object The article to process, if omitted, the currently selected article is used instead.
     * @return midcom_baseclasses_database_object The updated object.
     * @access private
     */
    function _generate_urlname($object = null)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (!$object)
        {
            $object = $this->_object;
        }

        $updated = false;

        if ($this->_missing_index && !$this->_config->get('autoindex'))
        {
            // Note that this code-block probably executes very seldomly, as the missing
            // index is caught during object creation. It could only happen if
            // you rename an index object forcefully, so this check should stay
            // here.
            $object->name = 'index';
            $updated = $object->update();
        }
        else
        {
            $tries = 0;
            $maxtries = 99;
            while (!$updated && $tries < $maxtries)
            {
                $object->name = midcom_generate_urlname_from_string($object->title);
                if ($tries > 0)
                {
                    // Append an integer if objects with same name exist
                    $object->name .= sprintf("-%03d", $tries);
                }
                $updated = $object->update();
                $tries ++;
            }
        }

        if (!$updated)
        {
            debug_print_r('Failed to update the Article with a new URL, last object state:', $object);
            $_MIDCOM->generate_error('Could not update the object\'s URL Name: '.mgd_errstr());
            // This will exit()
        }

        debug_pop();
        return $object;
    }

    /**
     * Callback for the datamanager create mode.
     * 
     * @access protected
     */
    function _dm_create_callback(& $datamanager)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $result = Array ('success' => true, 'storage' => null,);

        $this->_object_create = new $this->_type_create();

        $this->_object_create->author = $_MIDCOM->auth->user->id;
        // set the up atribute unless we are creating a root object
        if ($this->_object !== null)
        {
            $up_attribute = $this->_schema->get_up_attribute($this->_type_create);
            $this->_object_create-> $up_attribute = $this->_session->get('admin_create_up');
        }

        if (!$this->_object_create->create())
        {
            debug_add('Could not create object: '.$this->_type_create.mgd_errstr(), MIDCOM_LOG_WARN);
            debug_add("Could not create object {$this->_object_create->name} , {$this->_object_create->object}: " . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }
        $this->_object = new $this->_type_create();
        $this->_object->get_by_id($this->_object_create->id);
        $result['storage'] = & $this->_object_create;
        debug_pop();
        return $result;
    }

    /**
     * Deletes the currently active object 
     * On success, it will return to the welcome page, on failure, it returns false. 
     * 
     * @return bool Indicating success
     * @access private
     * @todo delete dependants and relocate to the parent object.
     */
    function _delete_record()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $up = $this->_object->up;
        $guid = $this->_object->guid;

        if (!$this->_object->delete())
        {
            $this->errstr = "Could not delete {$this->_type} with guid {$this->_object->guid}: ".mgd_errstr();
            $this->errcode = MIDCOM_ERRFORBIDDEN;
            debug_pop();
            return false;
        }

        // Update the index
        $indexer = & $_MIDCOM->get_service('indexer');
        $indexer->delete($guid);

        // Invalidate the cache modules
        $_MIDCOM->cache->invalidate($guid);

        // Redirect to parent object.
        if ($up > 0)
        {
            $_MIDCOM->relocate("objectbrowser/{$up}.html");
        }
        else
        {
            $_MIDCOM->relocate("objectbrowser/");
        }
        // This will exit()
    }

    /**
     * Return the metadata of the current object.
     */
    function get_metadata()
    {
        if (is_null($this->_object))
        {
            return false;
        }
        return array (MIDCOM_META_CREATOR => $this->_object->creator, MIDCOM_META_EDITOR => $this->_object->revisor, MIDCOM_META_CREATED => $this->_object->created, MIDCOM_META_EDITED => $this->_object->revised);
    }

    /**
     * Internal helper, called before the edit form is shown.
     * 
     * This is a rather bloody hack to modify the schema while the datamanager
     * is already up and running. It will make the url name field read-only 
     * if the current user is not a power user or admin and if we are looking
     * at the index object.
     * 
     * @todo Move the API to use the new MidCOM ACL stuff (and add acl studd)
     * @access private
     */
    function _patch_active_schema()
    {
        if ($this->_object->name == 'index')
        {
            $this->_datamanager->_fields["name"]["readonly"] = true;

        }
    }

    function _handler_move($handler_id, $args, & $data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (count($args) == 0)
        {
            // todo: add a relocate here!
            // (though you should never end up here. )        
        }
        if (!$this->_load_object($args[0])) // removed DM start here -> not needed!
        {
            debug_add("Could not load object with id {$args[0]}. Aborting.");
            debug_pop();
            return false;
        }

        /* use this to pass params between relocates etc. */
        $this->_session = new midcom_service_session();
        $this->prepare_object_toolbar();
        /* f_copyto might not be set if the user pressed false */
        if (array_key_exists('f_moveto', $_POST))
        {
            $object = new midcom_baseclasses_database_object($_POST['f_moveto']);
            // you must have write priveledges to the object you are moving to.

            if ($_MIDCOM->auth->can_do('midcom:create', $object))
            {
                $this->_object->up = $_POST['f_moveto'];
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                if ($this->_object->update())
                {
                    $this->_session->set('msg', $this->_l10n->get('Object moved sucessfully.'));

                    debug_add("Object {$this->_object->guid}, id. {$this->_object->id}  moved to object {$object->name}, id: {$object->guid}", MIDCOM_LOG_ERROR);
                    $_MIDCOM->relocate($prefix.'objectbrowser/'.$this->_object->guid);
                }
                else
                {
                    // todo : add errorlog.
                    $this->_session->set('msg', $this->_l10n->get('Object not moved'));
                    debug_add("Object {$this->_object->title}, id. {$this->_object->title} _NOT_ moved to object {$object->name}, id: {$object->id}", MIDCOM_LOG_ERROR);
                }
            }
            else
            {
                /* copyto not set this means that the user pressed cancel. */
                $this->_session->set('msg', $this->_l10n->get('Access denied. You cannot move the object there.'));
                debug_add('Move Access denied to object'.$object->id." ".mgd_errstr(), MIDCOM_LOG_WARN);

                $this->_request_data['first_run'] = true;
            }
        }
        else
        {

            $this->_request_data['first_run'] = true;
        }

        debug_pop();
        return true;
    }

    function _show_move()
    {

        if ($this->_request_data['first_run'])
        {
            midcom_show_style('object-move');
        }

    }

    function _handler_object_index()
    {
    }

    function _show_object_index()
    {
    }

    function _handler_index()
    {
        //$this->_request_data['schema'] = &$this->_schema;
        return true;
    }

    function _show_index()
    {

        //midcom_show_style('index');
        echo "<table width='80%' border='1' padding='0' cellspacing='0'>";

        echo "<tr><td> Type: </td><td>Node or leaf?</td><td>MGD_META_TREE_CHILDS</td>"."<td>MGD_META_TREE_PARENT</td><td>MGD_META_PROPERTY_PRIMARY</td>"."<td>MGD_META_PROPERTY_UP</td><td>MGD_META_PROPERTY_PARENT</td></tr>";
        foreach ($this->_schema->_meta as $type => $values)
        {
            if ($this->_schema->nav_hide($type))
                continue;
            echo "<tr>";

            echo "<td>$type</td>";
            echo "<td>{$values[MIDCOM_SCHEMA_OBJECT_TYPE]}</td>";
            echo "<td>";
            foreach ($values[MGD_META_TREE_CHILDS] as $child => $dd)
                echo $child."<br />";
            echo "</td>";
            echo "<td>{$values[MGD_META_TREE_PARENT]}</td>";
            echo "<td>{$values[MGD_META_PROPERTY_PRIMARY]}</td>";
            echo "<td>{$values[MGD_META_PROPERTY_UP]}</td>";
            echo "<td>{$values[MGD_META_PROPERTY_PARENT]}</td>";
            //echo "<td>{$values[]}</td>";
            echo "</tr>";

        }
        echo "</table>\n\n";

    }

}
?>

