<?php
/**
 * @package fi.protie.host
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 12672 2007-10-05 11:57:32Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Edit the requested host
 *
 * @package fi.protie.host
 */
class fi_protie_host_handler_edit extends midcom_baseclasses_components_handler
{
    /**
     * Host
     * 
     * @access private
     * @var midcom_db_host
     */
    private $_host;
    
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
    
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_host = new midcom_db_host($args[0]);
        
        if (   !$this->_host
            || !$this->_host->guid)
        {
            return false;
        }
        
        // Load the datamanager controller
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->set_storage($this->_host);
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for item {$this->_item->id}.");
            // This will exit.
        }
        
        switch ($this->_controller->process_form())
        {
            case 'save':
                if ($this->_config->get('page'))
                {
                    $this->_host->page = $this->_config->get('page');
                }
                
                $this->_host->update();
                // Fall through
            
            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit
        }
        
        // Bind the object to view toolbar
        $_MIDCOM->bind_view_to_object($this->_host, $data['schemadb']);
        
        $tmp = array();
        
        // Page title
        $title = sprintf($this->_l10n->get('edit %s'), "{$this->_host->name}{$this->_host->prefix}");
        $_MIDCOM->set_pagetitle($title);
        
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "edit/{$this->_host->guid}/",
            MIDCOM_NAV_NAME => $title,
        );
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        
        return true;
    }
    
    function _show_edit($handler_id, &$data)
    {
        $data['host'] =& $this->_host;
        $data['controller'] =& $this->_controller;
        midcom_show_style('host-edit');
    }
}
?>