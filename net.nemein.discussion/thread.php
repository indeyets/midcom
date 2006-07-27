<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
 
class net_nemein_discussion_thread_dba extends __net_nemein_discussion_thread_dba
{
    function net_nemein_discussion_thread_dba($id = null)
    {
        return parent::__net_nemein_discussion_thread_dba($id);
    }
    
    function get_parent_guid_uncached()
    {
        // FIXME: Midgard Core should do this
        if ($this->node != 0)
        {
            $parent = new midcom_db_topic($this->node);
            return $parent;
        }
        else
        {
            return null;
        }
    }
    
    function _on_updating()
    {
        $qb = net_nemein_discussion_thread_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->node);
        $qb->add_constraint('posts', '>', 0);
        $qb->add_constraint('name', '=', $this->name);
        $qb->add_constraint('id', '<>', $this->id);
        $result = $qb->execute();
        if (count($result) > 0)
        {
            // There is already a thread with this URL name
            return false;
        }
        
        return true;
    }  
}
?>
