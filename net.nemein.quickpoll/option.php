<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
 
class net_nemein_quickpoll_option_dba extends __net_nemein_quickpoll_option_dba
{
    function net_nemein_quickpoll_option_dba($id = null)
    {
        return parent::__net_nemein_quickpoll_option_dba($id);
    }
    
    function get_parent_guid_uncached()
    {
        // FIXME: Midgard Core should do this
        if ($this->article != 0)
        {
            $parent = new midcom_db_article($this->article);
            return $parent;
        }
        else
        {
            return null;
        }
    }
 }
?>