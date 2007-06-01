<?php
/**
 * @package de.linkm.sitemap
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Sitemap MidCOM Viewer class.
 * 
 * @package de.linkm.sitemap
 */
class de_linkm_sitemap_viewer extends midcom_baseclasses_components_request
{
    var $_nav;            // midcom_helper_nav reference
    var $_current_node;   // ID of current node we're in  
    var $_root_node_id;   // ID of the root node to use  

    function de_linkm_sitemap_viewer($object, $config) 
    {
        parent::midcom_baseclasses_components_request($object, $config);
        $this->_current_node = null;
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        // Google Sitemap Mode creates an XML output of the sitemap. Configuration switch
        // defines whether we should show the sitemap as content or 
        if ($this->_config->get('google_sitemap_mode'))
        {
            $this->_request_switch['sitemap'] = Array 
            ( 
                'handler'      => array ('de_linkm_sitemap_handler_sitemap', 'xml'),
            );
        }
        else
        {
            $this->_request_switch['sitemap'] = Array 
            ( 
                'handler'      => array ('de_linkm_sitemap_handler_sitemap', 'sitemap'),
            );
            
            $this->_request_switch['xml_sitemap'] = array
            (
                'handler'      => array ('de_linkm_sitemap_handler_sitemap', 'xml'),
                'fixed_args'   => array ('sitemap.xml'),
            );
        }
        
        // Match /config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/de/linkm/sitemap/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );
    }
    
    /**
     * Get the list of root nodes
     * 
     * @access static public
     */
    function list_root_nodes()
    {
        $nap = new midcom_helper_nav();
        
        $root_nodes = array();
        $root_nodes[''] = '';
        
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $nap->get_root_node());
        $qb->add_order('score');
        $qb->add_order('name');
        $nodes = $qb->execute();
        
        foreach ($nodes as $node)
        {
            $root_nodes[$node->guid] = $node->extra;
        }
        
        return $root_nodes;
    }
}
?>
