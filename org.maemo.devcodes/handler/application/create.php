<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 4505 2006-10-29 15:53:49Z tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * create application handler
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_application_create extends midcom_baseclasses_components_handler
{
    /**
     * The application which has been created
     *
     * @var midcom_db_application
     * @access private
     */
    var $_application = null;

    /**
     * The Controller of the article used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The schema name in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schema = null;

    /**
     * The defaults to use for the new article.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    var $_device = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
    }


    /**
     * Simple default constructor.
     */
    function org_maemo_devcodes_handler_application_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatement is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = $this->_schema;
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_application = new org_maemo_devcodes_application_dba();
        if (isset($_POST['device']))
        {
            $this->_application->device = $_POST['device'];
        }
        else
        {
            $this->_application->device = $this->_device->id;
        }
        if (isset($_POST['applicant']))
        {
            $this->_application->applicant = $_POST['applicant'];
        }
        else
        {
            $this->_application->applicant = $_MIDGARD['user'];
        }

        if (! $this->_application->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_application);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new application, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_application;
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article,
     */
    function _handler_create($handler_id, $args, &$data)
    {
        // TODO: better check ?
        $this->_topic->require_do('midgard:create');
        $this->_schema = 'application';

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                /*
                $indexer =& $_MIDCOM->get_service('indexer');
                org_maemo_devcodes::index($this->_controller->datamanager, $indexer, $this->_topic);
                */

                $_MIDCOM->relocate("application/{$this->_application->guid}.html");
                // This will exit.
            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();
        $title = $this->_l10n->get('create developer application');
        $this->_request_data['title'] = $title;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "create/application.html",
            MIDCOM_NAV_NAME => $data['title'],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * Shows the loaded article.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('admin-create-application');
    }

    function _handler_apply($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_device =& org_maemo_devcodes_device_dba::get_cached($args[0]);
        if (  !$this->_device
            || empty($this->_device->guid))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The device '{$args[0]}' was not found.");
            // This will exit.
        }
        if (!$this->_device->is_open())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The device '{$args[0]}' is not open for applications.");
            // This will exit.
        }

        // TODO: Verify that user has not already applied for device.
        $qb = org_maemo_devcodes_application_dba::new_query_builder();
        $qb->add_constraint('device', '=', $this->_device->id);
        $qb->add_constraint('applicant', '=', $_MIDGARD['user']);
        $applications = $qb->execute();
        if (!is_array($applications))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'QB failed critically when looking up existing applications');
            // this will exit
        }
        if (!empty($applications))
        {
            if (count($applications) > 1)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Found multiple existing applications for device #{$this->_device->id} with applicant #{$_MIDGARD['user']}", MIDCOM_LOG_WARN);
                debug_pop();
            }
            // see the relocate in process_form as well
            $_MIDCOM->relocate("application/{$applications[0]->guid}.html");
        }    

        if (!$this->_device->can_apply($_MIDGARD['user']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "User #{$_MIDGARD['user']} cannot apply for device #{$this->_device->id}, errstr: " . mgd_errstr());
            // this will exit
        }

        $this->_schema = 'application-user';

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                /*
                $indexer =& $_MIDCOM->get_service('indexer');
                org_maemo_devcodes::index($this->_controller->datamanager, $indexer, $this->_topic);
                */
                $this->_device->set_parameter('midcom.helper.datamanager2', 'schema_name', 'application');
                // TODO remove owner privileges from the current user (so that they can't do any funny stuff)

                // TODO: Relocate somewhere else ??
                $_MIDCOM->relocate("application/{$this->_application->guid}.html");
                // This will exit.
            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();

        $data['title'] = sprintf($this->_l10n->get('apply for developer %s'), $this->_device->title);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['title']}");

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "device/applyfor/{$this->_device->guid}.html",
            MIDCOM_NAV_NAME => $data['title'],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);


        return true;
    }

    function _show_apply($handler_id, &$data)
    {
        midcom_show_style('view-apply-device');
    }
}

?>
