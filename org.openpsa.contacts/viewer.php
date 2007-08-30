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

    var $_person_handler = null;
    var $_group_handler = null;
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
    function org_openpsa_contacts_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);

        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();

        $this->_request_data['enable_dbe'] = $this->_config->get('enable_dbe');
        $this->_request_data['config'] =& $this->_config;

        // Load datamanagers for main classes
        $this->_initialize_datamanager('group', $this->_config->get('schemadb_group'));
        $this->_initialize_datamanager('person', $this->_config->get('schemadb_person'));
        $this->_initialize_datamanager('acl', $this->_config->get('schemadb_acl'));
        $this->_initialize_datamanager('notifications', $this->_config->get('schemadb_notifications'));

        $this->_toolbars =& midcom_helper_toolbars::get_instance();

        $this->_group_handler = new org_openpsa_contacts_group_handler(&$this->_datamanagers, &$this->_request_data);
        $this->_request_data['group_handler'] = &$this->_group_handler;
        $this->_person_handler = new org_openpsa_contacts_person_handler(&$this->_datamanagers, &$this->_request_data);

        if (!$this->_is_initialized())
        {
            // Match /
            $this->_request_switch[] = array(
                'handler' => 'notinitialized'
            );
        }
        else
        {
            // Match /duplicates/person
            $this->_request_switch[] = array(
                'fixed_args' => array('duplicates', 'person'),
                'handler' => Array('org_openpsa_contacts_handler_duplicates_person', 'sidebyside'),
            );

            // Match /buddylist/
            $this->_request_switch[] = array(
                'fixed_args' => 'buddylist',
                'handler' => Array('org_openpsa_contacts_handler_buddy_list', 'list'),
            );

            // Match /buddylist/add/<person guid>
            $this->_request_switch[] = array(
                'fixed_args' => Array('buddylist', 'add'),
                'variable_args' => 1,
                'handler' => Array('org_openpsa_contacts_handler_buddy_list', 'add'),
            );

            // Match /buddylist/remove/<person guid>
            $this->_request_switch[] = array(
                'fixed_args' => Array('buddylist', 'remove'),
                'variable_args' => 1,
                'handler' => Array('org_openpsa_contacts_handler_buddy_list', 'remove'),
            );

            // Match /search/<type>
            $this->_request_switch[] = array(
                'fixed_args' => 'search',
                'variable_args' => 1,
                'handler' => 'search_type',
            );
            // Match /search/
            $this->_request_switch[] = array(
                'fixed_args' => 'search',
                'handler' => 'search',
            );
            // Match /group/new/<GUID>
            $this->_request_switch[] = array(
                'fixed_args' => array('group','new'),
                'variable_args' => 1,
                'handler' => array(&$this->_group_handler,'new'),
            );
            // Match /group/<GUID>/<action>
            $this->_request_switch[] = array(
                'fixed_args' => 'group',
                'variable_args' => 2,
                'handler' => array(&$this->_group_handler,'action'),
            );
            // Match /group/new
            $this->_request_switch[] = array(
                'fixed_args' => array('group','new'),
                'handler' => array(&$this->_group_handler,'new'),
            );
            // Match /group/<GUID>
            $this->_request_switch[] = array(
                'fixed_args' => 'group',
                'variable_args' => 1,
                'handler' => array(&$this->_group_handler,'view'),
            );
            // Match /person/new/GroupGUID
            $this->_request_switch[] = array(
                'fixed_args' => array('person','new'),
                'variable_args' => 1,
                'handler' => array(&$this->_person_handler,'person_new'),
            );

            // Match /person/new
            $this->_request_switch[] = array(
                'fixed_args' => array('person','new'),
                'handler' => array(&$this->_person_handler,'person_new'),
            );

            // Match /person/GUID
            $this->_request_switch['person_view'] = array
            (
                'fixed_args' => 'person',
                'variable_args' => 1,
                'handler' => array
                (
                    'org_openpsa_contacts_handler_person_view',
                    'view'
                ),
            );

            // Match /person/edit/GUID
            $this->_request_switch['person_edit'] = array
            (
                'fixed_args' => array
                (
                    'person',
                    'edit',
                ),
                'variable_args' => 1,
                'handler' => array
                (
                    'org_openpsa_contacts_handler_person_admin',
                    'edit'
                ),
            );

            // Match /person/edit/GUID
            $this->_request_switch['person_delete'] = array
            (
                'fixed_args' => array
                (
                    'person',
                    'delete',
                ),
                'variable_args' => 1,
                'handler' => array
                (
                    'org_openpsa_contacts_handler_person_admin',
                    'delete'
                ),
            );

            // Match /person/related/GUID
            $this->_request_switch['person_related'] = array(
                'fixed_args' => array('person', 'related'),
                'variable_args' => 1,
                'handler' => array(&$this->_person_handler,'person'),
            );

            // Match /person/GUID/action
            $this->_request_switch[] = array(
                'fixed_args' => 'person',
                'variable_args' => 2,
                'handler' => array(&$this->_person_handler,'person_action'),
            );


            // Match /debug
            $this->_request_switch[] = array(
                'fixed_args' => 'debug',
                'handler' => 'debug'
            );
            // Match /
            $this->_request_switch['frontpage'] = array
            (
                'handler' => array
                (
                    'org_openpsa_contacts_handler_frontpage',
                    'frontpage'
                ),
            );

            // Match /config/
            $this->_request_switch['config'] = Array
            (
                'handler' => Array('midcom_core_handler_configdm', 'configdm'),
                'schemadb' => 'file:/org/openpsa/contacts/config/schemadb_config.inc',
                'schema' => 'config',
                'fixed_args' => 'config',
            );

            //Add common relatedto request switches
            org_openpsa_relatedto_handler::common_request_switches($this->_request_switch, 'org.openpsa.contacts');
            //If you need any custom switches add them here

        }
        // This component uses Ajax, include the handler javascripts
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajaxutils.js");

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.core/ui-elements.css",
            )
        );
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $_MIDCOM->auth->require_valid_user();
    
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

    function _handler_notinitialized($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        return true;
    }

    function _show_notinitialized($handler_id, &$data)
    {
        midcom_show_style('show-not-initialized');
    }

    function _initialize_datamanager($type, $schemadb_snippet)
    {
        // Load schema database snippet or file
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $schemadb_contents = midcom_get_snippet_content($schemadb_snippet);
        eval("\$schemadb = Array ( {$schemadb_contents} );");

        // Initialize the datamanager with the schema
        $this->_datamanagers[$type] = new midcom_helper_datamanager($schemadb);

        if (!$this->_datamanagers[$type]) {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantinated.");
            // This will exit.
        }
    }



    function _handler_search_type($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        switch ($args[0])
        {
            case 'foaf':
                $_MIDCOM->skip_page_style = true;
                $this->_view = 'foaf';
                return true;
        }
        return false;
    }

    function _show_search_type($handler_id, &$data)
    {
        if ($this->_view == 'foaf')
        {
            $pres = $this->_person_handler->_search_qb_persons($_GET['search'], false, false);
            if ($pres)
            {
                midcom_show_style('foaf-header');
                foreach ($pres as $person)
                {
                    $GLOBALS['view_person'] = $person;
                    midcom_show_style('foaf-person-item');
                }
                midcom_show_style('foaf-footer');
            }
        }
    }

    function _handler_search($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        //We always want to display *something*

        if ($_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_contacts_person'))
        {
            $this->_toolbars->top->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "person/new/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create person'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }
        if ($_MIDCOM->auth->can_do('midgard:create', $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group']))
        {
            $this->_toolbars->top->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'group/new/',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create organization'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }

        return true;
    }

    /**
     * Queries all Contacts objects for $_GET['search']
     *
     * Displays style element 'search-empty' if no results at all
     * can be found
     */
    function _show_search($handler_id, &$data)
    {
        midcom_show_style('search-header');
        if (isset($_GET['search']))
        {
            //Convert asterisks to correct wildcard
            $search = str_replace('*', '%', $_GET['search']);
            $gret = $this->_group_handler->_search_qb_groups($search);
            $pret = $this->_person_handler->_search_qb_persons($search);
            if (   $gret
                || $pret)
            {
                //Some search results got.
            }
            else
            {
                //No results at all (from any of the queries)
                midcom_show_style('search-empty');
            }
        }
        midcom_show_style('search-footer');
    }

    function _handler_debug($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['config'] =& $this->_config;
        return true;
    }

    function _show_debug($handler_id, &$data)
    {
        midcom_show_style("show-debug");
    }


}

?>