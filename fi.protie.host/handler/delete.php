<?php
/**
 * @package fi.protie.host
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 12672 2007-10-05 11:57:32Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Delete the requested host
 *
 * @package fi.protie.host
 */
class fi_protie_host_handler_delete extends midcom_baseclasses_components_handler
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
    
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_host = new midcom_db_host($args[0]);
        
        if (   !$this->_host
            || !$this->_host->guid)
        {
            return false;
        }
        
        if (isset($_POST['f_delete']))
        {
            // Prevent Midgard from using a deleted host by ignoring metadata.deleted
            $this->_host->online = 0;
            $this->_host->update();
            
            // Delete the host
            $this->_host->delete();
            
            $_MIDCOM->uimessages->add($this->_l10n->get('fi.protie.host'), $this->_l10n->get('host deleted'));
            $_MIDCOM->relocate('');
            // This will exit
        }
        
        if (isset($_POST['f_cancel']))
        {
            $_MIDCOM->uimessages->add($this->_l10n->get('fi.protie.host'), $this->_l10n->get('cancelled'));
            $_MIDCOM->relocate('');
            // This will exit
        }
        
        // Load the datamanager
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($data['schemadb']);
        
        return true;
    }
    
    function _show_delete($handler_id, &$data)
    {
        $this->_datamanager->autoset_storage($this->_host);
        
        $data['host'] =& $this->_host;
        $data['datamanager'] =& $this->_datamanager;
        $data['view_host'] = $this->_datamanager->get_content_html();
        
        midcom_show_style('host-delete');
    }
}
?>