<?php
/**
* @package fi.protie.garbagetruck
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Person related handlers
 * 
 * @package fi.protie.garbagetruck
 */
class fi_protie_garbagetruck_handler_person extends midcom_baseclasses_components_handler
{
    /**
     * Object containing the selected person
     * 
     * @access private
     * @var midcom_db_person
     */
    var $_person = null;
    
    /**
     * DM2 instance
     * 
     * @access private
     */
    var $_datamanager = null;
    
    /**
     * DM2 controller instance
     * 
     * @access private
     */
    var $_controller = null;
    
    /**
     * Show the style depending on the action requested
     * 
     * @access private
     * @var string
     */
    var $_show_style = '';
    
    /**
     * Simple constructor, which calls for the baseclass
     * 
     * @access protected
     */
    function fi_protie_garbagetruck_handler_person()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Get the schemadbs connected to persons
     * 
     * @access private
     */
    function _on_initialize()
    {
        $this->_schemadb =& $this->_request_data['schemadb_person'];
    }
    
    /**
     * Simple collector, which creates the reference between the original instances and
     * request data for outputting the information.
     * 
     * @access private
     */
    function _populate_request_data()
    {
        $this->_request_data['person'] =& $this->_person;
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }
    
    /**
     * Load the datamanager for the requested object.
     * 
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        
        if (   !$this->_datamanager
            || !$this->_datamanager->autoset_storage($this->_person))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a Datamanager 2 instance for person {$this->_person->name}");
            // This will exit
        }
    }
    
    /**
     * Loads the DM2 creation controller for the requested person
     * 
     * @access private
     */
    function _load_create_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'default';
        $this->_controller->callback_object =& $this;
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }
    
    /**
     * Loads the DM2 editing controller for the requested person
     * 
     * @access private
     */
    function _load_edit_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'default';
        $this->_controller->set_storage($this->_person);
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
    }
    
    /**
     * DM2 creation callback
     */
    function &dm2_create_callback(&$controller)
    {
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('firstname', '=', $_POST['firstname']);
        $qb->add_constraint('lastname', '=', $_POST['lastname']);
        
        if ($qb->count() > 0)
        {
            $_MIDCOM->relocate('person/create/?error=person%20exists');
            // This will exit
        }
        
        if (!$this->_person->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_person);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new person, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }
        
        return $this->_person;
    }
    
    /**
     * Load the DM2 instance for creation of a new person object
     * 
     * @access private
     * @return bool Indicating success
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_load_create_controller();
        $this->_person = new midcom_db_person();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("person/{$this->_person->guid}/");
                break;
            
            case 'cancel':
                $_MIDCOM->relocate('');
                break;
        }
        
        $this->_topic->require_do('midgard:create');
        
        return true;
    }
    
    /**
     * Show creation form
     * 
     * @access private
     */
    function _show_create($handler_id, &$data)
    {
        $this->_request_data['page_title'] = $this->_l10n->get('create a new person');
        $this->_populate_request_data();
        
        midcom_show_style('create_form');
    }
    
    /**
     * Loads the person and its datamanager
     * 
     * @access private
     * @param String GUID of the requested person
     */
    function _get_person($guid)
    {
        if (!mgd_is_guid($guid))
        {
            return false;
        }
        
        $this->_person = new midcom_db_person();
        if (!$this->_person->get_by_guid($guid))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check the integrity of the requested person.
     * 
     * Matches /person/<person guid>/
     * 
     * @access private
     */
    function _handler_view($handler_id, $args, &$data)
    {
        if (!$this->_get_person($args[0]))
        {
            return false;
        }
        
        if ($this->_topic->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "person/{$this->_person->guid}/edit/",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('edit person %s'), $this->_person->name),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                )
            );
        }
        
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "person/create/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create a new person'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                )
            );
        }
        
        return true;
    }
    
    /**
     * Show the person details
     * 
     * @access private
     */
    function _show_view($handler_id, &$data)
    {
        $this->_load_datamanager();
        $this->_populate_request_data();
        
        midcom_show_style('show_person');
    }
    
    /**
     * Handler for actions for the requested person. Loads DM2 instances.
     * 
     * @access private
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        if (!$this->_get_person($args[0]))
        {
            return false;
        }
        
        $this->_topic->require_do('midgard:update');
        
        $this->_load_edit_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                $_MIDCOM->relocate("person/{$this->_person->guid}/");
                // This will exit
                break;
                
            case 'cancel':
                $_MIDCOM->relocate("person/{$this->_person->guid}/");
                // This will exit
                break;
                
        }
        
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "person/{$this->_person->guid}",
                MIDCOM_TOOLBAR_LABEL => sprintf($this->_l10n->get('back to person %s'), $this->_person->name),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
            )
        );
        
        return true;
    }
    
    /**
     * Show the editing form for the person
     * 
     * @access private
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['page_type'] = 'person';
        $this->_request_data['page_title'] = sprintf($this->_l10n->get('edit person %s'), $this->_person->name);
        
        $this->_populate_request_data();
        
        midcom_show_style('edit_form');
    }
}
?>