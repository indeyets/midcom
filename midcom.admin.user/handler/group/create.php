<?php
/**
 * @package midcom.admin.user
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * group creation class
 * 
 * @package midcom.admin.user
 */
class midcom_admin_user_handler_group_create extends midcom_baseclasses_components_handler
{
    var $_group = null;

    /**
     * Simple constructor
     * 
     * @access public
     */
    function midcom_admin_user_handler_group_create()
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
            MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/group/create/",
            MIDCOM_NAV_NAME => $this->_request_data['view_title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Loads and prepares the schema database.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_group'));
    }

    /**
     * Internal helper, loads the controller for the current group. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = 'default';
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to initialize a DM2 create controller.');
            // This will exit.
        }
    }

    /**
     * DM2 creation callback, creates a new group and binds it to the selected group.
     *
     * Assumes Admin Privileges.
     */
    function & dm2_create_callback (&$controller)
    {        
        // Create a new group
        $this->_group = new midcom_db_group();
        if (! $this->_group->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_group);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new group, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_group;
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
    function _handler_create($handler_id, $args, &$data)
    {    
        $data['view_title'] = $_MIDCOM->i18n->get_string('create group', 'midcom.admin.user');
        $_MIDCOM->set_pagetitle($data['view_title']);
                
        $data['asgard_toolbar'] = new midcom_helper_toolbar();
        
        $this->_load_controller();
        switch ($this->_controller->process_form())
        {
            case 'save':
                // Show confirmation for the group
                $_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.user'), sprintf($this->_l10n->get('group %s saved'), $this->_group->name));
                $_MIDCOM->relocate("__mfa/asgard_midcom.admin.user/group/edit/{$this->_group->guid}/");
                
            case 'cancel':
                $_MIDCOM->relocate('__mfa/asgard_midcom.admin.user/');
                // This will exit.
        }
        
        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midgard.admin.asgard');
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.user');
        $_MIDCOM->skip_page_style = true;

        $this->_update_breadcrumb();
        
        return true;
    }
    
    /**
     * Show list of the style elements for the currently createed topic component
     * 
     * @access private
     * @param string $handler_id Name of the used handler
     * @param mixed $data Data passed to the show method
     */
    function _show_create($handler_id, &$data)
    {
        midcom_show_style('midgard_admin_asgard_header');
        midcom_show_style('midgard_admin_asgard_middle');
        
        $data['group'] =& $this->_group;
        $data['controller'] =& $this->_controller;
        midcom_show_style('midcom-admin-user-group-create');
        
        midcom_show_style('midgard_admin_asgard_footer');    
    }
}
?>
