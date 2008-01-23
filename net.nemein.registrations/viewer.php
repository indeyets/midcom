<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event registration system site interface
 *
 * See the various handler classes for details.
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_viewer extends midcom_baseclasses_components_request
{
    function net_nemein_registrations_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {
        // Welcome page
        $this->_request_switch['welcome'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_welcome', 'welcome'),
        );

        // Event registration
        $this->_request_switch['register-success'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_register', 'success'),
            'fixed_args' => Array('register', 'success'),
        );
        $this->_request_switch['register'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_register', 'register'),
            'fixed_args' => Array('register'),
            'variable_args' => 1,
        );

        // registrations: view, edit, (un)approve, delete
        $this->_request_switch['registration-view'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_registration', 'view'),
            'fixed_args' => Array('registration', 'view'),
            'variable_args' => 1,
        );
        $this->_request_switch['registration-edit'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_registration', 'edit'),
            'fixed_args' => Array('registration', 'edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['registration-delete'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_registration', 'delete'),
            'fixed_args' => Array('registration', 'delete'),
            'variable_args' => 1,
        );
        $this->_request_switch['registration-manage'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_registration', 'manage'),
            'fixed_args' => Array('registration', 'manage'),
            'variable_args' => 1,
        );

        // event management
        $this->_request_switch['event-view'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_event', 'view'),
            'fixed_args' => Array('event', 'view'),
            'variable_args' => 1,
        );
        $this->_request_switch['event-list_registrations'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_event', 'list_registrations'),
            'fixed_args' => Array('event', 'list_registrations'),
            'variable_args' => 1,
        );
        $this->_request_switch['event-edit'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_event', 'edit'),
            'fixed_args' => Array('event', 'edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['event-open'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_event', 'open'),
            'fixed_args' => Array('event', 'open'),
            'variable_args' => 1,
        );
        $this->_request_switch['event-close'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_event', 'close'),
            'fixed_args' => Array('event', 'close'),
            'variable_args' => 1,
        );
        $this->_request_switch['event-delete'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_event', 'delete'),
            'fixed_args' => Array('event', 'delete'),
            'variable_args' => 1,
        );
        $this->_request_switch['event-export_csv'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_event', 'export_csv'),
            'fixed_args' => Array('event', 'export', 'csv'),
            'variable_args' => 2,
        );

        // General Events management (listing, creation), everything not directly related
        // to a single event instance
        $this->_request_switch['events-list_all'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_events', 'list_all'),
            'fixed_args' => Array('events', 'list_all'),
        );
        $this->_request_switch['events-create'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_events', 'create'),
            'fixed_args' => Array('events', 'create'),
        );


        $this->_request_switch['compose_test'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_compose', 'compose'),
            'fixed_args' => Array('compose', 'test'),
            'variable_args' => 2, // registration guid/output mode
        );
        $this->_request_switch['show-invoice'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_compose', 'compose'),
            'fixed_args' => Array('invoice'),
            'variable_args' => 1, // registration guid
        );


        // Administrative stuff
        $this->_request_switch['admin-rootevent'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_admin', 'rootevent'),
            'fixed_args' => Array('admin', 'rootevent'),
        );


    }

    /**
     * This generic handler function prepares the request data required for handling the request.
     * The data loaded here (as outlined in the component interface) encompasses the configured
     * root event and the schema database. If any of these elements cannot be loaded, a 500 error
     * is triggered.
     */
    function _on_handle($handler_id, $args)
    {
        // Pre-initialize the request data so that any class startup will succeed
        $this->_request_data['root_event'] = null;
        $this->_request_data['schemadb'] = null;

        //echo "DEBUG: _on_handle: 1180179900 comes " . date('Y-m-d H:i:s T (Z)', 1180179900) . ") (TZ: " . getenv('TZ') . ")<br>\n";

        $this->_load_root_event($handler_id);
        //echo "DEBUG: _on_handle (after load root event): 1180179900 comes " . date('Y-m-d H:i:s T (Z)', 1180179900) . ") (TZ: " . getenv('TZ') . ")<br>\n";
        $this->_load_schemadb();
        //echo "DEBUG: _on_handle (after schemadb): 1180179900 comes " . date('Y-m-d H:i:s T (Z)', 1180179900) . ") (TZ: " . getenv('TZ') . ")<br>\n";

        return true;
    }

    /**
     * Tries to load the Systemwide root event.
     *
     * This will trigger 500 errors in case the root event cannot be loaded. If you have sufficient
     * privileges to fix this error, the system will redirect you to the corresponding handler.
     *
     * If this is called for the admin-rootevent handler, any failure to load the root event
     * is silently ignored.
     *
     * @param $handler_id The handler ID to load the root event for.
     */
    function _load_root_event($handler_id)
    {
        $guid = $this->_config->get('root_event_guid');
        if (! $guid)
        {
            if ($handler_id == 'admin-rootevent')
            {
                $this->_request_data['root_event'] = null;
                return;
            }

            // Component has no configured root event (this will check permissions)
            $_MIDCOM->relocate('admin/rootevent.html');
            // This will exit.
        }

        $event = new net_nemein_registrations_event($guid);
        if (! $event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the root event '{$guid}', last Midgard error was: " . mgd_errstr());
            // This will exit.
        }
        $this->_request_data['root_event'] = $event;
    }

    /**
     * Tries to load the schema database. It does basic verification of the schemadb along these
     * guidelines:
     *
     * 1. At least three schemas must be present (registrar, event and additional questions)
     * 2. The presence of the schemas defined for registrars and events is verified
     */
    function _load_schemadb()
    {
        $src = $this->_config->get('schemadb');
        $schemadb = midcom_helper_datamanager2_schema::load_database($src);
        if (count($schemadb) < 3)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the schema db from '{$src}': Less then three schemas present");
            // This will exit.
        }

        $name = $this->_config->get('registrar_schema');
        if (! array_key_exists($name, $schemadb))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the schema db from '{$src}': The registrar schema '{$name}' was not found.");
            // This will exit.
        }

        $name = $this->_config->get('event_schema');
        if (! array_key_exists($name, $schemadb))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the schema db from '{$src}': The event schema '{$name}' was not found.");
            // This will exit.
        }
        $this->_request_data['schemadb'] = $schemadb;
    }

}

?>