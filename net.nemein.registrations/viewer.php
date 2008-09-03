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
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
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
            'handler' => Array('net_nemein_registrations_handler_export', 'csv'),
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
        $this->_request_switch['admin-register'] = Array
        (
            'handler' => Array('net_nemein_registrations_handler_register', 'register'),
            'fixed_args' => Array('admin', 'register'),
            'variable_args' => 1,
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
        $this->_request_data['content_topic'] = null;
        $this->_request_data['schemadb'] = null;

        //echo "DEBUG: _on_handle: 1180179900 comes " . date('Y-m-d H:i:s T (Z)', 1180179900) . ") (TZ: " . getenv('TZ') . ")<br>\n";

        $this->_load_content_topic($handler_id);
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
    function _load_content_topic($handler_id)
    {
        $guid = $this->_config->get('content_topic_guid');
        if (! $guid)
        {
            // Fall back to listing events from current topic
            $this->_request_data['content_topic'] = $this->_topic;
            return;
        }

        $topic = new midcom_db_topic($guid);
        if (   !$topic
            || !$topic->guid)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to load the event topic '{$guid}', last Midgard error was: " . mgd_errstr());
            // This will exit.
        }
        $this->_request_data['content_topic'] = $topic;
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

    function load_callback_class($classname)
    {
        if (class_exists($classname))
        {
            return true;
        }
        // TODO: Check for component of the same name...
        $include_path = 'midcom/lib/' . str_replace('_', '/', $classname) . '.php';
        include_once($include_path);
        if (class_exists($classname))
        {
            // TODO: log PHP error if possible
            return true;
        }
        return false;
    }

    function create_merged_schema(&$event, &$registrar)
    {
        // First, extract the base schemas as copies. We add the additional questions to the
        // bottom of the field list.
        $registrar_schema = $this->_request_data['schemadb'][$this->_config->get('registrar_schema')];
        $event_dm =& $event->get_datamanager();
        // This must be copy-by-value or we will pollute the registrar schema, so use clone() if available
        if (version_compare(phpversion(), '5.0.0', '<'))
        {
            $merged_schema = $registrar_schema;
        }
        else
        {
            $merged_schema = clone($registrar_schema);
        }
        
        if (count($event_dm->types['additional_questions']->selection) > 0)
        {
            $registration_schema = $this->_request_data['schemadb'][$event_dm->types['additional_questions']->selection[0]];
        }
        else
        {
            $registration_schema = $this->_request_data['schemadb']['aq-default'];
        }

        if (   ! $merged_schema
            || ! $registration_schema)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Could not load registrar or registration schema database.');
            // This will exit.
        }

        if (   $registrar
            && !$_MIDCOM->auth->can_do('midgard:update', $registrar))
        {
            foreach($merged_schema->field_order as $name)
            {
                $merged_schema->fields[$name]['readonly'] = true;
            }
        }

        foreach ($registration_schema->field_order as $name)
        {
            if (in_array($name, $merged_schema->field_order))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Duplicate field name '{$name}' found in both registrar and registration schema, cannot compute merged set. Aborting.");
                // This will exit.
            }
            $merged_schema->append_field($name, $registration_schema->fields[$name]);
        }

        $preferred_order = $this->_config->get('merged_schema_field_order');
        // Add any fields in schema missing from the preferred_order array
        foreach ($merged_schema->field_order as $fieldname)
        {
            if (in_array($fieldname, $preferred_order))
            {
                // Present, do nothing
                continue;
            }
            $preferred_order[] = $fieldname;
        }
        // Verify that all fields in preferred_order are actually in the schema
        foreach($preferred_order as $k => $fieldname)
        {
            if (isset($merged_schema->fields[$fieldname]))
            {
                // Present, do nothing
                continue;
            }
            unset($preferred_order[$k]);
        }
        //  array merge will make sure numeric keys are properly continous
        $merged_schema->field_order = array_merge($preferred_order);
        
        return $merged_schema;
    }
}

?>