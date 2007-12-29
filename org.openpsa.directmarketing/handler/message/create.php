<?php
/**
 * @package org.openpsa.directmarketing
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 4505 2006-10-29 15:53:49Z tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Direct marketing page handler
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_handler_message_create extends midcom_baseclasses_components_handler
{
    /**
     * The message which has been created
     *
     * @var org_openpsa_directmarketing_campaign_message
     * @access private
     */
    var $_message = null;

    /**
     * The Controller of the message used for editing
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
     * The schema to use for the new message.
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    /**
     * The defaults to use for the new message.
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
        $this->_request_data['schema'] =& $this->_schema;
    }

    /**
     * Simple default constructor.
     */
    function org_openpsa_directmarketing_handler_create()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
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
        $this->_schemadb =& $this->_request_data['schemadb_message'];
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
        $this->_message = new org_openpsa_directmarketing_campaign_message();
        //  duh ? (copy-paste artefact ??)
        $this->_message->campaign = $this->_request_data['campaign']->id;
        $this->_message->orgOpenpsaObtype = $this->_schemadb[$this->_schema]->customdata['org_openpsa_directmarketing_messagetype'];

        if (! $this->_message->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_message);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new message, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_message;
    }

    /**
     * Displays a message edit view.
     *
     * Note, that the message for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation message,
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $data['campaign'] = new org_openpsa_directmarketing_campaign($args[0]);
        if (   !is_object($data['campaign'])
            || !$data['campaign']->id)
        {
            // TODO: better error reporting
            return false;
        }
        $_MIDCOM->auth->require_do('midgard:create', $data['campaign']);

        $this->_component_data['active_leaf'] = "campaign_{$data['campaign']->id}";  

        $this->_schema = $args[1];
        
        if (!array_key_exists($this->_schema, $this->_request_data['schemadb_message']))
        {
            // This message type isn't available for our schema, return error
            return false;
        }

        $this->_load_controller();
        $this->_prepare_request_data();
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the message
                //$indexer =& $_MIDCOM->get_service('indexer');
                //org_openpsa_directmarketing_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                $_MIDCOM->relocate("message/{$this->_message->guid}/");

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        if ($this->_message != null) 
        {
            $_MIDCOM->set_26_request_metadata($this->_message->revised, $this->_message->guid);
        }
        $data['view_title'] = sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($this->_schemadb[$this->_schema]->description));
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
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
            MIDCOM_NAV_URL => "create/{$this->_schema}.html",
            MIDCOM_NAV_NAME => sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($this->_schemadb[$this->_schema]->description)),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Shows the loaded message.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('show-message-new');
    }
}

?>
