<?php

class net_nemein_teams_team_dba extends __net_nemein_teams_team_dba
{

    function net_nemein_teams_team_dba($src = null)
    {
        parent::__net_nemein_teams_team_dba($src);
    }
    
    function count_members()
    {
        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('gid.guid', '=', $this->groupguid);
        $count = $qb->count();
        
        return $count;
    }
    
    /**
     * DBA magic defaults which assign write privileges for all USERS, so that they can freely
     * create mails without the need to sudo of the component. Also, we deny read unconditionally,
     * as read privileges are set during creation for the sender, and are inherited from the
     * mailbox for the receiver.
     */
    function get_class_magic_default_privileges()
    {
        return Array (
            'EVERYONE' => Array('midgard:read' => MIDCOM_PRIVILEGE_ALLOW),
            'ANONYMOUS' => Array(),
            'USERS' => Array('midgard:create' => MIDCOM_PRIVILEGE_ALLOW),
        );
    }
}

?>
