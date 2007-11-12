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
