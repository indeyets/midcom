<?php
/**
 * Created on Jan 7, 2006
 * @author tarjei huse
 * @package midcom.admin.content2
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */

/**
 * This class handles the admin requests for a components leaves.
 * @package aegir.admin.content2
 */

class midcom_admin_content2_component extends midcom_baseclasses_components_handler 
{
    
    /**
     * Component config
     */
    var $_config = null;
    
    /**
     * The component to run
     */
    var $_component_name = null;
    
    /**
     * Contextswitcher 
     * @var midcom_admin_content2_context
     * @access public
     */
    var $_context = null;
    
    
    /**
     * The topic beeing worked on
     */
    var $_topic = null;
    
    /**
     * The component responsible for the output
     */
    var $_component = null;
    
    var $errcode;
    var $errstr;
    
    
    function midcom_admin_content2_component() 
    {
        parent::midcom_baseclasses_components_handler();
        
    }
    
    function _on_initialize() 
    {
        // Populate the request data with references to the class members we might need
        
        
        if (array_key_exists('aegir_interface',$this->_request_data)) {
            
            $this->_config = &$this->_request_data['aegir_interface']->get_handler_config($this->_component_name); 
        }
        /** @todo handle the else case */
        $this->_request_data['toolbars']    = & midcom_helper_toolbars::get_instance();
        require_once 'context.php';
        $this->_context = new midcom_admin_content2_context();     
        $this->_request_data['context'] = $this->_context->contextid;   
        
    }
    
    function _handler_component($handler_id, $args, &$data)  
    {
        $this->_topic = new midcom_db_topic($args[0]);
        
        if (array_key_exists('aegir_interface',$this->_request_data)) 
        {
            //$nav = & $this->_request_data['aegir_interface']->get_navigation();
            //$nav->set_current_node($this->_topic->id);
            
            $this->_request_data['aegir_interface']->set_current_node($this->_topic->id);
            $this->_request_data['aegir_interface']->prepare_toolbar();
        }
        array_shift($args);
        array_shift($args);
        
        $this->_context->set_content_topic(&$this->_topic);
        $this->_context->set_admincontext();
        /**
         * to remember: what does these do:
         * $this->viewdata["adminprefix"] = $_MIDCOM->get_context_data($this->_mycontext, MIDCOM_CONTEXT_ANCHORPREFIX);
         * $this->viewdata["admintopicprefix"] = $this->viewdata["adminprefix"] . $this->_topic->id . "/";
         * $this->viewdata["adminmode"] = $mode;
         * (as in: are they needed? (most probably...)
         */
        $this->_component_name =  $this->_topic->parameter("midcom","component") ;
        
        $this->_context->set_contextparam(MIDCOM_CONTEXT_COMPONENT, $this->_component_name);
        $this->_context->set_contextparam(MIDCOM_CONTEXT_REQUESTTYPE, MIDCOM_REQUEST_CONTENTADM);
        
        
        $this->_context->set_contextparam(MIDCOM_CONTEXT_ANCHORPREFIX,
            $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 
            $this->_master->get_prefix() .  "{$this->_topic->id}/data/" );
        //$_MIDCOM->_set_context_data("admin", MIDCOM_CONTEXT_SUBSTYLE);
        
        $this->_context->set_admincontext();
        $this->_context->enter_context();
        
        $toolbars =& midcom_helper_toolbars::get_instance();
        /*
        $GLOBALS['midcom_admin_content_toolbar_component'] = & $toolbars->bottom;
        $GLOBALS['midcom_admin_content_toolbar_main'] = & $toolbars->top;
        $GLOBALS['midcom_admin_content_toolbar_meta'] = & $toolbars->bottom;
        */
        $loader =& $_MIDCOM->get_component_loader();
        $this->_component =& $loader->get_contentadmin_class($this->_component_name);

        debug_push("Content Admin, Data Command");
        
        
        
        
        $config = new midcom_helper_configuration($this->_topic, $this->_component_name);
        
        if (! $config) 
        {
            debug_add("No custom configuration data found");
            $config = Array();
        } 
        else 
        {
            $config = $config->get_all();
        }
        
        if (! $this->_component->configure($config, $this->_context->get_current_context(), true)) 
        {
            debug_add("Data Component configuration was unsuccessful.");
            //$this->_contentadm->errcode = MIDCOM_ERRCRIT;
            //$this->_contentadm->errstr = $this->_component->errstr($this->_context);
            debug_pop();
            return false;
        }
        
        
        if (! $this->_component->can_handle($this->_topic, count($args), $args, $this->_context->get_current_context()) ) 
        {
            debug_add("Data Component declared unable to handle the request.");
            debug_print_r("Args:" , $args);
            //$this->_contentadm->errcode = MIDCOM_ERRCRIT;
            //$this->_contentadm->errstr = $this->_component->errstr($context);
            debug_pop();
            return false;
        }
        
        debug_add("Data Component Configured and ready to handle the request.");
        
        if (! $this->_component->handle($this->_topic, count($args), $args, $this->_context->get_current_context())) 
        {
            debug_add("Data Component failed to handle the request.");
            //$this->_contentadm->errcode = $this->_component->errcode($this->_context->get_current_context());
            //$this->_contentadm->errstr = $this->_component->errstr($context);
            debug_pop();
            return false;
        }

        debug_add("Data Component successfully handled the request.");

        // Retrieve Metadata
        $nav = new midcom_helper_nav($this->_context->get_current_context());
        if ($nav->get_current_leaf() === false)
        {
            $meta = $nav->get_node($nav->get_current_node());
        }
        else
        {
            $meta = $nav->get_leaf($nav->get_current_leaf());
        }
        $this->_context->set_contextparam(MIDCOM_META_CREATOR , $meta[MIDCOM_META_CREATOR]);
        $this->_context->set_contextparam(MIDCOM_META_EDITOR , $meta[MIDCOM_META_EDITOR]);
        $this->_context->set_contextparam(MIDCOM_META_CREATED , $meta[MIDCOM_META_CREATED]);
        $this->_context->set_contextparam(MIDCOM_META_EDITED , $meta[MIDCOM_META_EDITED]);
        $this->_context->leave_context();
        debug_pop();
        return true;
    }
    
    function _show_component() {
        
        debug_push_class(__CLASS__,__FUNCTION__);
        
        debug_add("Context is " . $this->_context->get_current_context());
        
        $this->_context->enter_context();
        
        $_MIDCOM->style->enter_context($this->_context->get_current_context());

        //ob_start();
        $this->_component->show_content($this->_context->get_current_context());
        
        //$_MIDCOM->_set_context_data( ob_get_contents() , MIDCOM_CONTEXT_OUTPUT);
        //ob_end_flush();
        
        $_MIDCOM->style->leave_context();
        $this->_context->leave_context();
        
        debug_add("Current context: " . $_MIDCOM->_currentcontext );
        
        return true;        
    }    
}
 