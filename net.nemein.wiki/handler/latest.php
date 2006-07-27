<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wikipage latest handler
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_latest extends midcom_baseclasses_components_handler
{
    function net_nemein_wiki_handler_latest() 
    {
        parent::midcom_baseclasses_components_handler();       
    }

    function _handler_latest($handler_id, $args, &$data)
    {   
        $this->_request_data['latest_pages'] = Array();
        $qb = net_nemein_wiki_wikipage::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_order('revised', 'DESC');
        $qb->set_limit($this->_config->get('latest_count'));
        $result = $qb->execute();        
        
        foreach ($result as $wikipage)
        {
            $this->_request_data['latest_pages'][] = $wikipage;
        }
        return true;
    }
    
    function _show_latest($handler_id, &$data)
    {
        $this->_request_data['wikiname'] = $this->_topic->extra;    
        if (count($this->_request_data['latest_pages']) > 0)
        {
            midcom_show_style('view-latest-header');
            
            foreach ($this->_request_data['latest_pages'] as $wikipage)
            {
                $this->_request_data['wikipage'] =& $wikipage;
                midcom_show_style('view-latest-item');
            }
            
            midcom_show_style('view-latest-footer');
        }
    }    
}
?>
