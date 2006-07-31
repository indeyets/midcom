<?php

/**
 * Created on Aug 3, 2005
 *
 * Create, edit and delete basic objects
 * 
 * This class abstracts out the main parts of the create - edit delete cycle for styles and 
 * styleelements.
 * 
 * @package midcom.admin.styleeditor
 */

class midcom_admin_styleeditor_handler_base extends midcom_baseclasses_components_handler
{

    /**
     * The object we are currently editing
     * @var midcom_db_element or midcom_db_pageelement.
     */
    var $_current;
    /**
     * The id of the current style we are editing
     * 
     * @access private
     */
    var $_style = null;

    /**
     * The schema the datamanager uses. It is either set by default or updated by
     * the _set_schema function that is subclassed.
     * 
     */
    var $_current_schema = null;
    
    /**
     * The name of the current midcom we are using as a context for 
     * defining which styleelements should be shown.
     */
    var $_midcom = null;

    /**
     *  if we got a topic to point to... 
     * */
    var $_topic = null;

    /** 
     * a host or a page object
     */
    var $_page = null;

    /**
     * The module config.
     */
    var $_config = array ();

    /**
     * The datamanager controller
     * @access private
     * @var midcom_helper_datamanager2_controller
     * 
     */
    var $_controller = null;

    /**
     * Constructor
     */
    function midcom_admin_styleeditor_handler_element()
    {
        parent :: midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {

    }

    /**
     * generate the datamanager controller instance and run it.
     * @param string type of controller (simple or nullstorage)
     */
    function _run_datamanager($type, $defaults = array ())
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create($type);
        $this->_controller->load_schemadb($this->_current_schema);
        $this->{"_run_datamanager_$type"}();
        $this->_controller->defaults = $defaults;
        $this->_controller->initialize();
        
        $this->_request_data['datamanager'] = & $this->_controller;
        return $this->_controller->process_form();
    }
    
    function _run_datamanager_simple () {
        $this->_controller->set_storage($this->_current);
    }
    
    function _run_datamanager_create() {
            $this->_controller->callback_object = &$this;
    }
    
    function _run_datamanager_nullstorage() {
    }
    

    /**
     * If you want to define the correct schema, use this function. Or, define the 
     * schema in the _schema variable.
     * One possible value is:
     * $this->_current_schema = $this->_config->get('schmadb_article');
     */
    function _set_schema($handler_id)
    {
    }

    /**
     * This function should be subclassed out into the subclasses
     * Abstract function. 
     */
    function _set_current_object($args)
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "this must be implemented in the subclass");
        // This will exit().
    }
    /**
     * Another abstract function. This generates the toolbar on top.
     */
    function _generate_toolbar()
    {   
        // todo handle the root scenario
        if ($this->_style == null ) 
        {
            return;
        }
        
        $toolbar = &midcom_helper_toolbars::get_instance();
        
        $style_guid = $this->_style->guid;
        $toolbar->top->add_item(Array(
        MIDCOM_TOOLBAR_URL =>  $this->_master->get_prefix() . "element/new/{$style_guid}.html",
        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("New element"),
        MIDCOM_TOOLBAR_HELPTEXT => null,
        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
        MIDCOM_TOOLBAR_ENABLED => true,
        MIDCOM_TOOLBAR_OPTIONS => array('accesskey' => 'n'),
        MIDCOM_TOOLBAR_HIDDEN =>
        ! (
           $_MIDCOM->auth->can_do('midgard:create', $this->_style)
          )
        
        ));
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL =>  $this->_master->get_prefix() . "style/create/{$style_guid}.html",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n->get("New substyle"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
               $_MIDCOM->auth->can_do('midgard:create', $this->_style)
              )
        ));
                
    }

    /**
     * This function generates an overview over which object you edit.
     * 
     */
    function _generate_elementlist()
    {

        $nav = & $this->_request_data['aegir_interface']->get_navigation();
        $finder = midcom_admin_styleeditor_stylefinder :: factory();
        $finder->set_cache($nav->get_cache());
        $finder->set_style($this->_style);
        $finder->set_midcom($this->_midcom);
        if ($this->_topic != null)
        {
            $finder->set_topic($this->_topic);
        }
        if ($this->_page !== null)
        {
            $finder->set_page($this->_page);
        }
        $toolbar_factory = new midcom_admin_styleeditor_toolbarfactory(& $finder);
        $toolbar = & midcom_helper_toolbars :: get_instance();
        $toolbar_factory->set_toolbar(& $toolbar->bottom);
        $toolbar_factory->generate_toolbar();

    }
    /**
     * Delete the current object
     * @todo : fix the delete_ok check.
     * 
     */
    function _handler_delete($handler_id, $args, & $data)
    {
        $this->_set_current_object($args);
        $this->_request_data['object_guid'] = $this->_current->guid;
        $this->_generate_toolbar();
        $_MIDCOM->auth->require_do('midgard:delete', $this->_current->guid);
        //var_dump($_REQUEST);
        if (array_key_exists('styleeditor_deleteok', $_REQUEST)  )
        {
            $up = $this->get_up();
                        
            if ($this->_current->delete()) 
            {
                if ($up == 0 ) {
                    $_MIDCOM->relocate ($this->_master->get_prefix()  );
                } 
                else 
                {
                    $_MIDCOM->relocate ($this->_master->get_prefix() ."style/{$up->guid}/{$up->name}" );
                }
            } 
            else 
            {
                $this->_request_data['reason'] = mgd_errstr();
                $this->_request_data['show'] = 'delete_failed';
            }
            
        }
        else 
        {
            // as this class is subclassed this info will be set by the correct handler.
            $this->_get_object_info();
            $this->_request_data['show'] = 'deletecheck'; 
            $this->_request_data['object_type'] = (get_class($this->_current) == 'midcom_db_element') ? 'element' : 'style';
            $this->_request_data['object'] = $this->_current;
        }
        return true;
        
    }
    
    function _show_delete() {
        midcom_show_style ($this->_request_data['show']);        
    }

    /**
     * Set the style. 
     */
    function _set_style($styleid)
    {
        $this->_style = $styleid;
    }

    /**
     * Set the midcom we should use to generate the list of possible elements to edit.
     */
    function _set_midcom($midcom)
    {

        $this->_midcom = $midcom;
    }
    /**
     * Set the topic we should use to generate the list of possible elements to edit.
     */
    function _set_topic($topic)
    {
        $this->_topic = $topic;
    }

    /**
     * Get object defaults (if any)
     * 
     */
    function get_defaults() {
        return array();
    }
    
    /**
     * Get the 
     */

    /**
     * 
     */
    function _handler_edit($handler_id, $args, & $data)
    {
        
        $this->_set_schema($handler_id);
        $this->_set_current_object($args);
        if ($this->_run_datamanager('simple') == 'save') {
            $this->_reload_cache();
        }
        $this->_generate_toolbar();
        
        return true;
    }

    function _show_edit()
    {
        midcom_show_style("admin_edit2");
    }

    function _handler_create($handler_id, $args, & $data)
    {
        $this->_set_schema($handler_id);
        $this->_set_current_object($args);
        $defaults = $this->get_create_defaults();
        $result = $this->_run_datamanager( 'nullstorage', $defaults);
        $this->_on_create();
        if ($result == 'save' ) 
        {
            $object =& $this->dm2_create_callback();
            if ($object) 
            {
                $this->_current =&$object;
                $result = $this->_run_datamanager('simple');
                if ($result == 'edit') {
                    $_MIDCOM->generate_error("Could not update created topic with id $id!");
                    // this will exit.
                }
                $this->_reload_cache();
                $_MIDCOM->relocate($this->get_creation_relocate($object));
            } 
            else 
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not create object.");
            }
        }
        
        $this->_generate_toolbar();
        return true;
    }
    /**
     * Callback function to be used by the subclasses to define where they want to go.
     */
    function get_creation_relocate($object ) {
        $type = is_a($this, 'midcom_admin_styleeditor_handler_style') ? 'style' : 'element';    
        return "styleeditor/{$type}/{$object->guid}/{$object->name}.html";
    }
    
    function _show_create()
    {
        midcom_show_style("admin_edit2");
    }
    
    function & dm2_create_callback ( &$controller) 
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "this must be implemented in the subclass");
        // This will exit().
    }
    /**
     * This function is used by the subclass to set some simple texts in the 
     * template
     */
    function _on_create() {}
    /**
     * Reloads the midcom cache.
     */
    function _reload_cache() {
        mgd_cache_invalidate();
    }
}
