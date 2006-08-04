<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
class midcom_org_openpsa_campaign_member extends __midcom_org_openpsa_campaign_member
{
    function midcom_org_openpsa_campaign_member($id = null)
    {
        $ret = parent::__midcom_org_openpsa_campaign_member($id);
        if (!$this->orgOpenpsaObtype)
        {
            $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER;
        }
        return $ret;
    }

    function get_parent_guid_uncached()
    {
        // FIXME: Midgard Core should do this
        if ($this->campaign != 0)
        {
            $parent = new org_openpsa_directmarketing_campaign($this->campaign);
            return $parent;
        }
        else
        {
            return null;
        }
    }     

    /**
     * Checks for duplicate memberships returns true for NO duplicate memberships
     */     
    function _check_duplicate_membership()
    {
        $qb = new MidgardQueryBuilder('org_openpsa_campaign_member');
        $qb->add_constraint('person', '=', $this->person);
        $qb->add_constraint('campaign', '=', $this->campaign);
        //For tester membership check only other tester memberships for duplicates, for other memberships check all BUT testers
        if ($this->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER)
        {
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        }
        else
        {
            $qb->add_constraint('orgOpenpsaObtype', '<>', ORG_OPENPSA_OBTYPE_CAMPAIGN_TESTER);
        }
        if ($this->id)
        {
            $qb->add_constraint('id', '<>', $this->id);
        }
        $ret = @$qb->execute();
        if ($ret === false)
        {
            //Failure in execute, return false to be safe
            return false;
        }
        if (   is_array($ret)
            && count($ret)>0)
        {
            //We already have a membership with the same campaign and person
            return false;
        }
        return true;
    }
    
    function _on_creating()
    {
        return $this->_check_duplicate_membership();
    }
    
    function _on_updating()
    {
        return $this->_check_duplicate_membership();
    }

    /**
     * Substitutes magic strings in content with values from the membership
     * and/or the person.
     */
    function personalize_message($content, $message_type=-1, $person=false, $node=false)
    {
        if (!$node)
        {
            $nap = new midcom_helper_nav();
            $node = $nap->get_node($nap->get_current_node());
        }
        if (!is_object($person))
        {
            $person = new org_openpsa_contacts_person($this->person);
        }
        //TODO: All kinds of string substitutions, remember to check message type before mangling if only applies to certain types
        //Unsubscribe URL
        $content = str_replace('<UNSUBSCRIBE_URL>', $this->get_unsubscribe_url($node, $person), $content);
        //Unsubscribe from all URL
        $content = str_replace('<UNSUBSCRIBE_ALL_URL>', "{$node[MIDCOM_NAV_FULLURL]}campaign/unsubscribe_all/{$person->guid}.html", $content);
        //General membership GUID
        $content = str_replace('<MEMBER_GUID>', $this->guid, $content);
        //General person GUID
        $content = str_replace('<PERSON_GUID>', $person->guid, $content);
        //Firstname
        $content = str_replace('<FNAME>', $person->firstname, $content);
        //Lastname
        $content = str_replace('<LNAME>', $person->lastname, $content);
        
        return $content;
    }
    
    function get_unsubscribe_url($node=false, $person=false)
    {
        if (!$node)
        {
            $nap = new midcom_helper_nav();
            $node = $nap->get_node($nap->get_current_node());
        }
        if (!is_object($person))
        {
            $person = new org_openpsa_contacts_person($this->person);
        }
        return "{$node[MIDCOM_NAV_FULLURL]}campaign/unsubscribe/{$this->guid}.html";
    }
    
    /**
     * Creates a message receipt of type.
     */
    function create_receipt($message_id, $type, $token = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);   
        $receipt = new org_openpsa_directmarketing_campaign_message_receipt();
        $receipt->orgOpenpsaObtype = $type;
        $receipt->person = $this->person;
        $receipt->message = $message_id;
        $receipt->token = $token;
        $receipt->timestamp = time();
        //mgd_debug_start();
        $stat = $receipt->create();
        //mgd_debug_stop();
        //PONDER: do something in case of failure ?
        if (!$stat)
        {
            debug_add('Failed to create, errstr: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
        }
        debug_pop();
        return $stat;
    }
} 
 
class org_openpsa_directmarketing_campaign_member extends midcom_org_openpsa_campaign_member
{
    function org_openpsa_directmarketing_campaign_member($id = null)
    {
        return parent::midcom_org_openpsa_campaign_member($id);
    }
}

?>
