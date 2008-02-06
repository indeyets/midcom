<?php
/**
 * @package org.maemo.devcodes
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 4505 2006-10-29 15:53:49Z tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * create code handler
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_handler_code_create extends midcom_baseclasses_components_handler
{
    /**
     * The code which has been created
     *
     * @var midcom_db_code
     * @access private
     */
    var $_code = null;

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
    function org_maemo_devcodes_handler_code_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatment is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_code'));
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
        $this->_code = new org_maemo_devcodes_code_dba();
        $this->_code->device = $_POST['device'];
        $this->_code->code = $_POST['code'];

        if (! $this->_code->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_code);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a new code, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_code;
    }

    /**
     * Displays an article edit view.
     *
     * Note, that the article for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation article
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');
        $this->_schema = 'code';

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                /*
                $indexer =& $_MIDCOM->get_service('indexer');
                org_maemo_devcodes::index($this->_controller->datamanager, $indexer, $this->_topic);
                */

                $_MIDCOM->relocate("code/{$this->_code->guid}.html");
                // This will exit.
            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        $this->_prepare_request_data();
        $title = $this->_l10n->get('create developer code');
        $this->_request_data['title'] = $title;
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "create/code.html",
            MIDCOM_NAV_NAME => $this->_request_data['title'],
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Shows the loaded article.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('admin-create-code');
    }



}

?>