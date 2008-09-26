<?php
/**
 * @package net.nemein.reservations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: create.php 4505 2006-10-29 15:53:49Z tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * n.n.reservations create page handler
 *
 * @package net.nemein.reservations
 */

class net_nemein_reservations_handler_create extends midcom_baseclasses_components_handler
{
    /**
     * The resource which has been created
     *
     * @var org_openpsa_calendar_resource_dba
     * @access private
     */
    var $_resource = null;

    /**
     * The Controller of the resource used for editing
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
     * The schema to use for the new resource.
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    /**
     * The defaults to use for the new resource.
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
    function __construct()
    {
        parent::__construct();
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
        $this->_schemadb =& $this->_request_data['schemadb_resource'];
        if (   $this->_config->get('simple_name_handling')
            && ! $_MIDCOM->auth->create)
        {
            foreach (array_keys($this->_schemadb) as $name)
            {
                $this->_schemadb[$name]->fields['name']['readonly'] = true;
            }
        }
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
        $this->_resource = new org_openpsa_calendar_resource_dba();
        $this->_resource->type = $this->_schema;

        if (! $this->_resource->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_resource);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new resource, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_resource;
    }

    /**
     * Displays a resource edit view.
     *
     * Note, that the resource for non-index mode operation is automatically determined in the can_handle
     * phase.
     *
     * If create privileges apply, we relocate to the index creation resource
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');

        $this->_schema = $args[0];

        if (!array_key_exists($this->_schema, $this->_request_data['schemadb_resource']))
        {
            // This resource type isn't available for our schema, return error
            return false;
        }

        $this->_load_controller();
        $this->_prepare_request_data();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the resource
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_reservations_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                // Generate URL name
                if ($this->_resource->name == '')
                {
                    $this->_resource->name = midcom_generate_urlname_from_string($this->_resource->title);
                    $tries = 0;
                    $maxtries = 999;
                    while(   !$this->_resource->update()
                          && $tries < $maxtries)
                    {
                        $this->_resource->name = midcom_generate_urlname_from_string($this->_resource->title);
                        if ($tries > 0)
                        {
                            // Append an integer if resources with same name exist
                            $this->_resource->name .= sprintf("-%03d", $tries);
                        }
                        $tries++;
                    }
                }
                $_MIDCOM->relocate("view/{$this->_resource->name}/");

            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit.
        }

        if ($this->_resource != null)
        {
            $_MIDCOM->set_26_request_metadata($this->_resource->revised, $this->_resource->guid);
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
            MIDCOM_NAV_URL => "create/{$this->_schema}/",
            MIDCOM_NAV_NAME => sprintf($this->_l10n_midcom->get('create %s'), $this->_l10n->get($this->_schemadb[$this->_schema]->description)),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }

    /**
     * Shows the loaded resource.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create ($handler_id, &$data)
    {
        midcom_show_style('view-resource-create');
    }



}

?>