<?php


$_MIDCOM->componentloader->load('midcom.admin.aegir');
/**
 * the Aegir handler for this module.
 * @package midcom.admin.ais
 */ 
class midcom_admin_content2_aegir extends midcom_admin_aegir_module {


    function midcom_admin_content2_aegir ()
    { 
        parent::midcom_admin_aegir_module ();
        // host is always the first of the variable arguments if it exists. if not the first is 0.
        if ($this->_argv[0] != 0) {
            $this->get_navigation();
            $this->_nav->_host = new midcom_host();
            $this->_nav->_host->get_by_id($this->_argv[0]);
            
        }
    }
    
    function prepare_toolbar() {
        $request_data =& $_MIDCOM->get_custom_context_data('request_data');
        $nav = &$this->get_navigation();
        $node = $nav->get_node($nav->get_current_node());
        
        if (!$node) 
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "The current handler has not set the current node as i t should.");
        }
        
        $topic = & $node[MIDCOM_NAV_OBJECT];
        $current_topic = $topic->id;
        
        $toolbar = &midcom_helper_toolbars::get_instance();
        
       // this should be in article.php. 
        if (0) foreach (array_reverse($this->_schemadb_article_index, true) as $name => $desc) 
        { 
            $text = sprintf($request_data["l10n_midcom"]->get('create %s'), $desc);
            $toolbar->top->add_item(
                Array 
                (
                    MIDCOM_TOOLBAR_URL => "ais/create/{$current_topic}/topic.html", 
                    MIDCOM_TOOLBAR_LABEL => $text,
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $topic) == false)
                ), 0);
        }
    
        // topic stuff
         
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "ais/topic/create/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("create subtopic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $topic)
               && $_MIDCOM->auth->can_do('midgard:create', $topic)
              )
            
        ));
        if (0) {
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL =>  "ais/topic/configure/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n_midcom"]->get('component configuration'),
            MIDCOM_TOOLBAR_HELPTEXT => $request_data["l10n_midcom"]->get('component configuration helptext'),
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => 
            (
                   ! $_MIDCOM->auth->can_do('midgard:update', $topic)
                || ! $_MIDCOM->auth->can_do('midcom:component_config', $topic)
            )
        ));
        }
        $toolbar->top->add_item(Array(
            // MIDCOM_TOOLBAR_URL =>  "ais/topic/edit/{$current_topic}.html",
            MIDCOM_TOOLBAR_URL =>  "ais/topic/configure/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("edit topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit-folder.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $topic)
               && $_MIDCOM->auth->can_do('midgard:update', $topic)
              )
            
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL =>  "ais/topic/score/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("edit order"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => ! $_MIDCOM->auth->can_do('midgard:update', $topic)
            
        )); 
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "ais/topic/delete/{$topic->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("delete topic"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midcom.admin.content:topic_management', $topic)
               && $_MIDCOM->auth->can_do('midgard:delete', $topic)
              )
            
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "ais/topic/move/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n_midcom"]->get('move'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $current_topic) == false))
        ));
        /* todo make attachmentshandler... */
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "ais/topic/attachment/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("topic attachments"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                  $_MIDCOM->auth->can_do('midgard:attachments', $topic)
               && $_MIDCOM->auth->can_do('midgard:update', $topic)
              )
            
        ));
   
        
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "ais/rcs/history/{$topic->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("topic revisions"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
    
 
    }
    /*
     * function to get the request array. 
     * */
    function get_request_switch() {
    
        
        // Note: if you do not set the handlerclass explicitly, the handler you're
        // refering to will be in the Aegir main request.
        $request_switch[] = Array
        (
            'fixed_args' => array('ais'),
            'handler' => 'index',
            
            // No further arguments, we have neither fixed nor variable arguments.
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic', 'configure'),
            'handler' => array('midcom_admin_content2_config','edit'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic','configure', 'edit'),
            'handler' => array('midcom_admin_content2_config','edit'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic', 'create'),
            'handler' => array('midcom_admin_content2_config','create'),
            'variable_args' => 1,
        );
    
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic','edit'),
            'handler' => array('midcom_admin_content2_config','edit'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic','create'),
            'handler' => array('midcom_admin_simplecontent_topic','create'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic','delete'),
            'handler' => array('midcom_admin_simplecontent_topic','delete'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic','move'),
            'handler' => array('midcom_admin_simplecontent_topic','move'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic', 'score'),
            'handler' => array('midcom_admin_simplecontent_topic','score'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic'),
            'handler' => array('midcom_admin_simplecontent_topic','topic'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','help'),
            'handler' => array('midcom_admin_help_help','show'),
            'variable_args' => 1,
        );
        
        
        
        
        
        
        /*// handled by simplecontent... 
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic','delete'),
            'handler' => array('midcom_admin_content2_config','delete'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic','move'),
            'handler' => array('midcom_admin_content2_config','move'),
            'variable_args' => 1,
        );
        */
        $request_switch[] = Array
        (
            'fixed_args' => array('ais','topic'),
            'handler' => array('midcom_admin_content2_config','view'),
            'variable_args' => 0,
        );
        
        return $request_switch;
    
    }
}
?>
