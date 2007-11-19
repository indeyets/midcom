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

    function _on_initialize()
    {

        $this->_l10n = $_MIDCOM->i18n->get_l10n('midcom.admin.user');
        $this->_request_data['l10n'] = $this->_l10n;

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.user/usermgmt.css',
            )
        );

        midgard_admin_asgard_plugin::prepare_plugin($this->_l10n->get('midcom.admin.user'),$this->_request_data);

    }
    
    function _update_breadcrumb($handler_id)
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
        
        if ($handler_id == '____mfa-asgard_midcom.admin.user-user_edit_password')
        {
            $tmp[] = Array
            (
                MIDCOM_NAV_URL => "__mfa/asgard_midcom.admin.user/password/{$this->_person->guid}",
                MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('edit account', 'midcom.admin.user'),
            );
        }
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    function _prepare_toolbar(&$data,$handler_id)
    {
        if (   $handler_id != '____mfa-asgard_midcom.admin.user-user_edit_password'
            && $this->_config->get('allow_manage_accounts'))
        {
            $data['asgard_toolbar']->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__mfa/asgard_midcom.admin.user/password/{$this->_person->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('edit account', 'midcom.admin.user'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                ),
                $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            );
        }

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
    
        if ($handler_id == '____mfa-asgard_midcom.admin.user-user_edit_password')
        {
            if (!$this->_config->get('allow_manage_accounts'))
            {
                return false;
            }
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
                $_MIDCOM->uimessages->add($this->_l10n->get('midcom.admin.user'), sprintf($this->_l10n->get('person %s saved'), $this->_person->name));
                $_MIDCOM->relocate("__mfa/asgard_midcom.admin.user/edit/{$this->_person->guid}/");
                // This will exit.
                
            case 'cancel':
                $_MIDCOM->relocate('__mfa/asgard_midcom.admin.user/');
                // This will exit.
        }
        
        $data['language_code'] = '';
        midgard_admin_asgard_plugin::bind_to_object($this->_person, $handler_id, &$data);

        $data['view_title'] = sprintf($_MIDCOM->i18n->get_string('edit %s', 'midcom.admin.user'), $this->_person->name);
        $_MIDCOM->set_pagetitle($data['view_title']);
        $this->_prepare_toolbar($data,$handler_id);
        $this->_update_breadcrumb($handler_id);
        
        // Add jQuery Form handling for generating passwords with AJAX
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/jQuery/jquery.form-1.0.3.pack.js');
        
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
        midgard_admin_asgard_plugin::asgard_header();

        $data['l10n'] =& $this->_l10n;
        $data['person'] =& $this->_person;
        $data['controller'] =& $this->_controller;
        midcom_show_style('midcom-admin-user-person-edit');
        
        if (isset($_GET['f_submit']))
        {
            midcom_show_style('midcom-admin-user-generate-passwords');
        }
        
        midgard_admin_asgard_plugin::asgard_footer();
    }
    
    /**
     * Auto-generate passwords on the fly
     * 
     * @access public
     */
    function _handler_passwords($handler_id, $args, &$data)
    {
        $_MIDCOM->skip_page_style = true;
        return true;
    }
    
    /**
     * Auto-generate passwords on the fly
     * 
     * @access public
     */
    function _show_passwords($handler_id, &$data)
    {
        // Show passwords
        $data['l10n'] =& $this->_l10n;
        midcom_show_style('midcom-admin-user-generate-passwords');
    }
}
?>
