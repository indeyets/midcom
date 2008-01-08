<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: group_handler.php,v 1.35 2006/07/06 15:47:50 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.contacts group handler and viewer class.
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_group_handler extends midcom_baseclasses_core_object
{
    var $_datamanagers;
    var $_request_data;

    /**
     * The node toolbar for the current request context. Not available during the can_handle
     * phase.
     *
     * @var midcom_helper_toolbar
     * @see midcom_services_toolbars
     */
    var $_node_toolbar = null;

    /**
     * The view toolbar for the current request context. Not available during the can_handle
     * phase.
     *
     * @var midcom_helper_toolbar
     * @see midcom_services_toolbars
     */
    var $_view_toolbar = null;

    function org_openpsa_contacts_group_handler(&$datamanagers, &$request_data)
    {
        $this->_datamanagers = &$datamanagers;
        $this->_request_data = &$request_data;

        parent::midcom_baseclasses_core_object();
    }

    function _load($identifier, $initialize_datamanager = true)
    {
        $group = new org_openpsa_contacts_group($identifier);

        //$parent = $group->get_parent_guid_uncached();
        //$parent = new org_openpsa_contacts_group($parent);
        //die("can edit parent: ".$parent->can_do('midgard:update').", can edit group: ".$group->can_do('midgard:update'));

        if (!$group)
        {
            return false;
        }

        if ($initialize_datamanager)
        {
            // Load the group to datamanager
            if (!$this->_datamanagers['group']->init($group))
            {
                return false;
            }
        }

        $_MIDCOM->set_pagetitle($group->official);

        return $group;
    }

    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a group
        $result = array (
            "success" => false,
            "storage" => null,
        );

        $group = new org_openpsa_contacts_group();

        $group->owner = 0;
        if ($this->_request_data['parent_group'])
        {
            $group->owner = (int) $this->_request_data['parent_group']->id;
        }
        else
        {
            $group->owner = (int) $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group']->id;
        }
        $group->name = time();
        $stat = $group->create();
        if ($stat)
        {
            $this->_request_data['group'] = new org_openpsa_contacts_group($group->id);
            //Debugging
            $result["storage"] = & $this->_request_data['group'];
            $result["success"] = true;
            return $result;
        }
        debug_add("Object's create() method returned {$group->errstr} ({$group->errno})");
        return null;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        //$GLOBALS['midcom_debugger']->setLogLevel(MIDCOM_LOG_DEBUG);

        $this->_request_data['parent_group'] = false;
        if (count($args) > 0)
        {
            // Get the parent organization
            $this->_request_data['parent_group'] = $this->_load($args[0]);

            if (!$this->_request_data['parent_group'])
            {
                return false;
            }

            $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['parent_group']);

            // Set the default type to "department"
            org_openpsa_helpers_schema_modifier(&$this->_datamanagers['group'], 'object_type', 'default', ORG_OPENPSA_OBTYPE_DEPARTMENT, 'newgroup', false);
        }
        else
        {
            // This is a root level organization, require creation permissions under the component root group
            $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_contacts_group');
        }

        if (!$this->_datamanagers['group']->init_creation_mode("newgroup",$this,"_creation_dm_callback"))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'newgroup'.");
            // This will exit
        }

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get("new organization"));

        switch ($this->_datamanagers['group']->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

                break;

            case MIDCOM_DATAMGR_EDITING:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['group']->parameter("midcom.helper.datamanager","layout","default");
                break;

            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['group']->parameter("midcom.helper.datamanager","layout","default");

                // Index the organization
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanagers['group']);

                // Relocate to group view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate($prefix."group/".$this->_request_data['group']->guid."/");
                break;

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                $_MIDCOM->relocate('');
                // This will exit

            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;

            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanager failed to process the request, see the Debug Log for details';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;

            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;

        }

        debug_pop();
        return true;

    }

    function _show_new($handler_id, &$data)
    {
        $this->_request_data['group_dm'] = $this->_datamanagers['group'];
        midcom_show_style("show-group-new");
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_view($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        // Get the requested group object
        $this->_request_data['group'] = $this->_load($args[0]);
        if (!$this->_request_data['group'])
        {
            return false;
        }

        // Add toolbar items
        if (count($args) == 1)
        {
            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "group/{$this->_request_data['group']->guid}/edit/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("edit"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['group']),
                )
            );

            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "group/{$this->_request_data['group']->guid}/notifications.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("notification settings"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_help-agent.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['group']),
                )
            );

            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "group/{$this->_request_data['group']->guid}/privileges.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("permissions"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png', // TODO: Get better icon
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:privileges', $this->_request_data['group']),
                )
            );

            if (   $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_contacts_person')
                && $_MIDCOM->auth->can_do('midgard:create', $this->_request_data['group']))
            {
                $allow_person_create = true;
            }
            else
            {
                $allow_person_create = false;
            }

            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "person/new/{$this->_request_data['group']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create person'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
                    MIDCOM_TOOLBAR_ENABLED => $allow_person_create,
                )
            );

            $this->_view_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "group/new/{$this->_request_data['group']->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create suborganization'),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_home.png',
                    MIDCOM_TOOLBAR_ENABLED => $this->_request_data['group']->can_do('midgard:update'),
                )
            );

            $cal_node = midcom_helper_find_node_by_component('org.openpsa.calendar');
            if (!empty($cal_node))
            {
                //TODO: Check for privileges somehow
                $this->_node_toolbar->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "#",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create event'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new-event.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                        MIDCOM_TOOLBAR_OPTIONS  => Array(
                            'rel' => 'directlink',
                            'onClick' => org_openpsa_calendar_interface::calendar_newevent_js($cal_node, false, $this->_request_data['group']->guid),
                        ),
                    )
                );
            }

            $invoices_node = midcom_helper_find_node_by_component('org.openpsa.invoices');
            if (!empty($invoices_node))
            {
                //TODO: Check for privileges somehow
                $this->_view_toolbar->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => $invoices_node[MIDCOM_NAV_FULLURL] . "list/customer/all/{$this->_request_data['group']->guid}",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('customers invoices'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-open.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    )
                );
            }
        }

        $GLOBALS['midcom_component_data']['org.openpsa.contacts']['active_leaf'] = $this->_request_data['group']->id;

        $_MIDCOM->bind_view_to_object($this->_request_data['group']);
        return true;
    }

    function _show_view($handler_id, &$data)
    {
        $this->_request_data['group_dm'] = $this->_datamanagers['group'];

        if ($this->_request_data['group']->owner != $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group']->id)
        {
            $this->_request_data['parent_group'] = $this->_load($this->_request_data['group']->owner, false);
        }
        else
        {
            $this->_request_data['parent_group'] = false;
        }

        midcom_show_style("show-group");
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        // Check if we get the group
        if (!$this->_handler_view($handler_id, $args, &$data))
        {
            return false;
        }

        // Check if the action is a valid one
        $this->_request_data['action'] = $args[1];
        switch ($args[1])
        {
            case "notifications":
                debug_add("Entering notifications handler");
                $this->_request_data['group']->require_do('midgard:update');

                $this->_datamanagers['notifications']->init($this->_request_data['group']);

                switch ($this->_datamanagers['notifications']->process_form())
                {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "notifications";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

                        debug_pop();
                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        $this->_view = "default";
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "group/" . $this->_request_data["group"]->guid . "/");
                        // This will exit()

                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "group/" . $this->_request_data["group"]->guid . "/");
                        // This will exit()

                    case MIDCOM_DATAMGR_FAILED:
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        debug_pop();
                        return false;
                }

                debug_pop();
                return true;

            case "privileges":
                debug_add("Entering privilege handler");
                $_MIDCOM->auth->require_do('midgard:privileges', $this->_request_data['group']);
                $group_object = $_MIDCOM->auth->get_group("group:{$this->_request_data['group']->guid}");

                // Load project classes
                $_MIDCOM->componentloader->load('org.openpsa.projects');
                // Load campaign classes
                $_MIDCOM->componentloader->load('org.openpsa.directmarketing');

                // Get the calendar root event
                $_MIDCOM->componentloader->load('org.openpsa.calendar');
                org_openpsa_helpers_schema_modifier(&$this->_datamanagers['acl'], 'calendar', 'privilege_object', $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']);

                // Set the contacts root group into ACL
                /* The persons are not necessarily under the root group...
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_creation']['privilege_object'] = $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group'];
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_editing']['privilege_object'] = $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group'];
                */
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_creation']['privilege_object'] =  $group_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_editing']['privilege_object'] =  $group_object->get_storage();

                // Set the group as ACL assignee
                /* Skip assignee to make it 'SELF'
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_creation']['privilege_assignee'] = $group_object->id;
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_editing']['privilege_assignee'] = $group_object->id;
                */


                $this->_datamanagers['acl']->_layoutdb['default']['fields']['calendar']['privilege_assignee'] = $group_object->id;
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['organization_creation']['privilege_object'] = $group_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['organization_editing']['privilege_object'] = $group_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['projects']['privilege_object'] = $group_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['invoices_creation']['privilege_object'] = $group_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['invoices_editing']['privilege_object'] = $group_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['campaigns_creation']['privilege_object'] = $group_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['campaigns_editing']['privilege_object'] = $group_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['salesproject_creation']['privilege_object'] = $group_object->get_storage();

                $this->_datamanagers['acl']->init($this->_request_data['group']);

                switch ($this->_datamanagers['acl']->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "privileges";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

                        debug_pop();
                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        $this->_view = "default";
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "group/" . $this->_request_data["group"]->guid . "/");
                        // This will exit()

                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "group/" . $this->_request_data["group"]->guid . "/");
                        // This will exit()

                    case MIDCOM_DATAMGR_FAILED:
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        debug_pop();
                        return false;
                }

                debug_pop();
                return true;

            case "update_member_title":
                // Ajax save handler
                $update_succeeded = false;
                $errstr = NULL;
                if (   array_key_exists('member_title',$_POST)
                    && is_array($_POST['member_title']))
                {
                    foreach ($_POST['member_title'] as $id => $title)
                    {
                        $member = new midcom_baseclasses_database_member($id);
                        if ($member)
                        {
                            $_MIDCOM->auth->require_do('midgard:update', $member);
                            $member->extra = $title;
                            $update_succeeded = $member->update();
                            $errstr = mgd_errstr();
                        }
                    }
                }
                $ajax=new org_openpsa_helpers_ajax();
                //This will exit.
                $ajax->simpleReply($update_succeeded, $errstr);

            case "members":
                // Group person listing, always work even if there are none
                $this->_view = "area_group_members";
                return true;

            case "subgroups":
                // Group person listing, always work even if there are none
                $this->_view = "area_group_subgroups";
                return true;

            case "edit":
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['group']);

                // Make the members editable
                $this->_datamanagers['group']->_layoutdb['default']['fields']['members']['hidden'] = false;

                switch ($this->_datamanagers['group']->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "default";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        // Index the organization
                        $indexer =& $_MIDCOM->get_service('indexer');
                        $indexer->index($this->_datamanagers['group']);

                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "group/" . $this->_request_data["group"]->guid());
                        // This will exit()

                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "group/" . $this->_request_data["group"]->guid());
                        // This will exit()

                    case MIDCOM_DATAMGR_FAILED:
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        return false;
                }

                return true;
            default:
                return false;
        }
    }


    function _show_action($handler_id, &$data)
    {
        if ($this->_view == "area_group_members")
        {
            // This is most likely a dynamic_load
            $qb = new org_openpsa_qbpager('midcom_baseclasses_database_member', 'group_members');
            $qb->add_constraint('gid', '=', $this->_request_data['group']->id);
            $qb->results_per_page = 10;
            $results = $qb->execute();
            $this->_request_data['members_qb'] = &$qb;

            midcom_show_style("show-group-persons-header");
            if (count($results) > 0)
            {
                foreach ($results as $member)
                {
                    $this->_request_data['member'] = $member;

                    if ($member->extra == "")
                    {
                        $member->extra = $this->_request_data['l10n']->get('<title>');
                    }
                    $this->_request_data['member_title'] = $member->extra;

                    $this->_request_data['person'] = new org_openpsa_contacts_person($member->uid);
                    midcom_show_style("show-group-persons-item");
                }
            }
            else
            {
                midcom_show_style("show-group-persons-empty");
            }
            midcom_show_style("show-group-persons-footer");
        }
        elseif ($this->_view == "notifications")
        {
            // Default view, display the selected action
            $this->_request_data['notifications_dm'] = $this->_datamanagers['notifications'];
            midcom_show_style("show-notifications");
        }
        elseif ($this->_view == "privileges")
        {
            // Default view, display the selected action
            $this->_request_data['acl_dm'] = $this->_datamanagers['acl'];
            midcom_show_style("show-privileges");
        }
        elseif ($this->_view == "area_group_subgroups")
        {
            // This is most likely a dynamic_load
            $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_group');
            $qb->add_constraint('owner', '=', $this->_request_data['group']->id);
            $results = $_MIDCOM->dbfactory->exec_query_builder($qb);
            if (count($results) > 0)
            {
                midcom_show_style("show-group-subgroups-header");
                foreach ($results as $subgroup)
                {
                    $this->_request_data['subgroup'] = new org_openpsa_contacts_group($subgroup->id);
                    midcom_show_style("show-group-subgroups-item");
                }
                midcom_show_style("show-group-subgroups-footer");
            }
        }
        else
        {
            // Default view, display the selected action
            $GLOBALS["view"] = $this->_datamanagers['group'];

            if ($this->_request_data['group']->owner != $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group']->id)
            {
                $this->_request_data['parent_group'] = $this->_load($this->_request_data['group']->owner, false);
            }
            else
            {
                $this->_request_data['parent_group'] = false;
            }

            midcom_show_style("show-group-{$data['action']}");
        }
    }

    /**
     * Does a QB query for groups, returns false or number of matched entries
     *
     * Displays style element 'search-groups-empty' only if $displayEmpty is
     * set to true.
     */
    function _search_qb_groups($search, $displayEmpty=false, $displayOutput=true, $limit=false, $offset=false)
    {
        if ($search == NULL)
        {
            return false;
        }

        $qb_org = org_openpsa_contacts_group::new_query_builder();
        //$qb_org = new MidgardQuerybuilder('org_openpsa_organization');
        $qb_org->begin_group('OR');

        // Search using only the fields defined in config
        $org_fields = explode(',', $this->_request_data['config']->get('organization_search_fields'));
        if (   !is_array($org_fields)
            || count($org_fields) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Invalid organization search configuration');
        }

        foreach ($org_fields as $field)
        {
            if (empty($field))
            {
                continue;
            }
            $qb_org->add_constraint($field, 'LIKE', '%'.$search.'%');
        }

        $qb_org->end_group();

        //Skip groups in other sitegroups (sitegroup constraint is no longer dropped ?)
        $qb_org->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $results = $qb_org->execute();
        if (   is_array($results)
            && count($results) > 0)
        {
            midcom_show_style('search-groups-header');
            foreach($results as $group)
            {
                //TODO: When we actually use MgdSchema objects just use $group
                //$GLOBALS['view_group'] = new org_openpsa_contacts_group($group->id);
                $GLOBALS['view_group'] = $group;
                midcom_show_style('search-groups-item');
            }
            midcom_show_style('search-groups-footer');
            return count($results);
        }
        else
        {
            //No group results
            if ($displayEmpty==true)
            {
                midcom_show_style('search-groups-empty');
            }
            return false;
        }
    }

}
?>