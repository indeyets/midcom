<?php
/**
 * @package midcom.admin.user
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Style editor class for listing style elements
 * 
 * @package midcom.admin.user
 */
class midcom_admin_user_handler_user_edit extends midcom_baseclasses_components_handler
{
    var $_person = null;

    /**
     * Simple constructor
     * 
     * @access public
     */
    function midcom_admin_user_handler_user_edit()
    {
        $this->_component = 'midcom.admin.user';
        parent::midcom_baseclasses_components_handler();
     }
    
    function _update_breadcrumb()
    {
        // Populate breadcrumb
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/",
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('midcom.admin.user', 'midcom.admin.user'),
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/edit/{$this->_person->guid}",
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Loads and prepares the schema database.
     */
    function _load_schemadb($config_key)
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get($config_key));
    }

    /**
     * Internal helper, loads the controller for the current person. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_person, 'default');
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for person {$this->_person->id}.");
            // This will exit.
        }
    }

    
    /**
     * Handler method for listing style elements for the currently used component topic
     *
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $args Array containing the variable arguments passed to the handler
     * @param mixed $data Data passed to the show method
     * @return boolean Indicating successful request
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_person = new midcom_db_person($args[0]);
        if (   !$this->_person
            || !$this->_person->guid)
        {
            return false;
        }
        $this->_person->require_do('midgard:update');
    
        $data['view_title'] = sprintf($_MIDCOM->i18n->get_string('edit %s', 'midcom.admin.user'), $this->_person->name);
        $_MIDCOM->set_pagetitle($data['view_title']);
                
        $data['asgard_toolbar'] = new midcom_helper_toolbar();

        
        if ($handler_id == '____mfa-asgard_midcom.admin.user-user_edit_password')
        {
            $this->_load_schemadb('schemadb_account');
        }
        else
        {
            $this->_load_schemadb('schemadb_person');
        }
        
        $this->_load_controller();
        switch ($this->_controller->process_form())
        {
            case 'save':
                // Show confirmation for the user
                $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('midcom.admin.user'), sprintf($this->_l10n->get('person %s saved'), $this->_person->name));
                $_MIDCOM->relocate("__mfa/asgard_midcom.admin.user/edit/{$this->_person->guid}/");
                // This will exit.
                
            case 'cancel':
                $_MIDCOM->relocate('__mfa/asgard_midcom.admin.user/');
                // This will exit.
        }
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.user');
        $_MIDCOM->skip_page_style = true;

        $data['language_code'] = '';
        midgard_admin_asgard_plugin::bind_to_object($this->_person, $handler_id, &$data);
        $this->_update_breadcrumb();
        
        return true;
    }
    
    /**
     * Show list of the style elements for the currently edited topic component
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $data Data passed to the show method
     */
    function _show_edit($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        
        $data['person'] =& $this->_person;
        $data['controller'] =& $this->_controller;
        midcom_show_style('midcom-admin-user-person-edit');
        
        midcom_show_style('midgard_admin_asgard_footer');    
    }
}
?>
