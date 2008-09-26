<?php
/**
 * @package fi.protie.host
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 12672 2007-10-05 11:57:32Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * List hosts of this sitegroup
 *
 * @package fi.protie.host
 */
class fi_protie_host_handler_list extends midcom_baseclasses_components_handler
{
    /**
     * Hosts list
     * 
     * @access private
     * @var Array
     */
    private $_hosts = array();
    
    /**
     * Datamanager2 instance
     * 
     * @access private
     * @var midcom_helper_datamanager2_datamanager
     */
    private $_datamanager = null;
    
    function __construct()
    {
        parent::__construct();
    }
    
    function _handler_list($handler_id, $args, &$data)
    {
        $qb = midcom_db_host::new_query_builder();
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb->add_order('metadata.score', 'DESC');
        $qb->add_order('name');
        
        $this->_hosts = $qb->execute();
        
        // Load the datamanager
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($data['schemadb']);
        
        return true;
    }
    
    function _show_list($handler_id, &$data)
    {
        midcom_show_style('host-list-start');
        
        foreach ($this->_hosts as $host)
        {
            $this->_datamanager->autoset_storage($host);
            
            $data['host'] =& $host;
            $data['datamanager'] =& $this->_datamanager;
            $data['view_host'] = $this->_datamanager->get_content_html();
            
            midcom_show_style('host-list-item');
        }
        
        midcom_show_style('host-list-end');
    }
}
?>