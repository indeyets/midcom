<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: event.php 6056 2007-05-25 13:52:04Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
require_once(MIDCOM_ROOT.'/midcom/baseclasses/components/handler/dataexport.php');
/**
 * Event registration management handler
 *
 * @package net.nemein.registrations
 */
class net_nemein_registrations_handler_export extends midcom_baseclasses_components_handler_dataexport
{
    /**
     * The events to register for
     *
     * @var array
     * @access private
     */
    var $_event = null;

    /**
     * The schema database (taken from the request data area)
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The schema database used for the nullstorage controller. It consists of the merged registrar
     * and add registration schemas in a single schema named 'merged'. No further schemas will be
     * part of this database.
     *
     * @var Array
     * @access private
     */
    var $_nullstorage_schemadb = Array();

    var $_datamanager_registration = false;
    var $_datamanager_registrar = false;
    var $_datamanager_event = false;

    function net_nemein_registrations_handler_export()
    {
        parent::midcom_baseclasses_components_handler_dataexport();
    }

    function _on_initialize()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    function _load_schemadb()
    {
        $this->_schema = $this->_config->get('registrar_schema');
        return $this->_schemadb;
    }

    function _load_schemadb_real()
    {
        // A bit hackish but we need the full instance...
        $interface =& $_MIDCOM->componentloader->get_interface_class('net.nemein.registrations');
        $viewer =& $interface->_context_data[$_MIDCOM->_currentcontext]['handler'];
        $x = false;
        $this->_nullstorage_schemadb['merged'] = $viewer->create_merged_schema($this->_event, $x);
        $this->_schema = 'merged';

        // We must do this after the schema merging or things get funny...
        $this->_datamanager_registrar = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        $this->_datamanager_registrar->set_schema($this->_config->get('registrar_schema'));
        $this->_datamanager_registration = new midcom_helper_datamanager2_datamanager($this->_schemadb);
        $this->_datamanager_event =& $this->_event->get_datamanager();
        if (count($this->_datamanager_event->types['additional_questions']->selection) > 0)
        {
            $this->_datamanager_registration->set_schema($this->_datamanager_event->types['additional_questions']->selection[0]);
        }
        else
        {
            $this->_datamanager_registration->set_schema('aq-default');
        }
        return $this->_nullstorage_schemadb;
    }
    
    function _load_data($handler_id, $args, &$data)
    {
        // Validate args.
        $this->_event = new net_nemein_registrations_event($args[0]);
        if (! $this->_event)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The event {$args[0]} could not be found.");
            // This will exit.
        }
        $this->_load_datamanager($this->_load_schemadb_real());
        return $this->_event->get_registrations();
    }

    function set_dm_storage(&$registration)
    {
        $this->_datamanager_registration->set_storage($registration);
        $registrar = $registration->get_registrar();
        $this->_datamanager_registrar->set_storage($registrar);
        $merged_values = array();
        foreach ($this->_datamanager_registrar->types as $name => $type)
        {
            $merged_values[$name] = $type->convert_to_storage();
        }
        unset($name, $type);
        foreach ($this->_datamanager_registration->types as $name => $type)
        {
            $merged_values[$name] = $type->convert_to_storage();
        }
        $nullstorage = new midcom_helper_datamanager2_storage_null($this->_datamanager->schema, $merged_values);
        return $this->_datamanager->set_storage($nullstorage);
    }
}
?>