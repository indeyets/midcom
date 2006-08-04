<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: campaign_handler.php,v 1.28 2006/07/06 15:48:42 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
/**
 * org.openpsa.directmarketing campaign handler and viewer class.
 */
class org_openpsa_directmarketing_campaign_handler
{
    var $_datamanagers;
    var $_request_data;
    var $_config = null;
    var $_toolbars = null;
    
    function org_openpsa_directmarketing_campaign_handler(&$datamanagers, &$request_data, &$config)
    {
        $this->_datamanagers = &$datamanagers;
        $this->_request_data = &$request_data;
        $this->_config &= $config;
        $this->_toolbars = &midcom_helper_toolbars::get_instance();
    }
    
    function _load($identifier)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $campaign = new org_openpsa_directmarketing_campaign($identifier);
        
        if (!is_object($campaign))
        {
            debug_add("Campaign object {$identifier} is not an object");
            debug_pop();
            return false;
        }
        
        // Load the campaign to datamanager
        if (!$this->_datamanagers['campaign']->init($campaign))
        {
            debug_add("Datamanager failed to handle campaign {$identifier}");
            debug_pop();
            return false;
        }
        
        //$this->_view_toolbar->bind_to($campaign);
        
        debug_pop();
        return $campaign;
    }

    function _creation_dm_callback(&$datamanager)
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        // This is what Datamanager calls to actually create a directory
        $result = array (
            "success" => false,
            "storage" => null,
        );

        $campaign = new org_openpsa_directmarketing_campaign();
        $stat = $campaign->create();
        if ($stat)
        {
            $this->_request_data['campaign'] = new org_openpsa_directmarketing_campaign($campaign->id);            
            $result["storage"] =& $this->_request_data['campaign'];
            $result["success"] = true;
            debug_pop();
            return $result;
        }
        debug_pop();
        return null;
    }   

    function _handler_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);    
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_directmarketing_campaign');

        if (!$this->_datamanagers['campaign']->init_creation_mode("default",$this,"_creation_dm_callback"))
        {
            debug_pop();
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanger in creation mode for schema 'default'.");
            // This will exit   
        }

        // Add toolbar items
        org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);

        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('create campaign'));

        switch ($this->_datamanagers['campaign']->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');
                break;

            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:
                debug_add("First time submit, the DM has created an object");

                // Index the directory
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanagers['campaign']);
                                
                // Relocate to the new directory view
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . 'campaign/' . $this->_request_data['campaign']->guid. '/');
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
                $this->errstr = 'The Datamanger failed to process the request, see the Debug Log for details';
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
        debug_push_class(__CLASS__, __FUNCTION__);    
        $this->_request_data['campaign_dm'] = $this->_datamanagers['campaign'];
        midcom_show_style("show-campaign-new");
        debug_pop();
    }

    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        if (count($args) == 1)
        {
            $this->_request_data['person'] = new midcom_baseclasses_database_person($args[0]);
            if (!$this->_request_data['person'])
            {
                debug_add("Person record '{$args[0]}' not found");
                debug_pop();
                return false;
                // This will exit               
            }
            
            if (array_key_exists('add_to_campaign', $_POST))
            {
                // Add person to campaign
                $campaign = new org_openpsa_directmarketing_campaign($_POST['add_to_campaign']);
                if ($campaign)
                {
                    $_MIDCOM->auth->require_do('midgard:create', $campaign);
                
                    $member = new org_openpsa_directmarketing_campaign_member();
                    $member->orgOpenpsaObType = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER;
                    $member->person = $this->_request_data['person']->id;
                    $member->campaign = $campaign->id;
                    $member->create();
                    $message = new org_openpsa_helpers_uimessages();
                    if ($member->id)
                    {
                        $message->addMessage(
                            sprintf(
                                $this->_request_data['l10n']->get('Added person %s to campaign %s'),
                                "{$this->_request_data['person']->firstname} {$this->_request_data['person']->lastname}", 
                                $campaign->title
                            ), 
                            'ok'
                        );
                    }
                    else
                    {
                        $message->addMessage(
                            sprintf(
                                $this->_request_data['l10n']->get('Failed adding person %s to campaign %s'),
                                "{$this->_request_data['person']->firstname} {$this->_request_data['person']->lastname}", 
                                $campaign->title
                            ), 
                            'error'
                        );
                    }
                }
            }
        }
    
        return true;
    }

    function _show_list($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__); 
        
        midcom_show_style("show-campaign-list-header");
        $this->_request_data['campaigns_all'] = array();
        if (   array_key_exists('person', $this->_request_data)
            && $this->_request_data['person'])
        {
            debug_add("Listing campaigns person '{$this->_request_data['person']->guid}' is member of");
            
            $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_directmarketing_campaign_member');
            $qb->add_constraint('person', '=', $this->_request_data['person']->id);
            $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
            $memberships = $_MIDCOM->dbfactory->exec_query_builder($qb);
            
            $campaign_membership_map = array();
            $campaigns = array();
            if ($memberships)
            {
                foreach ($memberships as $membership)
                {
                    $campaign_membership_map[$membership->campaign] = $membership;
                    $campaigns[$membership->campaign] = $this->_load($membership->campaign);
                }
            }

            // List active campaigns for the "add to campaign" selector
            $qb_all = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_directmarketing_campaign');
            $qb_all->add_constraint('archived', '=', 0);
            $campaigns_all = $_MIDCOM->dbfactory->exec_query_builder($qb_all);
            
            if ($campaigns_all)
            {
                foreach ($campaigns_all as $campaign)
                {
                    if (   !array_key_exists($campaign->id, $campaigns)
                        && $_MIDCOM->auth->can_do('midgard:create', $campaign))
                    {
                        $this->_request_data['campaigns_all'][] = $campaign;
                    }
                }
            }

        }
        else
        {
            debug_add("Listing campaigns visible to current user");
            
            $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_directmarketing_campaign');
            $qb->add_constraint('archived', '=', 0);
            
            // Workgroup filtering
            if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
            {
                debug_add("Filtering documents by workgroup {$GLOBALS['org_openpsa_core_workgroup_filter']}");
                $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
            }

            $campaigns = $_MIDCOM->dbfactory->exec_query_builder($qb);
        }
        if (   is_array($campaigns)
            && count($campaigns) > 0)
        {     
            foreach ($campaigns as $campaign)
            {
                $this->_request_data['campaign'] =  $this->_load($campaign->guid);
                $this->_request_data['campaign_array'] = $this->_datamanagers['campaign']->get_array();
                if (   isset($campaign_membership_map)
                    && array_key_exists($campaign->id, $campaign_membership_map))
                {
                    $this->_request_data['membership'] = $campaign_membership_map[$campaign->id];
                }
                
                // TODO: Get count of members and messages here
                          
                midcom_show_style('show-campaign-list-item');
            }
        }
        midcom_show_style("show-campaign-list-footer");        
        debug_pop();
    }

    function _handler_view($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);   

        // Get the requested document metadata object
        $this->_request_data['campaign'] = $this->_load($args[0]);
        if (!$this->_request_data['campaign'])
        {
            debug_pop();
            return false;
        }
        
        $_MIDCOM->set_pagetitle($this->_request_data['campaign']->title);
        $this->_component_data['active_leaf'] = $this->_request_data['campaign']->id;
        
        // Add toolbar items for root view
        if (count($args) == 1)
        {
            //Edit button in case we a) can edit b) are not in edit mode already
            $this->_toolbars->bottom->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "campaign/{$this->_request_data['campaign']->guid}/edit.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("edit"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                    /*
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['directory']),
                    */
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['campaign']),
                )
            );
            //Import button if we have permissions to create users
            $this->_toolbars->bottom->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "campaign/import/{$this->_request_data['campaign']->guid}.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("import subscribers"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'midcom_db_person'),
                )
            );
            //Edit query parameters button in case 1) not in edit mode 2) is smart campaign 3) can edit
            if ($this->_request_data['campaign']->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART)
            {
                $this->_toolbars->bottom->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "campaign/{$this->_request_data['campaign']->guid}/edit_query.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("edit rules"),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/repair.png',
                        /*
                        MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['directory']),
                        */
                        MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['campaign']),
                    )
                );
            }
        }
        
        // List members of this campaign
        /*
        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_directmarketing_campaign_member');
        $qb->add_constraint('campaign', '=', $this->_request_data['campaign']->id);
        $members = $_MIDCOM->dbfactory->exec_query_builder($qb);
        */
        $qb = new org_openpsa_qbpager_direct('org_openpsa_campaign_member', 'campaign_members');
        //Debugging
        //$qb->results_per_page = 1;
        $qb->add_constraint('campaign', '=', $this->_request_data['campaign']->id);
        $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED);
        $this->_request_data['campaign_members_qb'] =& $qb;
        $this->_request_data['memberships'] = $qb->execute_unchecked();
        $this->_request_data['campaign_members_count'] =  $qb->count_unchecked();
            
        $this->_request_data['campaign_members'] = array();
        if (!empty($this->_request_data['memberships']))
        {
            foreach ($this->_request_data['memberships'] as $k => $membership)
            {
                $this->_request_data['campaign_members'][$k] = new midcom_baseclasses_database_person($membership->person);
            }
        }

        // Get message schemas
        $this->_request_data['message_schemas'] = $this->_datamanagers['message']->_layoutdb;
        foreach ($this->_request_data['message_schemas'] as $schema)
        {
            $this->_toolbars->top->add_item(
                array(
                    MIDCOM_TOOLBAR_URL => "message/new/{$this->_request_data['campaign']->guid}/{$schema['name']}",
                    MIDCOM_TOOLBAR_LABEL => sprintf($this->_request_data['l10n_midcom']->get("create %s"), $this->_request_data['l10n']->get($schema['description'])),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/'.$this->_request_data['message_handler']->get_icon($schema['org_openpsa_directmarketing_messagetype']),
                    // Ponder why is create granted under this campaign if general create is given (probably just because of that, checking update)
                    MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['campaign']),
                )
            );
        }

        debug_pop();
        return true;
    }

    function _show_view($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        $this->_request_data['campaign_dm'] = $this->_datamanagers['campaign'];
        midcom_show_style("show-campaign");
        debug_pop();
    }
    
    function _handler_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);    
        // Check if we get the campaign
        if (!$this->_handler_view($handler_id, $args, &$data))
        {
            debug_pop();
            return false;
        }

        $this->_request_data['action'] = $args[1];
        switch ($this->_request_data['action'])
        {
            case 'edit':
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['campaign']);
            
                switch ($this->_datamanagers['campaign']->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "edit";
        
                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);
                        
                        debug_pop();
                        return true;
        
                    case MIDCOM_DATAMGR_SAVED:                
                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "campaign/" . $this->_request_data["campaign"]->guid);
                        // This will exit()
                
                    case MIDCOM_DATAMGR_FAILED:
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        debug_pop();
                        return false;
                }
                break;
            case 'edit_query':
                // Add toolbar items
                org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);
                
                //PONDER: Locking ?
                
                if (   isset($_POST['midcom_helper_datamanager_cancel'])
                    && !empty($_POST['midcom_helper_datamanager_cancel']))
                {
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                        . "campaign/" . $this->_request_data["campaign"]->guid);
                    // This will exit()
                }
                if (   isset($_POST['midcom_helper_datamanager_submit'])
                    && !empty($_POST['midcom_helper_datamanager_submit']))
                {
                    debug_add("_POST\n===\n" . sprint_r($_POST) . "===\n");
                    //Actual save routine
                    $messages = new org_openpsa_helpers_uimessages();
                    if (   !isset($_POST['midcom_helper_datamanager_dummy_field_rules'])
                        || empty($_POST['midcom_helper_datamanager_dummy_field_rules']))
                    {
                        //Rule empty
                        $messages->add_message('no rule given', 'error');
                        break;
                    }
                    $eval = '$tmp_array = ' . $_POST['midcom_helper_datamanager_dummy_field_rules'] . ';';
                    //$eval_ret = eval($eval);
                    $eval_ret = @eval($eval);
                    if ($eval_ret === false)
                    {
                        //Rule could not be parsed
                        $messages->add_message('given rule could not be parsed', 'error');
                        break;
                    }
                    $this->_request_data['campaign']->rules = $tmp_array;
                    $update_ret = $this->_request_data['campaign']->update();
                    if (!$update_ret)
                    {
                        //Save failed
                        $messages->add_message('error when saving rule:' . mgd_errstr(), 'error');
                        break;
                    }
                    
                    //Schedule background members refresh
                    $this->_request_data['campaign']->schedule_update_smart_campaign_members();
                                        
                    //Save ok, relocate
                    $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                        . "campaign/" . $this->_request_data["campaign"]->guid);
                    // This will exit()
                }
                break;
            case 'ajax':
                $ajax = new org_openpsa_helpers_ajax();
                if (!isset($_REQUEST['org_openpsa_ajax_mode']))
                {
                    $ajax->simpleReply(false, 'mode not set');
                    //this will exit
                }
                switch ($_REQUEST['org_openpsa_ajax_mode'])
                {
                    case 'unsubscribe':
                        $_MIDCOM->auth->request_sudo();
                        $this->_request_data['membership'] = false;
                        if (isset($_POST['org_openpsa_ajax_membership_guid']))
                        {
                            $this->_request_data['membership'] = new org_openpsa_directmarketing_campaign_member($_POST['org_openpsa_ajax_membership_guid']);
                        }
                        else if (isset($_POST['org_openpsa_ajax_person_guid']))
                        {
                            $person = new org_openpsa_contacts_person($_POST['org_openpsa_ajax_person_guid']);
                            if (!is_object($person)
                                || empty($person->id))
                            {
                                $ajax->simpleReply(false, "Person '{$_POST['org_openpsa_ajax_person_guid']}' not found");
                                //this will exit
                            }
                            $qb = org_openpsa_directmarketing_campaign_member::new_query_builder();
                            $qb->add_constraint('person', '=', $person->id);
                            $qb->add_constraint('campaign', '=',  $this->_request_data['campaign']->id);
                            //Do not unsubscribe testers
                            $qb->add_constraint('orgOpenpsaObtype', '<>',  ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
                            $ret =  $qb->execute();
                            if (   !is_array($ret)
                                || count($ret) == 0)
                            {
                                debug_add("Membership record for person '{$person->guid}' not found");
                                debug_pop();
                                $ajax->simpleReply(false, "Membership record for person '{$person->guid}' not found");
                                // This will exit
                            }
                            //We might have multiple memberships for same person (untill the uniqueness requirements are enforced everywhere)
                            foreach ($ret as $member)
                            {
                                $member->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED;
                                $stat = $member->update();                        
                            }
                            //$this->_request_data['membership'] = $ret[0];
                        }
                        /*
                        if (!$this->_request_data['membership'])
                        {
                            debug_add("Membership record not found");
                            debug_pop();
                            $ajax->simpleReply(false, "Membership record not found");
                            // This will exit
                        }
                        $this->_request_data['membership']->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED;
                        $stat = $this->_request_data['membership']->update();                        
                        */
                        $_MIDCOM->auth->drop_sudo();
                        $ajax->simpleReply($stat, mgd_errstr());
                        //this will exit
                        break;
                    default:
                        $ajax->simpleReply(false, 'mode not set recognized');
                        //this will exit
                        break;
                }
                break;
            default:
                debug_pop();
                return false;
        }
        debug_pop();
        return true;
    }
    
    function _show_action($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        $this->_request_data['campaign_dm']  = $this->_datamanagers['campaign'];
        switch ($this->_request_data['action'])
        {
            case 'edit':
                midcom_show_style("show-campaign-edit");
                break;
            case 'edit_query':
                midcom_show_style("show-campaign-edit_query");
                break;
        }
        debug_pop();
    }

    function _handler_unsubscribe($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        if (count($args) != 1)
        {
            debug_pop();
            return false;
            // This will exit               
        }
        $_MIDCOM->auth->request_sudo();
        $this->_request_data['membership'] = new org_openpsa_directmarketing_campaign_member($args[0]);
        if (!is_a($this->_request_data['membership'], 'org_openpsa_directmarketing_campaign_member'))
        {
            debug_add("Membership record '{$args[0]}' not found", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
            // This will exit
        }
        $this->_request_data['campaign'] = new org_openpsa_directmarketing_campaign($this->_request_data['membership']->campaign);
        $this->_request_data['membership']->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED;
        $this->_request_data['unsubscribe_status'] = $this->_request_data['membership']->update();
        debug_add("Unsubscribe status: {$this->_request_data['unsubscribe_status']}");
        $_MIDCOM->auth->drop_sudo();
        //This is often called by people who should not see anything pointing to OpenPSA, also allows full styling of the unsubscribe page
        $_MIDCOM->skip_page_style = true;
        
        debug_pop();
        return true;
    }
    
    function _show_unsubscribe($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($this->_request_data['unsubscribe_status'] == false)
        {
            midcom_show_style('show-unsubscribe-failed');
        }
        else
        {
            midcom_show_style('show-unsubscribe-ok');
        }
        debug_pop();
    }

    function _handler_unsubscribe_all($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        if (count($args) != 1)
        {
            debug_pop();
            return false;
            // This will exit               
        }
        $_MIDCOM->auth->request_sudo();
        $this->_request_data['person'] = new org_openpsa_contacts_person($args[0]);
        if (!is_a($this->_request_data['person'], 'org_openpsa_contacts_person'))
        {
            debug_add("Person record '{$args[0]}' not found", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
            // This will exit
        }
        $this->_request_data['unsubscribe_status'] = true;
        
        $qb = org_openpsa_directmarketing_campaign_member::new_query_builder();
        $qb->add_constraint('person', '=', $this->_request_data['person']->id);
        $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED);
        $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        $memberships = $qb->execute();
        if ($memberships === false)
        {
            //Some error occured with QB
            $_MIDCOM->auth->drop_sudo();
            debug_pop();
            return false;
        }
        foreach ($memberships as $member)
        {
            $member->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED;
            $mret = $member->update();
            if (!$mret)
            {
                //TODO: How to report failures of single rows when other succeed sensibly ??
                $this->_request_data['unsubscribe_status'] = false;
            }
        }
        
        $_MIDCOM->auth->drop_sudo();
        //This is often called by people who should not see anything pointing to OpenPSA, also allows full styling of the unsubscribe page
        $_MIDCOM->skip_page_style = true;
        
        debug_pop();
        return true;
    }

    function _show_unsubscribe_all($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if ($this->_request_data['unsubscribe_status'] == false)
        {
            midcom_show_style('show-unsubscribe-failed');
        }
        else
        {
            midcom_show_style('show-unsubscribe-ok');
        }
        debug_pop();
    }
}
?>