<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.32 2006/06/08 14:12:38 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.contacts site interface class.
 *
 * Contact management, address book and user manager
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_viewer extends midcom_baseclasses_components_request
{
    /**
     * The root-level MidgardGroup used by the Contacts
     *
     * @var MidgardGroup
     */
    var $_root_group = null;

    var $_datamanagers = array();

    var $_view = "default";

    var $_toolbars = null;

    //var $_node = null;

    /**
     * Constructor.
     *
     * OpenPSA Contacts handles its URL space following the convention:
     * - First parameter is the object type (person, group, salesproject, list)
     * - Second parameter is the object identifier (GUID, or some special filter like "all")
     * - Third parameter defines current view/action
     * - Additional parameters are defined by the action concerned
     */
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        $this->_request_data['enable_dbe'] = $this->_config->get('enable_dbe');
        $this->_request_data['config'] =& $this->_config;

        $this->_toolbars =& midcom_helper_toolbars::get_instance();

        if (!$this->_is_initialized())
        {
            // Match /
            $this->_request_switch[] = array
        (
                'handler' => 'notinitialized'
            );
        }
        else
        {
            // Match /duplicates/person
            $this->_request_switch[] = array
           (
                'handler' => array('org_openpsa_contacts_handler_duplicates_person', 'sidebyside'),
                'fixed_args' => array('duplicates', 'person'),
            );

            // Match /buddylist/
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_buddy_list', 'list'),
                'fixed_args' => 'buddylist',
            );

            // Match /buddylist/xml
            $this->_request_switch['buddylist_xml'] = array
            (
                'handler' => array('org_openpsa_contacts_handler_buddy_list', 'list'),
                'fixed_args' => array('buddylist', 'xml'),
            );

            // Match /buddylist/add/<person guid>
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_buddy_list', 'add'),
                'fixed_args' => array('buddylist', 'add'),
                'variable_args' => 1,
            );

            // Match /buddylist/remove/<person guid>
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_buddy_list', 'remove'),
                'fixed_args' => array('buddylist', 'remove'),
                'variable_args' => 1,
            );

            // Match /search/<type>
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_search', 'search_type'),
                'fixed_args' => 'search',
                'variable_args' => 1,
            );
            // Match /search/
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_search', 'search'),
                'fixed_args' => 'search',
            );
            // Match /group/new/<GUID>
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_group', 'new'),
                'fixed_args' => array('group','new'),
                'variable_args' => 1,
            );
            // Match /group/<GUID>/<action>
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_group', 'action'),
                'fixed_args' => 'group',
                'variable_args' => 2,
            );
            // Match /group/new
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_group', 'new'),
                'fixed_args' => array('group', 'new'),
            );
            // Match /group/<GUID>
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_group', 'view'),
                'fixed_args' => 'group',
                'variable_args' => 1,
            );
            // Match /person/new/GroupGUID
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_person', 'person_new'),
                'fixed_args' => array('person', 'new'),
                'variable_args' => 1,
            );

            // Match /person/new
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_person', 'person_new'),
                'fixed_args' => array('person','new'),
            );

            // Match /person/GUID
            $this->_request_switch['person_view'] = array
            (
                'handler' => array('org_openpsa_contacts_handler_person_view', 'view'),
                'fixed_args' => 'person',
                'variable_args' => 1,
            );

            // Match /person/edit/GUID
            $this->_request_switch['person_edit'] = array
            (
                'handler' => array('org_openpsa_contacts_handler_person_admin', 'edit'),
                'fixed_args' => array('person', 'edit'),
                'variable_args' => 1,
            );

            // Match /person/edit/GUID
            $this->_request_switch['person_delete'] = array
            (
                'handler' => array('org_openpsa_contacts_handler_person_admin', 'delete'),
                'fixed_args' => array('person', 'delete'),
                'variable_args' => 1,
            );

            // Match /person/related/GUID
            $this->_request_switch['person_related'] = array
            (
                'handler' => array('org_openpsa_contacts_handler_person', 'person'),
                'fixed_args' => array('person', 'related'),
                'variable_args' => 1,
            );

            // Match /person/GUID/action
            $this->_request_switch[] = array
            (
                'handler' => array('org_openpsa_contacts_handler_person', 'person_action'),
                'fixed_args' => 'person',
                'variable_args' => 2,
            );

            // Match /debug
            $this->_request_switch[] = array
            (
            'handler' => 'debug',
                'fixed_args' => 'debug'
            );
            // Match /
            $this->_request_switch['frontpage'] = array
            (
                'handler' => array('org_openpsa_contacts_handler_frontpage', 'frontpage'),
            );

            // Match /config/
            $this->_request_switch['config'] = array
            (
                'handler' => array('midcom_core_handler_configdm', 'configdm'),
                'schemadb' => 'file:/org/openpsa/contacts/config/schemadb_config.inc',
                'schema' => 'config',
                'fixed_args' => 'config',
            );

            //Add common relatedto request switches
            $_MIDCOM->load_library('org.openpsa.relatedto');
            org_openpsa_relatedto_handler::common_request_switches($this->_request_switch, 'org.openpsa.contacts');
            //If you need any custom switches add them here

        }
        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . "/org.openpsa.helpers/ajaxutils.js");

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.openpsa.core/ui-elements.css",
            )
        );
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        if ($handler != 'buddylist_xml')
        {
            $_MIDCOM->auth->require_valid_user();
        }

        // Safety
        if (!class_exists('midcom_helper_datamanager2_schema'))
        {
            $_MIDCOM->load_library('midcom.helper.datamanager2');
        }
        $this->_request_data['schemadb_person'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_person_dm2'));

        return true;
    }

    function _is_initialized()
    {
        $root_group = org_openpsa_contacts_interface::find_root_group($this->_config);
        $this->_request_data['contacts_root_group'] =& $root_group;
        if (!$this->_request_data['contacts_root_group'])
        {
            return false;
        }

        debug_pop();
        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_notinitialized($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_notinitialized($handler_id, &$data)
    {
        midcom_show_style('show-not-initialized');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_debug($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['config'] =& $this->_config;
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_debug($handler_id, &$data)
    {
        midcom_show_style("show-debug");
    }


}

?>