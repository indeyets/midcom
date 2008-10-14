<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM DBA wrapped access to org_openpsa_campaign object, with some utility methods
 *
 * @package org.openpsa.directmarketing
 */
class midcom_org_openpsa_campaign extends __midcom_org_openpsa_campaign
{
    var $testers = array(); // List of tests members (stored as campaign_members, referenced here for easier access)
    var $testers2 = array(); // List of testers, in DM2 format
    var $rules = array(); //rules for smart-campaign

    function __construct($id = null)
    {
        $stat = parent::__construct($id);
        if (   !$this->orgOpenpsaObtype
            && $stat)
        {
            $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN;
        }
        return $stat;
    }

    function _sync_to_dm2()
    {
        if (!is_array($this->testers))
        {
            $this->testers = array();
        }
        
        foreach ($this->testers as $tester => $selected)
        {
            $this->testers2[] = $tester;
        }
    }

    function _sync_from_dm2()
    {
        if (!is_array($this->testers))
        {
            $this->testers = array();
        }
        
        if (!is_array($this->testers2))
        {
            $this->testers2 = array();
        }
        
        foreach ($this->testers as $tester => $included)
        {
            if (!in_array($tester, $this->testers2))
            {
                unset($this->testers[$tester]);
            }
        }
        foreach ($this->testers2 as $tester)
        {
            $this->testers[$tester] = true;
        }
    }

    function _on_updated()
    {
        //Sync the testers array to member objects
        $this->_update_testers();
        // Sync the object's ACL properties into MidCOM ACL system
        $sync = new org_openpsa_core_acl_synchronizer();
        $sync->write_acls($this, $this->orgOpenpsaOwnerWg, $this->orgOpenpsaAccesstype);
        return true;
    }

    function _on_loaded()
    {
        $this->_get_testers();
        $this->_sync_to_dm2();
        $this->_unserialize_rules();
        if (!is_array($this->rules))
        {
            $this->rules = array();
        }
        return true;
    }

    function _on_creating()
    {
        $this->_sync_from_dm2();
        $this->_serialize_rules();
        return true;
    }

    function _on_updating()
    {
        $this->_sync_from_dm2();
        $this->_serialize_rules();
        return true;
    }

    /**
     * Populates the testers array from memberships
     */
    function _get_testers()
    {
        if (!$this->id)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('This campaign has no id (maybe not created yet?), aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $qb = new midgard_query_builder('org_openpsa_campaign_member');
        $qb->add_constraint('campaign', '=', $this->id);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        $ret = @$qb->execute();
        if (   !is_array($ret)
            || count($ret)==0)
        {
            return;
        }
        //Just to be sure
        $this->testers = array();
        foreach ($ret as $member)
        {
            $this->testers[$member->person] = true;
        }
    }

    /**
     * Updates the database according to the testers array
     */
    function _update_testers()
    {
        if (!$this->id)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('This campaign has no id (maybe not created yet?), aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $old_testers = array();
        $qb = new midgard_query_builder('org_openpsa_campaign_member');
        $qb->add_constraint('campaign', '=', $this->id);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        $ret = @$qb->execute();
        if (   is_array($ret)
            && count($ret)>0)
        {
            foreach ($ret as $member)
            {
                $old_testers[$member->person] = true;
            }
        }
        $added_testers = $this->tester_diff($this->testers, $old_testers);
        $removed_testers = $this->tester_diff($old_testers, $this->testers);
        foreach ($removed_testers as $person => $bool)
        {
            $member = $this->_get_tester_by_personid($person);
            if (is_object($member))
            {
                $member->delete();
            }
        }
        foreach ($added_testers as $person => $bool)
        {
            $member = new org_openpsa_directmarketing_campaign_member();
            $member->person = $person;
            $member->campaign = $this->id;
            $member->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER;
            $member->create();
        }
    }

    /**
     * Utility for diffing associative arrays
     */
    function tester_diff($arr1, $arr2)
    {
        reset($arr1);
        reset($arr2);
        $ret = array();
        foreach($arr1 as $key => $value)
        {
            if (!array_key_exists($key, $arr2))
            {
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    /**
     * Returns org_openpsa_directmarketing_campaign_member object based on the person ID
     */
    function _get_tester_by_personid($id)
    {

        //Find the correct campaign tester by person ID
        $qb = org_openpsa_directmarketing_campaign_member::new_query_builder();
        $qb->add_constraint('campaign', '=', $this->id);
        $qb->add_constraint('person', '=', $id);
        $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        $result = $qb->execute();

        if (!empty($result))
        {
            return $result[0];
        }
    }

    /**
     * Unserializes rulesSerialized to rules
     */
    function _unserialize_rules()
    {
        $unserRet = @unserialize($this->rulesSerialized);
        if ($unserRet === false)
        {
            //Unserialize failed (probably newline/encoding issue), try to fix the serialized string and unserialize again
            $unserRet = @unserialize($this->_fix_serialization($this->rulesSerialized));
            if ($unserRet === false)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Failed to unserialize rulesSerialized', MIDCOM_LOG_WARN);
                debug_pop();
                $this->rules = array();
                return;
            }
        }
        $this->rules = $unserRet;
    }

    /**
     * Serializes rules to rulesSerialized
     */
    function _serialize_rules()
    {
        $this->rulesSerialized = serialize($this->rules);
    }

    /**
     * Fixes newline etc encoding issues in serialized data
     *
     * @param string $data The data to fix.
     * @return string $data with serializations fixed.
     */
    function _fix_serialization($data = null)
    {
        return org_openpsa_helpers::fix_serialization($data);
    }

    /**
     * Creates/Removes members for this smart campaign based on the rules array
     * NOTE: This is highly resource intensive for large campaigns
     * @return boolean indicating success/failure
     */
    function update_smart_campaign_members()
    {
        //Disable limits
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$this->id)
        {
            debug_add('This campaign has no id (maybe not created yet?), aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if ($this->orgOpenpsaObtype != ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART)
        {
            debug_add("This (id #{$this->id}) is not a smart campaign, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $_MIDCOM->auth->request_sudo('org.openpsa.directmarketing');
        $this->parameter('org.openpsa.directmarketing_smart_campaign', 'members_update_failed', '');
        $this->parameter('org.openpsa.directmarketing_smart_campaign', 'members_update_started', time());

        $solver = new org_openpsa_directmarketing_campaign_ruleresolver();
        $rret = $solver->resolve($this->rules);
        if (!$rret)
        {
            $this->parameter('org.openpsa.directmarketing_smart_campaign', 'members_update_failed', time());
            debug_add('Failed to resolve rules', MIDCOM_LOG_ERROR);
            debug_add("this->rules has value:\n===\n" . org_openpsa_helpers::sprint_r($this->rules) . "===\n");
            debug_pop();
            $_MIDCOM->auth->drop_sudo();
            return false;
        }
        $rule_persons =  $solver->execute();
        debug_add("solver->execute() returned with:\n===\n" . org_openpsa_helpers::sprint_r($rule_persons) . "===\n");
        if (!is_array($rule_persons))
        {
            $this->parameter('org.openpsa.directmarketing_smart_campaign', 'members_update_failed', time());
            debug_pop('Failure when executing rules based search', MIDCOM_LOG_ERROR);
            debug_pop();
            $_MIDCOM->auth->drop_sudo();
            return false;
        }

        //Create some useful maps
        $wanted_persons = array();
        $rule_persons_id_map = array();
        foreach ($rule_persons as $person)
        {
            $wanted_persons[] = $person->id;
            $rule_persons_id_map[$person->id] = $person->guid;
        }

        //Delete (normal) members that should not be here anymore
        $qb_unwanted = org_openpsa_directmarketing_campaign_member::new_query_builder();
        $qb_unwanted->add_constraint('campaign', '=', $this->id);
        $qb_unwanted->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER);
        //1.7 does not support IN/NOT IN properly, simulated below
        //$qb_unwanted->add_constraint('person', 'NOT IN', $wanted_persons);
        $qb_unwanted->begin_group('AND');
        foreach ($wanted_persons as $pid)
        {
            $qb_unwanted->add_constraint('person', '<>', $pid);
        }
        $qb_unwanted->end_group();
        $uwret = $qb_unwanted->execute();
        if (   is_array($uwret)
            && !empty($uwret))
        {
            foreach ($uwret as $member)
            {
                debug_add("Deleting unwanted member #{$member->id} (linked to person #{$member->person}) in campaign #{$this->id}");
                $delret = $member->delete();
                if (!$delret)
                {
                    debug_add("Failed to delete unwanted member #{$member->id} (linked to person #{$member->person}) in campaign #{$this->id}, reason: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                }
            }
        }

        //List current non-tester members (including unsubscribed etc), and filter those out of rule_persons
        $qb_current = org_openpsa_directmarketing_campaign_member::new_query_builder();
        $qb_current->add_constraint('campaign', '=', $this->id);
        $qb_current->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        $cret = $qb_current->execute();
        if (   is_array($cret)
            && !empty($cret))
        {
            foreach ($cret as $member)
            {
                //Filter the existing member from rule_persons (if present, for example unsubscribed members might not be)
                if (   !array_key_exists($member->person, $rule_persons_id_map)
                    || !array_key_exists($rule_persons_id_map[$member->person], $rule_persons))
                {
                    continue;
                }
                debug_add("Removing person #{$rule_persons[$rule_persons_id_map[$member->person]]->id} ({$rule_persons[$rule_persons_id_map[$member->person]]->rname}) from rule_persons list, already a member");
                unset($rule_persons[$rule_persons_id_map[$member->person]]);
            }
        }

        //Finally, create members of each person matched by rule left
        reset ($rule_persons);
        foreach ($rule_persons as $person)
        {
            debug_add("Creating new member (linked to person #{$person->id}) to campaign #{$this->id}");
            $member = new org_openpsa_directmarketing_campaign_member();
            $member->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER;
            $member->campaign = $this->id;
            $member->person = $person->id;
            $mcret = $member->create();
            if (!$mcret)
            {
                debug_add("Failed to create new member (linked to person #{$person->id}) in campaign #{$this->id}, reason: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            }
        }

        //All done, set last updated timestamp
        $this->parameter('org.openpsa.directmarketing_smart_campaign', 'members_updated', time());

        $_MIDCOM->auth->drop_sudo();
        return true;
    }

    /**
     * Schedules a background memberships update for a smart campaign
     */
    function schedule_update_smart_campaign_members($time = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$time)
        {
            $time = time();
        }
        if (!$this->id)
        {
            debug_add('This campaign has no id (maybe not created yet?), aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if ($this->orgOpenpsaObtype != ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART)
        {
            debug_add("This (id #{$this->id}) is not a smart campaign, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $_MIDCOM->auth->request_sudo('org.openpsa.directmarketing');
        $stat = midcom_services_at_interface::register($time, 'org.openpsa.directmarketing', 'background_update_campaign_members', array('campaign_guid' => $this->guid));
        if (!$stat)
        {
            debug_add('Failed to register an AT job for members update, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            $_MIDCOM->auth->drop_sudo();
            debug_pop();
            return false;
        }
        $this->parameter('org.openpsa.directmarketing_smart_campaign', 'members_update_scheduled', $time);
        $_MIDCOM->auth->drop_sudo();
        debug_pop();
        return true;
    }

    /**
     * Checks the parameters related to members update and returns string describing status or false if this is not
     * a smart campaign.
     * For example:
     *  - Running (started on yyyy-mm-dd H:i)
     *  - Last run on yyyy-mm-dd H:i
     *  - Last run on --, next scheduled run on --
     *  - Last run failed on --, last successful run on --
     */
    function members_update_status()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$this->id)
        {
            debug_add('This campaign has no id (maybe not created yet?), aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if ($this->orgOpenpsaObtype != ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART)
        {
            debug_add("This (id #{$this->id}) is not a smart campaign, aborting", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        //TODO
        return false;
    }

}

/**
 * Another wrap level
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_campaign extends midcom_org_openpsa_campaign
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }
}

?>