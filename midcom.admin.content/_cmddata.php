<?php

class midcom_admin_content__cmddata {
    
    var $_argv;
    var $_contentadm;
    var $_component;
    var $_path;
    var $_topic;
    
    function midcom_admin_content__cmddata ($argv, &$contentadm) {
        $loader =& $GLOBALS["midcom"]->get_component_loader();

        $this->_argv = $argv;
        $this->_contentadm = &$contentadm;
        $this->_path = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_COMPONENT);
        $this->_component =& $loader->get_contentadmin_class($this->_path);
        $this->_topic = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
    }


    function execute () {
        global $midcom;

        debug_push("Content Admin, Data Command");
                
        $context = $this->_contentadm->viewdata["context"];

        $config = new midcom_helper_configuration($this->_topic, $this->_path);
        if (! $config) 
        {
            debug_add("No custom configuration data found");
            $config = Array();
        } 
        else 
        {
            $config = $config->get_all();
        }
        
        if (! $this->_component->configure($config, $context, true)) 
        {
            debug_add("Data Component configuration was unsuccessful.");
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            $this->_contentadm->errstr = $this->_component->errstr($context);
            debug_pop();
            return false;
        }
        
        if (! $this->_component->can_handle($this->_topic, count($this->_argv), $this->_argv, $context) ) 
        {
            debug_add("Data Component declared unable to handle the request.");
            $this->_contentadm->errcode = MIDCOM_ERRCRIT;
            $this->_contentadm->errstr = $this->_component->errstr($context);
            debug_pop();
            return false;
        }
        
        debug_add("Data Component Configured and ready to handle the request.");

        if (! $this->_component->handle($this->_topic, count($this->_argv), $this->_argv, $context)) 
        {
            debug_add("Data Component failed to handle the request.");
            $this->_contentadm->errcode = $this->_component->errcode($context);
            $this->_contentadm->errstr = $this->_component->errstr($context);
            debug_pop();
            return false;
        }

        debug_add("Data Component successfully handled the request.");

        // Retrieve Metadata
        $nav = new midcom_helper_nav();
        if ($nav->get_current_leaf() === false)
        {
            $meta = $nav->get_node($nav->get_current_node());
        }
        else
        {
            $meta = $nav->get_leaf($nav->get_current_leaf());
        }
		$this->_context[$this->_contentadm->_context][MIDCOM_META_CREATOR] = $meta[MIDCOM_META_CREATOR];
        $this->_context[$this->_contentadm->_context][MIDCOM_META_EDITOR] = $meta[MIDCOM_META_EDITOR];
        $this->_context[$this->_contentadm->_context][MIDCOM_META_CREATED] = $meta[MIDCOM_META_CREATED];
        $this->_context[$this->_contentadm->_context][MIDCOM_META_EDITED] = $meta[MIDCOM_META_EDITED];
        
        debug_pop();
        return true;
    }

    function show () {
        global $midcom;
        debug_push("Content Admin, Data Command");

        debug_add("Executing Show");
        debug_add("Context is " . $this->_contentadm->viewdata["context"]);
        ob_start();
        $this->_component->show_content($this->_contentadm->viewdata["context"]);
        $midcom->_set_context_data(ob_get_contents(), MIDCOM_CONTEXT_OUTPUT);
        ob_end_flush();
        debug_add("Complete");        
    }
    
}

?>
