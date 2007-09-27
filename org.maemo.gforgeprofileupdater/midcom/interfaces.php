<?php
/**
 * @package org.maemo.gforgeprofileupdater 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for org.maemo.gforgeprofileupdater
 * 
 * @package org.maemo.gforgeprofileupdater
 */
class org_maemo_gforgeprofileupdater_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function org_maemo_gforgeprofileupdater_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'org.maemo.gforgeprofileupdater';
        $this->_purecode = true;
        $this->_autoload_files = Array
        (
            'main.php',
        );

        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }

    /**
     * Make sure we have all we need
     */
    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        @include_once('SOAP/Client.php');
        if (!class_exists('SOAP_Client'))
        {
            debug_add('Could not load PEAR SOAP client, aborting');
            debug_pop();
            return false;
        }
        $_MIDCOM->componentloader->load_graceful('org.openpsa.contacts');
        if (!class_exists('org_openpsa_contacts_person'))
        {
            debug_add('Could not load org.openpsa.contacts, aborting');
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }

    /**
     * Passes the object to the main class opearation specific handler.
     */
    function _on_watched_operation($operation, &$object)
    {
        $handler = new org_maemo_gforgeprofileupdater();
        if (   !isset($handler->_config)
            || !is_object($handler->_config))
        {
            // cannot check for state, return silently
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('$this->_config is not set/object, can\'t read state', MIDCOM_LOG_ERROR);
            debug_pop();
            return true;
        }
        if (!$handler->_config->get('active'))
        {
            // Return with success if we're not active
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Sync not active, returning early');
            debug_pop();
            return true;
        }

        // Normalize object to org_openpsa_contacts_person
        if (is_a($object, 'org_openpsa_contacts_person'))
        {
            // PHP5-TODO: Copy by value
            $person = $object;
        }
        else
        {
            $person = new org_openpsa_contacts_person($object->id);
        }
        if (   !$person
            || !$person->guid)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not cast object to org_openpsa_contacts_person', MIDCOM_LOG_ERROR);
            debug_print_r('object:', $object);
            debug_print_r('person:', $person);
            debug_pop();
            return false;
        }

        switch ($operation)
        {
            /* We do not handle creates in this end
            case MIDCOM_OPERATION_DBA_CREATE:
                return $handler->created($object);
                break;
            */
            case MIDCOM_OPERATION_DBA_UPDATE:
                return $handler->updated($person);
                break;
            case MIDCOM_OPERATION_DBA_DELETE:
                return $handler->deleted($person);
                break;
            default:
                return false;
        }
    }

}
?>
