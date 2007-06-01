<?php
/**
 * MidCOM wrapped class for access to stored queries
 */
class net_nemein_rss_feed_dba extends __net_nemein_rss_feed_dba
{
    function net_nemein_rss_feed_dba($id = null)
    {
        return parent::__net_nemein_rss_feed_dba($id);
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
    
    function _on_loaded()
    {
        if (   $this->title == ''
            && $this->id)
        {
            $this->title = "Feed #{$this->id}";
        }
        
        return parent::_on_loaded();
    }
}
?>
