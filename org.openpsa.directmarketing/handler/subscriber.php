<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: import.php,v 1.4 2006/06/19 09:39:42 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.directmarketing campaign handler and viewer class.
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_handler_subscriber extends midcom_baseclasses_components_handler
{
    function org_openpsa_directmarketing_handler_subscriber()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
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

            $qb = org_openpsa_directmarketing_campaign_member::new_query_builder();
            $qb->add_constraint('person', '=', $this->_request_data['person']->id);
            $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
            $memberships = $qb->execute();

            $campaign_membership_map = array();
            $campaigns = array();
            if ($memberships)
            {
                foreach ($memberships as $membership)
                {
                    $campaign_membership_map[$membership->campaign] = $membership;
                    $campaigns[$membership->campaign] = new org_openpsa_directmarketing_campaign($membership->campaign);
                }
            }

            // List active campaigns for the "add to campaign" selector
            $qb_all = org_openpsa_directmarketing_campaign::new_query_builder();
            $qb_all->add_constraint('archived', '=', 0);
            $campaigns_all = $qb_all->execute();

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

            $qb = org_openpsa_directmarketing_campaign::new_query_builder();
            $qb->add_constraint('archived', '=', 0);

            // Workgroup filtering
            if ($GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
            {
                debug_add("Filtering documents by workgroup {$GLOBALS['org_openpsa_core_workgroup_filter']}");
                $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
            }

            $campaigns = $qb->execute();
        }
        if (   is_array($campaigns)
            && count($campaigns) > 0)
        {
            foreach ($campaigns as $campaign)
            {
                $this->_request_data['campaign'] = new org_openpsa_directmarketing_campaign($campaign->guid);
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

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
	 */
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

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
	 */
    function _handler_unsubscribe_ajax($handler_id, $args, &$data)
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

        $message = new org_openpsa_helpers_ajax();
        $message->simpleReply($this->_request_data['unsubscribe_status'], "Unsubscribe failed");
        // This will exit

        return true;
    }

    function _show_unsubscribe_ajax($handler_id, &$data)  { }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
	 */
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
            //Some error occurred with QB
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
        if ($data['unsubscribe_status'] == false)
        {
            midcom_show_style('show-unsubscribe-failed');
        }
        else
        {
            midcom_show_style('show-unsubscribe-ok');
        }
    }
}
?>