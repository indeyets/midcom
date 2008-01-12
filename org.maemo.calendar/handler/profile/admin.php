<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.net
 * @version $Id: admin.php 4152 2006-09-20 18:24:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.net
 * @license http://www.gnu.net/licenses/lgpl.html GNU Lesser General Public License
 */

//require_once(MIDCOM_ROOT . '/net/nehmer/account/viewer.php');

/**
 *
 * @package org.maemo.calendar
 */
class org_maemo_calendar_handler_profile_admin extends midcom_baseclasses_components_handler
{
    /**
     * The person to register for
     *
     * @var array
     * @access private
     */
    var $_account = null;

    /**
     * The schema database (taken from the config)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The schema (taken from the config)
     *
     * @var Array
     * @access private
     */
    var $_schema = null;

    /**
     * The Datamanager of the person to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_controller = null;

    var $_save_status = false;

    /**
     * Simple default constructor.
     */
    function org_maemo_calendar_handler_profile_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _prepare_request_data($handler_id)
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['account'] =& $this->_account;
        $this->_request_data['saved'] =& $this->_save_status;
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $_MIDCOM->auth->require_valid_user();
    }

    /**
     * Loads and prepares the schema database.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database( $this->_config->get('profile_schemadb') );
        $this->_schema = $this->_config->get('profile_schema');
    }

    /**
     * Internal helper, loads the controller for the current event. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();

        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = $this->_schema;
        $this->_controller->set_storage($this->_account, $this->_schema);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for person {$this->_account->id}.");
            // This will exit.
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        if ($handler_id == 'ajax-profile-edit')
        {
            $_MIDCOM->skip_page_style = true;
        }

        $this->_save_status = false;

        $this->_account = $_MIDCOM->auth->user->get_storage();
        net_nehmer_account_viewer::verify_person_privileges($this->_account);
        $_MIDCOM->auth->require_do('midgard:update', $this->_account);
        $_MIDCOM->auth->require_do('midgard:parameters', $this->_account);

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                $this->_account->set_parameter('net.nehmer.account', 'revised', time());
                $this->_save_status = true;
                break;
            case 'cancel':
                break;
                // This will exit.
        }

        $this->_prepare_request_data($handler_id);
        $_MIDCOM->bind_view_to_object($this->_account, $this->_request_data['controller']->datamanager->schema->name);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        if ($handler_id == 'ajax-profile-edit')
        {
            midcom_show_style('profile-edit-ajax');
        }
        else
        {
            midcom_show_style('profile-edit');
        }
    }

}

?>