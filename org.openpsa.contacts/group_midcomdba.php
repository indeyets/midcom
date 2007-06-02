<?php

// TODO Hotfix
class midcom_org_openpsa_organization extends __midcom_org_openpsa_organization
{
    function midcom_org_openpsa_organization($id = null)
    {
        return parent::__midcom_org_openpsa_organization($id);
    }
    
    function get_label()
    {
    	if ($this->official)
    	{
    		$label = $this->official;
    	}
    	else
    	{
    		$label = $this->name;
    	}
    	
    	return $label;
    }
}

/**
 * Wrapper group
 */
class org_openpsa_contacts_group extends midcom_org_openpsa_organization
{
    var $members = array();
    var $members_loaded = false;

    function org_openpsa_contacts_group($identifier=NULL)
    {
        return parent::midcom_org_openpsa_organization($identifier);
    }

    function get_parent_guid_uncached()
    {
        // FIXME: Midgard Core should do this
        if ($this->owner != 0)
        {
            $parent = new org_openpsa_contacts_group($this->owner);
            return $parent->guid;
        }
        else
        {
            return null;
        }
    }

    function _on_loaded()
    {
        if (   !array_key_exists('org_openpsa_contacts_group_autoload_members', $GLOBALS)
            || !empty($GLOBALS['org_openpsa_contacts_group_autoload_members']))
        {
            $this->_get_members();
        }

        if (empty($this->official))
        {
            if (!empty($this->name))
            {
                $this->official = $this->name;
            }
            else
            {
                $this->official = "Group #{$this->id}";
            }
        }

        return parent::_on_loaded();
    }

    function _on_creating()
    {
        //Make sure we have accessType
        if (!$this->orgOpenpsaAccesstype)
        {
            $this->orgOpenpsaAccesstype = ORG_OPENPSA_ACCESSTYPE_PUBLIC;
        }
        return parent::_on_creating();
    }

    function _on_updating()
    {
        $this->_update_members();

        if ($this->homepage)
        {
            // This group has a homepage, register a prober
            $args = array
            (
                'group' => $this->guid,
            );
            $atstat = midcom_services_at_interface::register(time() + 60, 'org.openpsa.contacts', 'check_url', $args);
        }

        return parent::_on_updating();
    }

    function _get_members_array()
    {
        $members = array();
        $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_db_member');
        $qb->add_constraint('gid', '=', $this->id);
        $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
        if (count($ret) > 0)
        {
            foreach ($ret as $member)
            {
                $members[$member->uid] = true;
            }
        }
        return $members;
    }

    function _get_members()
    {
        $this->members = $this->_get_members_array();
        $this->members_loaded = true;
    }

    function _update_members()
    {
        if (   !$this->members_loaded
            && count($this->members) == 0)
        {
            $this->_get_members();
        }
        $old_members = $this->_get_members_array();

        // Add new members
        foreach ($this->members as $member_id => $status)
        {
            if (!array_key_exists($member_id, $old_members))
            {
                $member = new midcom_db_member();
                $member->uid = $member_id;
                $member->gid = $this->id;
                $member->create();
            }
        }

        // Delete removed members
        foreach ($old_members as $member_id => $status)
        {
            if (!array_key_exists($member_id, $this->members))
            {
                $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_db_member');
                $qb->add_constraint('gid', '=', $this->id);
                $qb->add_constraint('uid', '=', $member_id);
                $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
                if (count($ret) > 0)
                {
                    foreach ($ret as $member)
                    {
                        $member->delete();
                    }
                }
            }
        }
    }
}
?>