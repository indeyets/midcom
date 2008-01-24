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
 * This class encapsulates an registration record.
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
     * The default constructor will create an empty object. Optionally, you can pass
     * an object ID or GUID to the object which will then initialize the object with
     * the corresponding DB instance.
     *
     * It will bind the instance to the current request data to access configuration
     * data.
     *
     * @param mixed $id A valid object ID or GUID, omit for an empty object.
     */
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function _do_bindings()
    {
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
        // CAVEAT: This is likely to only work correctly when registrations is in fact in charge of the request 
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
     * Helper method to expand the old keywords system
     *
     * The system is still usefull for mail subjects etc
     * but mail bodies *should* be fully composed at the style
     * engine event though *for now* any remaining supported
     * keywords will get expanded at mail send phase
     */
    function expand_keywords($text)
    {
        $this->populate_compose_data($this->_request_data);
        $event =& $this->_request_data['event'];
        $registrar_dm =& $this->_request_data['registrar_dm'];
        $registrar_data = $registrar_dm->get_content_csv();
        $registrar_all = net_nemein_registrations_event::dm_array_to_string($registrar_dm);

        $registration_dm =& $this->_request_data['registration_dm'];
        $registration_data = $registration_dm->get_content_csv();
        $registration_all = net_nemein_registrations_event::dm_array_to_string($registration_dm);

        if (!isset($this->_request_data['reject_reason']))
        {
            $this->_request_data['reject_reason'] = $this->_l10n->get('no reason given');
        }

        //syntax: _REGISTRAR_arraykey_ bzw. REGISTRATION
        $search = Array
        (
            '/__REGEVENT_([^ \.>"-]*?)__/e',
            '/__REGISTRAR__/', /* Order important here ! */
            '/__REGISTRAR_([^"]*?)__/e',
            '/__REGISTRATION__/', /* Order important here ! */
            '/__REGISTRATION_([^_"]*?)__/e',
            '/__URL__/',
            '/__REASON__/',
        );
        $replace = Array
        (
            '$event->\1',
            $registrar_all,
            '$registrar_data["\1"]',
            $registration_all,
            '$registration_data["\1"]',
            $this->_request_data['registration_url'],
            $this->_request_data['reject_reason'],
        );
        return preg_replace($search, $replace, $text);
    }

    /**
     * Helper to populate members needed in message composition to the given array
     */
    function populate_compose_data(&$request_data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        if (   isset($request_data['invoice_url'])
            && $request_data['invoice_url'] === "{$prefix}invoice/{$this->guid}/")
        {
            /**
             * Chances are extremely good that we have already done these for this registration
             * saves us the very expensive DM loads
             */
            return;
        }
        $request_data['registration'] =& $this;
        $request_data['event'] = $this->get_event();
        $request_data['registrar'] = $this->get_registrar();
        $request_data['event_dm'] =& $request_data['event']->get_datamanager();
        $request_data['registration_dm'] =& $request_data['registration']->get_datamanager();
        $request_data['registrar_dm'] =& $request_data['registrar']->get_datamanager();
        $request_data['event_url'] = "{$prefix}event/view/{$request_data['event']->guid}/";
        $request_data['registration_url'] = "{$prefix}registration/view/{$this->guid}/";
        $request_data['invoice_url'] = "{$prefix}invoice/{$this->guid}/";
    }

    /**
     * Helper to allow us to use the full power of the style engine to compose the
     * emails sent out, composes all the mail bodies in one go, we trade a bit of memory
     * (sizes of the bodies) for speed (no need to create multiple contexts or do dynamic_loads etc)
     *
     * 
     *
     * @return array of strings
     */
    function compose_mail_bodies()
    {
        $composed_bodies = array
        (
            'accept_text' => '',
            'accept_html' => '',
            'reject_text' => '',
            'reject_html' => '',
        );
        // This is slightly hackish, we generate a new context to effectively do many DLs as one
        $context = $_MIDCOM->_create_context();
        $copy_context_data = array
        (
            MIDCOM_CONTEXT_ROOTTOPIC,
            MIDCOM_CONTEXT_CONTENTTOPIC,
            MIDCOM_CONTEXT_COMPONENT,
            MIDCOM_CONTEXT_ANCHORPREFIX,
            MIDCOM_CONTEXT_URI,
            /* we can't copy this with set_context_data
            MIDCOM_CONTEXT_CUSTOMDATA,
            */
        );
        foreach ($copy_context_data as $key)
        {
            $_MIDCOM->_set_context_data($_MIDCOM->get_context_data($key), $context, $key);
        }
        // Separate copy for request_data (the only thing we really need to copy from MIDCOM_CONTEXT_CUSTOMDATA)
        $_MIDCOM->set_custom_context_data('request_data', $this->_request_data, $context);
        /**
         * Populate certain values to the request_data of the new context
         *
         * In fact due to the way all the references pan out it's likely 
         * that these will get referred all the way to the current contexts' 
         * unless we do an explicit clone() above, current way should be faster
         * and use less memory but there is a slight possibility of messing up the
         * request_data if one is not carefull
         */
        $new_request_data =& $_MIDCOM->get_custom_context_data($context, 'request_data');
        $this->populate_compose_data($new_request_data);

        // Start invoking...
        $_MIDCOM->style->enter_context($context);
        foreach ($composed_bodies as $key => $dummy)
        {
            ob_start();
            midcom_show_style("compose-{$key}-mail");
            $composed_bodies[$key] = trim(ob_get_contents());
            ob_end_clean();
        }
        $_MIDCOM->style->leave_context();
        return $composed_bodies;
    }

    /**
     * Retrive the registrar object associated with this registration.
     *
     * @return net_nemein_registrations_registrar The registrar.
     */
    function get_registrar()
    {
        return new net_nemein_registrations_registrar($this->uid);
    }

    /**
     * Retrive the event object associated with this registration.
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