<?php
/**
 * @package fi.protie.host
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: interfaces.php 12672 2007-10-05 11:57:32Z adrenalin $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Create a new host
 *
 * @package fi.protie.host
 */
class fi_protie_host_handler_create extends midcom_baseclasses_components_handler
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
    
    /**
     * DM2 creation callback, bind to the current content topic.
     * 
     * @access public
     * @param midcom_helper_datamanager2_controller_create &$controller
     * @return fi_protie_shop_item_dba
     */
    function & dm2_create_callback (&$controller)
    {
        if (!$this->_host->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_host);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new item, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }
        
        return $this->_host;
    }
    
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_host = new midcom_db_host();
        
        // Load the datamanager controller
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->set_storage($this->_host);
        $this->_controller->callback_object =& $this;
        
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
        
        $tmp = array();
        
        // Page title
        $title = sprintf($this->_l10n->get('create host'), "{$this->_host->name}{$this->_host->prefix}");
        $_MIDCOM->set_pagetitle($title);
        
        $tmp[] = array
        (
            MIDCOM_NAV_URL => "create/",
            MIDCOM_NAV_NAME => $title,
        );
        
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        
        return true;
    }
    
    function _show_create($handler_id, &$data)
    {
        $data['host'] =& $this->_host;
        $data['controller'] =& $this->_controller;
        midcom_show_style('host-create');
    }
}
?>