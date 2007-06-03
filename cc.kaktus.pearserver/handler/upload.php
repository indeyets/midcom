<?php
/**
 * @package cc.kaktus.pearserver
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: viewer.php 4368 2006-10-20 07:47:46Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * PEAR server handler class for viewing the welcome screen
 * 
 * @package cc.kaktus.pearserver
 */
class cc_kaktus_pearserver_handler_upload extends midcom_baseclasses_components_handler
{
    /**
     * DM2 Controller instance
     * 
     * @access private
     * @var midcom_helper_datamanager2_controller;
     */
    var $_controller = null;
    
    /**
     * Release object
     * 
     * @access private
     * @var org_openpsa_products_product_dba
     */
    var $_product = null;
    
    /**
     * Constructor. Ties to the parent class constructor.
     * 
     * @access public
     */
    function cc_kaktus_pearserver_handler_upload()
    {
        parent::midcom_baseclasses_components_handler();
        $this->_root_group =& $this->_request_data['root_group'];
    }
    
    /**
     * Loads the DM2 create controller instance
     * 
     * @access private
     * @return boolean Indicating success
     */
    function _load_controller()
    {
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb = $this->_request_data['schemadb'];
        $this->_controller->schemaname = 'upload';
        $this->_controller->callback_object =& $this;
        
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
        
        return true;
    }
    
    /**
     * DM2 creation callback, binds to the topic PEAR group
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_request_data['release'] = new org_openpsa_products_product_dba();
        $this->_request_data['release']->productGroup = $this->_request_data['root_group']->id;

        if (! $this->_request_data['release']->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_request_data['release']);
            debug_pop();
            
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to upload a new release, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_request_data['release'];
    }
    
    /**
     * Handle the uploading interface
     * 
     * @access public
     * @return boolean Indicating success
     */
    function _handler_upload($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:update');
        $this->_load_controller();
        
        switch ($this->_controller->process_form())
        {
            case 'cancel':
                $_MIDCOM->relocate('');
                // This will exit
                break;
            
            case 'save':
                $_MIDCOM->relocate("process/{$this->_request_data['release']->guid}/");
                // This will exit
        }
        
        return true;
    }
    
    /**
     * Show the upload form
     * 
     * @access public
     */
    function _show_upload($handler_id, &$data)
    {
        $data['controller'] =& $this->_controller;
        midcom_show_style('package-upload');
    }
}
?>