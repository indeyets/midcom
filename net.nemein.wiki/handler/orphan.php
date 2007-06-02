<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: orphan.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wiki orphan handler
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_orphan extends midcom_baseclasses_components_handler
{
    function net_nemein_wiki_handler_orphan() 
    {
        parent::midcom_baseclasses_components_handler();       
    }

    function _handler_orphan($handler_id, $args, &$data)
    {
        $data['wiki_name'] = $this->_topic->extra;

        $_MIDCOM->set_pagetitle(sprintf($this->_l10n->get('orphaned pages in wiki %s'), $data['wiki_name'])); 
        $this->_node_toolbar->hide_item('orphans/');
        
        $data['orphans'] = array();
        $qb = net_nemein_wiki_wikipage::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_constraint('name', '<>', 'index');
        $qb->add_order('name');
        $wikipages = $qb->execute();  
        
        foreach ($wikipages as $wikipage)
        {
            $link_qb = net_nemein_wiki_link_dba::new_query_builder();
            $link_qb->add_constraint('topage', '=', $wikipage->title);
            $links = $link_qb->count_unchecked();
            
            if ($links == 0)
            {
                $data['orphans'][] = $wikipage;
            }
        }
        
        return true;
    }
    
    function _show_orphan($handler_id, &$data)
    {
        midcom_show_style('view-orphans');
    }    
}
?>
