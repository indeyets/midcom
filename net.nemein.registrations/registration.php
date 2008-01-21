<?php
/**
 * @package net.nemein.registrations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Event registration system: Registration class
 *
 * This class encaspulates an registration record.
 *
 * Approval and rejection of registrations is done through their event, as its information
 * is readily available there.
 *
 * TODO...
 *
 * @package net.nemein.registrations
 */

class net_nemein_registrations_registration_dba extends __net_nemein_registrations_registration_dba
{
    /**
     * Request data information
     *
     * @access private
     */
    var $_request_data;

    /**
     * Request data information
     *
     * @access private
     */
    var $_config;

    /**
     * Request data information
     *
     * @access private
     */
    var $_topic;

    /**
     * Request data information
     *
     * @access private
     */
    var $_l10n;

    /**
     * Request data information
     *
     * @access private
     */
    var $_l10n_midcom;

    /**
     * References to uid and eid respectively (NOTE: cannot be used as properties with QB!)
     */
    var $person = false;
    var $event = false;

    /**
     * The default constructor will create an empty object. Optionally, you can pass
     * an object ID or GUID to the object which will then initialize the object with
     * the corresponding DB instance.
     *
     * It will bind the instance to the current request data to access configuration
     * data.
     *
     * @param mixed $id A valid object ID or GUID, omit for an empty object.
     */
    function net_nemein_registrations_registration_dba($id = null)
    {
        return parent::__net_nemein_registrations_registration_dba($id);
    }

    function _do_bindings()
    {
        // Reference the "properly" named properties to mgdschema properties
        $this->person =& $this->uid;
        $this->event =& $this->eid;
        // Bind request_data
        $this->_bind_to_request_data();
    }

    function _on_loaded()
    {
        $this->_do_bindings();
        return true;
    }

    function _on_created()
    {
        $this->_do_bindings();
        return true;
    }

    /**
     * Overwrite the query builder getter with a version retrieving the right type.
     * We need a better solution here in DBA core actually, but it will be difficult to
     * do this as we cannot determine the current class in a polymorphic environment without
     * having a this (this call is static).
     */
    function new_query_builder()
    {
        return $_MIDCOM->dbfactory->new_query_builder(__CLASS__);
    }

    /**
     * Binds the object to the current request data. This populates the members
     * _request_data, _config, _topic, _l10n and _l10n_midcom accordingly.
     */
    function _bind_to_request_data()
    {
        $this->_request_data =& $_MIDCOM->get_custom_context_data('request_data');
        $this->_config =& $this->_request_data['config'];
        $this->_topic =& $this->_request_data['topic'];
        $this->_l10n =& $this->_request_data['l10n'];
        $this->_l10n_midcom =& $this->_request_data['l10n_midcom'];
    }

    /**
     * Returns a DM2 datamanager instance for this object.
     *
     * @return midcom_helper_datamanager2_datamanager The DM2 reference.
     */
    function & get_datamanager()
    {
        $dm = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);
        $dm->autoset_storage($this);
        return $dm;
    }

    /**
     * Helper function, which creates simple controller for the current registrar.
     *
     * @return midcom_helper_datamanager2_controller_simple A reference to the new controller
     */
    function & create_simple_controller($schema = null)
    {
        /*
        echo "DEBUG: \$this->_request_data['schemadb']<pre>\n";
        print_r($this->_request_data['schemadb']);
        echo "</pre>\n";
        die('Debugging');
        */
        $controller = midcom_helper_datamanager2_controller::create('simple');
        $controller->schemadb = $this->_request_data['schemadb'];
        $controller->set_storage($this, $schema);
        $controller->initialize();

        return $controller;
    }

    /**
     * Retrive the registrar object accociated with this registration.
     *
     * @return net_nemein_registrations_registrar The registrar.
     */
    function get_registrar()
    {
        return new net_nemein_registrations_registrar($this->uid);
    }

    /**
     * Retrive the event object accociated with this registration.
     *
     * @return net_nemein_registrations_event The event.
     */
    function get_event()
    {
        return new net_nemein_registrations_event($this->eid);
    }

    /**
     * Checks, if the event is approved.
     *
     * @return bool Indicating approval state
     */
    function is_approved()
    {
        return (bool) $this->get_parameter('net.nemein.registrations', 'approved');
    }

}

?>