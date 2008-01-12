<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: person_handler.php,v 1.45 2006/07/06 15:47:50 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.contacts person handler and viewer class.
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_person_handler
{
    var $_datamanagers;
    var $_request_data;
    var $_toolbars;

    function org_openpsa_contacts_person_handler(&$datamanagers, &$request_data)
    {
        $this->_datamanagers =& $datamanagers;
        $this->_request_data =& $request_data;
        $this->_toolbars =& midcom_helper_toolbars::get_instance();
    }

    function _load_person($identifier)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $person = new org_openpsa_contacts_person($identifier);

        if (!is_object($person))
        {
            debug_add("Person object {$identifier} is not an object");
            debug_pop();
            return false;
        }

        // Add the DBE settings field
        if ($this->_request_data['enable_dbe'])
        {
            org_openpsa_helpers_schema_modifier(&$this->_datamanagers['person'], 'dbe_service_id', 'description', 'dbe service id');
            org_openpsa_helpers_schema_modifier(&$this->_datamanagers['person'], 'dbe_service_id', 'datatype', 'text');
            org_openpsa_helpers_schema_modifier(&$this->_datamanagers['person'], 'dbe_service_id', 'location', 'config');
            org_openpsa_helpers_schema_modifier(&$this->_datamanagers['person'], 'dbe_service_id', 'config_domain', 'org.openpsa.dbe');
            org_openpsa_helpers_schema_modifier(&$this->_datamanagers['person'], 'dbe_service_id', 'config_key', 'serviceID');
            org_openpsa_helpers_schema_modifier(&$this->_datamanagers['person'], 'dbe_service_id', 'start_fieldgroup', Array('title' => $this->_request_data['l10n']->get('digital business ecosystem')));
            org_openpsa_helpers_schema_modifier(&$this->_datamanagers['person'], 'dbe_service_id', 'end_fieldgroup', '');
        }

        // Load the group to datamanager
        if (!$this->_datamanagers['person']->init($person))
        {
            debug_add("Datamanager failed to handle person {$identifier}");
            debug_pop();
            return false;
        }

        $_MIDCOM->set_pagetitle("{$person->firstname} {$person->lastname}");

        debug_pop();
        return $person;
    }

    function _creation_dm_callback_person(&$datamanager)
    {
        // This is what Datamanager calls to actually create a person
        $result = array (
            "success" => false,
            "storage" => null,
        );

        $person = new org_openpsa_contacts_person();
        $person->firstname = "";
        $person->lastname = "";

        $stat = $person->create();
        if ($stat)
        {
            $this->_request_data['person'] = new org_openpsa_contacts_person($person->id);
            //Debugging
            $person = $this->_request_data['person'];
            $result["storage"] =& $this->_request_data['person'];
            $result["success"] = true;
            return $result;
        }
        return null;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_person_new($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_contacts_person');

        if (!$this->_datamanagers['person']->init_creation_mode("newperson",$this,"_creation_dm_callback_person"))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'newperson'.");
            // This will exit
        }

        if (count($args) > 0)
        {
            // Get the organization
            $this->_request_data['group'] = $this->_request_data['group_handler']->_load($args[0]);

            if (!$this->_request_data['group'])
            {
                return false;
            }

            // Check permissions
            $_MIDCOM->auth->require_do('midgard:create', $this->_request_data['group']);
        }

        switch ($this->_datamanagers['person']->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);

                break;

            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['person']->parameter("midcom.helper.datamanager","layout","default");

                // Index the person
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanagers['person']);

                // Add person to group if requested
                if ($this->_request_data['group'])
                {
                    $member = new midcom_baseclasses_database_member();
                    $member->uid = $this->_request_data['person']->id;
                    $member->gid = $this->_request_data['group']->id;
                    $member->create();

                    if ($member->id)
                    {
                        debug_add("Added person #{$this->_request_data['person']->id} to group #{$this->_request_data['group']->id} successfully");
                    }
                    else
                    {
                        // TODO: Cleanup
                        debug_pop();
                        $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                            "Failed adding the person to group #{$this->_request_data['group']->id}, reason {$member->errstr}");
                        // This will exit
                    }
                }

                // Relocate to group view
                debug_pop();
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $_MIDCOM->relocate("{$prefix}person/{$this->_request_data['person']->guid}/");
                break;

            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                debug_pop();
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

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_person_new($handler_id, &$data)
    {
        $GLOBALS["view"] = $this->_datamanagers['person'];
        midcom_show_style("show-person-new");
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_person($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->require_valid_user();

        // Get the requested person object
        $this->_request_data['person'] = $this->_load_person($args[0]);
        if (!$this->_request_data['person'])
        {
            debug_add("Person loading failed");
            debug_pop();
            return false;
        }

        // Add toolbar items
        if (count($args) == 1)
        {
            debug_add("Populating the Edit button");
            $this->_toolbars->bottom->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "person/{$this->_request_data['person']->guid}/edit/",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("edit"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['person']),
                )
            );

            $this->_toolbars->bottom->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "person/{$this->_request_data['person']->guid}/privileges.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("permissions"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png', // TODO: Get better icon
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:privileges', $this->_request_data['person']),
                )
            );
            if ($this->_request_data['person']->username)
            {
                $this->_toolbars->bottom->add_item
                (
                    Array
                    (
                        MIDCOM_TOOLBAR_URL => "person/{$this->_request_data['person']->guid}/account_edit.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('edit account'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                        MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['person']),
                    )
                );
            }
            else
            {
                $this->_toolbars->bottom->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "person/{$this->_request_data['person']->guid}/account_create.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create account'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                        MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['person']),
                    )
                );
            }

            $cal_node = midcom_helper_find_node_by_component('org.openpsa.calendar');
            if (!empty($cal_node))
            {
                $this->_toolbars->top->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "#",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create event'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new-event.png',
                        //TODO: Check for privileges somehow
                        MIDCOM_TOOLBAR_ENABLED => true,
                        MIDCOM_TOOLBAR_OPTIONS  => Array(
                            'rel' => 'directlink',
                            'onClick' => org_openpsa_calendar_interface::calendar_newevent_js($cal_node, false, $this->_request_data['person']->guid),
                        ),
                    )
                );
            }

            $qb = org_openpsa_contacts_buddy::new_query_builder();
            $user = $_MIDCOM->auth->user->get_storage();
            $qb->add_constraint('account', '=', $user->guid);
            $qb->add_constraint('buddy', '=', $this->_request_data['person']->guid);
            $qb->add_constraint('blacklisted', '=', false);
            $buddies = $qb->execute();
            if (count($buddies) > 0)
            {
                // We're buddies, show remove button
                $this->_toolbars->bottom->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "buddylist/remove/{$this->_request_data['person']->guid}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('remove buddy'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                        MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:delete', $buddies[0]),
                    )
                );
            }
            else
            {
                // We're not buddies, show add button
                $this->_toolbars->bottom->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "buddylist/add/{$this->_request_data['person']->guid}.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('add buddy'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_person.png',
                        MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:create', $user),
                    )
                );
            }

            if ($handler_id == 'person_view')
            {
                $this->_toolbars->bottom->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "person/related/{$this->_request_data['person']->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('view related information'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    )
                );
            }
            else
            {
                $this->_toolbars->bottom->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "person/{$this->_request_data['person']->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('back to person'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    )
                );

                // Load "Create X" buttons for all the related info
                $relatedto_button_settings = org_openpsa_relatedto_handler::common_toolbar_buttons_defaults();
                $relatedto_button_settings['wikinote']['wikiword'] = sprintf($this->_request_data['l10n']->get('notes for %s on %s'), $this->_request_data['person']->name, date('Y-m-d H:i'));

                unset($relatedto_button_settings['event']);

                org_openpsa_relatedto_handler::common_node_toolbar_buttons($this->_toolbars->top, $this->_request_data['person'], 'org.openpsa.contacts', $relatedto_button_settings);
            }
        }

        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_person($handler_id, &$data)
    {
        if ($handler_id == 'person_view')
        {
            org_openpsa_helpers_schema_modifier(&$this->_datamanagers['person'], 'dbe_service_id', 'hidden', true);
            $this->_request_data['person_dm'] = $this->_datamanagers['person'];
            midcom_show_style("show-person");
        }
        else
        {
            midcom_show_style("show-person-related");
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_person_action($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->require_valid_user();
        debug_add("Person action handler called");

        // Check if we get the person
        if (!$this->_handler_person($handler_id, $args, &$data))
        {
            debug_add("Person handler failed");
            debug_pop();
            return false;
        }

        // Check if the action is a valid one
        $this->_request_data['person_action'] = $args[1];
        debug_add("person_action: {$this->_request_data['person_action']}");
        switch ($args[1])
        {
            case "privileges":
                debug_add("Entering privilege handler");
                $_MIDCOM->auth->require_do('midgard:privileges', $this->_request_data['person']);
                $user_object = $_MIDCOM->auth->get_user($this->_request_data['person']->guid);

                // Get the calendar root event
                $_MIDCOM->componentloader->load('org.openpsa.calendar');
                if (   isset($GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'])
                    && is_object($GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']))
                {
                    $this->_datamanagers['acl']->_layoutdb['default']['fields']['calendar']['privilege_object'] = $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'];
                    $this->_datamanagers['acl']->_layoutdb['default']['fields']['calendar']['privilege_assignee'] = $user_object->id;
                }
                else if (isset($this->_datamanagers['acl']->_layoutdb['default']['fields']['calendar']))
                {
                    unset($this->_datamanagers['acl']->_layoutdb['default']['fields']['calendar']);
                }

                // Set the contacts root group into ACL
                /* The persons are not necessarily under the root group
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_creation']['privilege_object'] = $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group'];
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_editing']['privilege_object'] = $GLOBALS['midcom_component_data']['org.openpsa.contacts']['contacts_root_group'];
                */
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_creation']['privilege_object'] =  $user_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_editing']['privilege_object'] =  $user_object->get_storage();
                // Set user object as privilege assignee
                /* Skip assignee to make it 'SELF'
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_creation']['privilege_assignee'] = $user_object->id;
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['contact_editing']['privilege_assignee'] = $user_object->id;
                */

                // Load project classes
                $_MIDCOM->componentloader->load('org.openpsa.projects');
                // Load invoice classes
                $_MIDCOM->componentloader->load('org.openpsa.invoices');
                // Load campaign classes
                $_MIDCOM->componentloader->load('org.openpsa.directmarketing');

                $this->_datamanagers['acl']->_layoutdb['default']['fields']['organization_creation']['privilege_object'] = $user_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['organization_editing']['privilege_object'] = $user_object->get_storage();

                $this->_datamanagers['acl']->_layoutdb['default']['fields']['projects']['privilege_object'] = $user_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['invoices_creation']['privilege_object'] = $user_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['invoices_editing']['privilege_object'] = $user_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['campaigns_creation']['privilege_object'] = $user_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['campaigns_editing']['privilege_object'] = $user_object->get_storage();
                $this->_datamanagers['acl']->_layoutdb['default']['fields']['salesproject_creation']['privilege_object'] = $user_object->get_storage();

                $this->_datamanagers['acl']->init($this->_request_data['person']);

                switch ($this->_datamanagers['acl']->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "privileges";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);

                        debug_pop();
                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        $this->_view = "default";
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "person/" . $this->_request_data["person"]->guid . "/");
                        // This will exit()

                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "person/" . $this->_request_data["person"]->guid . "/");
                        // This will exit()

                    case MIDCOM_DATAMGR_FAILED:
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        debug_pop();
                        return false;
                }

                debug_pop();
                return true;

            case "account_create":
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['person']);

                if ($this->_request_data['person']->username)
                {
                    // Creating new account for existing account is not possible
                    return false;
                }

                $this->_view = "area_person_account_create";

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);

                if (array_key_exists('midcom_helper_datamanager_submit', $_POST))
                {
                    // User has tried to create account
                    $plaintext = true;
                    $stat = $this->_request_data['person']->set_account($_POST['org_openpsa_contacts_person_account_username'], $_POST['org_openpsa_contacts_person_account_password'], $plaintext);

                    if ($stat)
                    {
                        // Account created, redirect to person card
                        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                        $_MIDCOM->relocate($prefix."person/".$this->_request_data['person']->guid."/");
                    }
                    else
                    {
                        // Failure, give a message
                        $messagebox = new org_openpsa_helpers_uimessages();
                        $messagebox->addMessage($this->_request_data['l10n']->get("failed to create user account, reason ").mgd_errstr(), 'error');
                    }
                }

                if ($this->_request_data['person']->email)
                {
                    // Email address (first part) is the default username
                    $this->_request_data['default_username'] = preg_replace('/@.*/', '', $this->_request_data['person']->email);
                }
                else
                {
                    // Otherwise use cleaned up firstname.lastname
                    $this->_request_data['default_username'] = midcom_generate_urlname_from_string($this->_request_data['person']->firstname) . '.' . midcom_generate_urlname_from_string($this->_request_data['person']->lastname);
                }

                // TODO: Generate random password
                // We should do this by listing to /dev/urandom
                //$this->_request_data['default_password'] = substr(md5(microtime()), 5, 6);
                $d = $this->_request_data['config']->get('default_password_lenght');
                // Safety
                if ($d == 0)
                {
                    $d = 6;
                }
                if (function_exists('mt_rand'))
                {
                    $rand = 'mt_rand';
                }
                else
                {
                    $rand = 'rand';
                }
                // Valid characters for default password (PONDER: make configurable ?)
                $passwdchars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#@';
                $this->_request_data['default_password'] = '';
                while ($d--)
                {
                    $this->_request_data['default_password'] .= $passwdchars[$rand(0, strlen($passwdchars) - 1)];
                }
                return true;

            case "account_edit":
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['person']);

                if ($this->_request_data['person']->id != $_MIDGARD['user'] && !$_MIDGARD['admin'])
                {
                    return false;
                }

                if (!$this->_request_data['person']->username)
                {
                    // Account needs to be created first, relocate
                    $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                    $_MIDCOM->relocate($prefix."person/".$this->_request_data['person']->guid."/account_create.html");
                }

                $this->_view = "area_person_account_edit";

                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);

                if (array_key_exists('midcom_helper_datamanager_submit', $_POST))
                {

                    // Check that the inputted passwords match
                    if ($_POST['org_openpsa_contacts_person_account_newpassword'] != $_POST['org_openpsa_contacts_person_account_newpassword2'])
                    {
                        $messagebox = new org_openpsa_helpers_uimessages();
                        $messagebox->addMessage($this->_request_data['l10n']->get("passwords don't match"), 'error');
                    }
                    else
                    {

                        $plaintext = true;

                        // Update account
                        $stat = $this->_request_data['person']->set_account($_POST['org_openpsa_contacts_person_account_username'], $_POST['org_openpsa_contacts_person_account_newpassword'], $plaintext);

                        if ($stat)
                        {
                            // Account updated, redirect to person card
                            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                            $_MIDCOM->relocate($prefix."person/".$this->_request_data['person']->guid."/");
                        }
                        else
                        {
                            // Failure, give a message
                            $messagebox = new org_openpsa_helpers_uimessages();
                            $messagebox->addMessage($this->_request_data['l10n']->get("failed to update user account, reason ").mgd_errstr(), 'error');
                        }
                    }
                }
                return true;

            case "groups":
                // Group person listing, always work even if there are none
                $this->_view = "area_person_memberships";
                return true;

            case "edit":
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['person']);

                if ($this->_request_data['person']->sitegroup != $_MIDGARD['sitegroup'])
                {
                    return false;
                }

                switch ($this->_datamanagers['person']->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        debug_add('DM returned to mode: MIDCOM_DATAMGR_EDITING');
                        $this->_view = "default";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);

                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        debug_add('DM returned to mode: MIDCOM_DATAMGR_SAVED');
                        // Index the person
                        $indexer =& $_MIDCOM->get_service('indexer');
                        debug_add('indexing person started');
                        $indexer->index($this->_datamanagers['person']);
                        debug_add('indexing person done');

                        $this->_view = "default";
                        $relocate_to = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "person/" . $this->_request_data["person"]->guid();
                        debug_add("trying to relocate to: {$relocate_to}");
                        $_MIDCOM->relocate($relocate_to);
                        // This will exit()
                        break;
                    case MIDCOM_DATAMGR_CANCELLED:
                        debug_add('DM returned to mode: MIDCOM_DATAMGR_CANCELLED');
                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "person/" . $this->_request_data["person"]->guid());
                        // This will exit()
                        break;
                    case MIDCOM_DATAMGR_FAILED:
                        debug_add('DM returned to mode: MIDCOM_DATAMGR_FAILED');
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        return false;
                }

                return true;
            default:
                return false;
        }
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_person_action($handler_id, &$data)
    {
        if ($this->_view == "area_person_account_create")
        {
            midcom_show_style("show-person-account-create");
        }
        elseif ($this->_view == "area_person_account_edit")
        {
            midcom_show_style("show-person-account-edit");
        }
        elseif ($this->_view == "area_person_memberships")
        {
            // This is most likely a dynamic_load
            midcom_show_style("show-person-groups-header");
            $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_member');
            $qb->add_constraint('uid', '=', $this->_request_data['person']->id);
            $results = $_MIDCOM->dbfactory->exec_query_builder($qb);
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
                    $this->_request_data['group'] = new org_openpsa_contacts_group($member->gid);
                    midcom_show_style("show-person-groups-item");
                }
            }
            else
            {
                midcom_show_style("show-person-groups-empty");
            }
            midcom_show_style("show-person-groups-footer");
        }
        elseif ($this->_view == "privileges")
        {
            // Default view, display the selected action
            $this->_request_data['acl_dm'] = $this->_datamanagers['acl'];
            midcom_show_style("show-privileges");
        }
        else
        {
            // Default view, display the selected action
            $GLOBALS["view"] = $this->_datamanagers['person'];
            midcom_show_style("show-person-{$data['person_action']}");
        }
    }

    /**
     * Does a QB query for persons, returns false or number of matched entries
     *
     * Displays style element 'search-persons-empty' only if $displayEmpty is
     * set to true.
     */
    function _search_qb_persons($search, $displayEmpty=false, $displayOutput=true, $limit=false, $offset=false)
    {
        if ($search == NULL)
        {
            return false;
        }

        $search = str_replace('*', '%', $search);

        $qb_org = org_openpsa_contacts_person::new_query_builder();
        $qb_org->begin_group('OR');

        // Search using only the fields defined in config
        $person_fields = explode(',', $this->_request_data['config']->get('person_search_fields'));
        if (   !is_array($person_fields)
            || count($person_fields) == 0)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Invalid person search configuration');
        }

        foreach ($person_fields as $field)
        {
            if (empty($field))
            {
                continue;
            }
            $qb_org->add_constraint($field, 'LIKE', $search);
        }

        $qb_org->end_group();
        //Skip accounts in other sitegroups (sitegroup constraint is no longer dropped ?)
        $qb_org->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        //mgd_debug_start();
        $results = $qb_org->execute();
        //mgd_debug_stop();
        if (   is_array($results)
            && count($results)>0)
        {
            if ($displayOutput)
            {
                midcom_show_style('search-persons-header');
                foreach($results as $person)
                {
                    $GLOBALS['view_person'] = $person;
                    midcom_show_style('search-persons-item');
                }
                midcom_show_style('search-persons-footer');
                return count($results);
            }
            else
            {
                return $results;
            }
        }
        else
        {
            //No group results
            if ($displayEmpty && $displayOutput)
            {
                midcom_show_style('search-persons-empty');
            }
            return false;
        }
    }

}
?>