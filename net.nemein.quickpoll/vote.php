<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
 
class net_nemein_quickpoll_vote_dba extends __net_nemein_quickpoll_vote_dba
{
    function net_nemein_quickpoll_vote_dba($id = null)
    {
        return parent::__net_nemein_quickpoll_vote_dba($id);
    }
    
    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        if ($this->user)
        {
            return $this->user;
        }
        return $this->ip;
    }
 }
?>