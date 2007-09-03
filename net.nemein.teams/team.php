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
}

?>
