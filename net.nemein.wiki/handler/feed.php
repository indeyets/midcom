<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wikipage feed handler
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_feed extends midcom_baseclasses_components_handler
{
    function net_nemein_wiki_handler_feed() 
    {
        parent::midcom_baseclasses_components_handler();       
    }

    function _handler_rss($handler_id, $args, &$data)
    {   
        $nap = new midcom_helper_nav();
        $data['node'] = $nap->get_node($nap->get_current_node());
        
        $_MIDCOM->cache->content->content_type("text/xml");
        $_MIDCOM->header("Content-type: text/xml; charset=UTF-8");
        
        $_MIDCOM->skip_page_style = true;
        
        $data['rss_creator'] = new UniversalFeedCreator();
        $data['rss_creator']->title = $data['node'][MIDCOM_NAV_NAME];
        $data['rss_creator']->link = $data['node'][MIDCOM_NAV_FULLURL];
        $data['rss_creator']->syndicationURL = "{$data['node'][MIDCOM_NAV_FULLURL]}rss.xml";
        $data['rss_creator']->cssStyleSheet = false;
        
        return true;
    }
    
    function _show_rss($handler_id, &$data)
    {
        $qb = net_nemein_wiki_wikipage::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_order('metadata.revised', 'DESC');
        $qb->set_limit($this->_config->get('rss_count'));
        $result = $qb->execute();        
        
        foreach ($result as $wikipage)
        {
            $author = new midcom_db_person($wikipage->revisor);
            $item = new FeedItem();
            $item->title = $wikipage->title;
            $item->link = "{$data['node'][MIDCOM_NAV_FULLURL]}{$wikipage->name}/";
            $item->date = $wikipage->revised;
            $item->author = $author->name;
            $item->description = Markdown(preg_replace_callback($this->_config->get('wikilink_regexp'), array($wikipage, 'replace_wikiwords'), $wikipage->content));
            $data['rss_creator']->addItem($item);
        }      
        $data['rss'] = $data['rss_creator']->createFeed('RSS2.0');
    
        echo $data['rss'];
    }    
}
?>
