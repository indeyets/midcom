<?php

$_MIDCOM->componentloader->load('midcom.admin.aegir');
/**
 * the Aegir handler for this module.
 * @package midcom.admin.simplecontent
 */ 

class midcom_admin_simplecontent_aegir extends midcom_admin_aegir_module {


    function midcom_admin_simplecontent_aegir ()
    { 
        parent::midcom_admin_aegir_interface();
    }
    /*
     * function to get the request array. 
     * */
    function get_request_switch() {
    
        
        // Note: if you do not set the handlerclass explicitly, the handler you're
        // refering to will be in the Aegir main request.
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent'),
            'handler' => 'index',
            
            // No further arguments, we have neither fixed nor variable arguments.
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','article'),
            'handler' => array('midcom_admin_simplecontent_article','article'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','article','edit'),
            'handler' => array('midcom_admin_simplecontent_article','edit'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','article','create'),
            'handler' => array('midcom_admin_simplecontent_article','create'),
            'variable_args' => 2,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','article','delete'),
            'handler' => array('midcom_admin_simplecontent_article','delete'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','article','move'),
            'handler' => array('midcom_admin_simplecontent_article','move'),
            'variable_args' => 1,
        );             
        /* disabled until further notice.
        $request_switch[] = Array
        (
            'fixed_args' => array('article','copy'),
            'handler' => array('midcom_admin_simplecontent_article','copy'),
            'variable_args' => 1,
        );             
        */
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','topic'),
            'handler' => array('midcom_admin_simplecontent_topic','topic'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','topic', 'configure'),
            'handler' => array('midcom_admin_content2_config','edit'),
            'variable_args' => 1,
        );
        
        
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','topic','edit'),
            'handler' => array('midcom_admin_simplecontent_topic','edit'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','topic','create'),
            'handler' => array('midcom_admin_simplecontent_topic','create'),
            'variable_args' => 2,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','topic','delete'),
            'handler' => array('midcom_admin_simplecontent_topic','delete'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','topic','move'),
            'handler' => array('midcom_admin_simplecontent_topic','move'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','topic','acls'),
            'handler' => array('midcom_admin_acls_edit','edit'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','topic'),
            'handler' => array('midcom_admin_simplecontent_topic','index'),
            'variable_args' => 0,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('simplecontent','parameters'),
            'handler' => array('midcom_admin_parameters_parameters','edit'),
            'variable_args' => 1,
        );
        
        return $request_switch;
    
    }
    
    function prepare_toolbar() {
        $request_data =& $_MIDCOM->get_custom_context_data('request_data');
        $nav = &$this->get_navigation();
        $node = $nav->get_node($this->get_current_node());
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
                    MIDCOM_TOOLBAR_URL => "simplecontent/create/{$current_topic}/topic.html", 
                    MIDCOM_TOOLBAR_LABEL => $text,
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $topic) == false)
                ), 0);
        }
    
        // topic stuff
         
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/topic/create/{$current_topic}.html",
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
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL =>  "simplecontent/topic/configure/{$current_topic}.html",
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
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL =>  "simplecontent/topic/edit/{$current_topic}.html",
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
            MIDCOM_TOOLBAR_URL =>  "simplecontent/topic/score/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("edit order"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => ! $_MIDCOM->auth->can_do('midgard:update', $topic)
            
        )); 
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/topic/delete/{$topic->guid}.html",
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
            MIDCOM_TOOLBAR_URL => "simplecontent/topic/move/{$current_topic}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n_midcom"]->get('move'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $current_topic) == false))
        ));
        /* todo make attachmentshandler... */
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/topic/attachment/{$current_topic}.html",
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
            MIDCOM_TOOLBAR_URL => "simplecontent/parameters/{$topic->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("Edit topic parameters"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit-folder.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => !( $_MIDCOM->auth->can_do('midgard:update', $topic)
                                     && $_MIDCOM->auth->can_do('midgard:parameters', $topic))
            
        ));
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "rcs/{$topic->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("topic revisions"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
    
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "simplecontent/topic/acls/{$topic->guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("Security"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN =>
            ! (
                $_MIDCOM->auth->can_do('midgard:update', $topic)
              )
            
        ));
    
    }
    
    function set_current_node( $node) 
    {
        if ($this->_nav === null) 
        {
            $this->get_navigation();
        }
        
        if (is_object($node)) 
        {
            if (is_a($node, 'midcom_baseclasses_database_article')) 
            {
                $this->set_current_leaf($node->id);
                $this->_nav->_current_node = $node->topic;
                
            } else {
                $this->_nav->_current_node = $node->id;
            }
            return;
        }
        $this->_nav->_current_node = $node;
        
    }
}
?>