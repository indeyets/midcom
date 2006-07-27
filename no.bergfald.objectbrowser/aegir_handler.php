<?php
$_MIDCOM->componentloader->load('midcom.admin.aegir');
/**
 * Aegir handler class.
 * @package no.bergfald.objectbrowser
 */


class no_bergfald_objectbrowser_aegir extends midcom_admin_aegir_module {

    /**
     * Pointer to the schema object
     * @var no_bergfald_objectbrowser_schema
     * @access private
     */
    var $_schema = null;


    function no_bergfald_objectbrowser_aegir ()
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
            'fixed_args' => array('objectbrowser'),
            'handler' => array('no_bergfald_objectbrowser_object','index'),
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('objectbrowser','edit'),
            'handler' => array('no_bergfald_objectbrowser_object','edit'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('objectbrowser','create'),
            'handler' => array('no_bergfald_objectbrowser_object','create'),
            'variable_args' => 4,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('objectbrowser','delete'),
            'handler' => array('no_bergfald_objectbrowser_object','delete'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('objectbrowser','move'),
            'handler' => array('no_bergfald_objectbrowser_object','move'),
            'variable_args' => 1,
        );             
        
        $request_switch[] = Array
        (
            'fixed_args' => array('objectbrowser'),
            'handler' => array('no_bergfald_objectbrowser_object','view'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('objectbrowser'),
            'handler' => array('no_bergfald_objectbrowser','index'),
            'variable_args' => 0,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('objectbrowser', 'parameters'),
            'handler' => array('midcom_admin_parameters_parameters','edit'),
            'variable_args' => 1,
        );
        
        return $request_switch;
    
    }
    
    function prepare_toolbar()
    {
        $this->_schema = & no_bergfald_objectbrowser_schema::get_instance();
        $request_data =& $_MIDCOM->get_custom_context_data('request_data');
        $toolbar = &midcom_helper_toolbars::get_instance();
        $this->get_navigation();

        if (! ( $current_object_id = $this->get_current_leaf() ) ) 
        {
            $current_object_id = $this->get_current_node();
            if (mgd_is_guid($current_object_id)) 
            {
                $current_object = $this->_nav->get_node($current_object_id);
            }            
            
        }  
        elseif (mgd_is_guid($current_object_id))
        {
            $current_object = $this->_nav->get_leaf($current_object);
        }
        
        if (mgd_is_guid($current_object_id)) 
        {
            $current_object_guid = $current_object[MIDCOM_NAV_OBJECT]->guid;
        } 
        else 
        {
            $current_object_guid = 0;
        }
        
        $class_name = $this->_schema->get_current_type();
        

        foreach ($this->_schema->list_schemas($this->_schema->get_current_type()) as $name => $desc) 
        { 
            $label= sprintf($request_data["l10n"]->get('create %s'), $desc);
            if ($this->_schema->is_leaf($this->_schema->get_current_type())) {
                $up_attribute = $this->_schema->get_leaf_up_attribute(get_class($current_object[MIDCOM_NAV_OBJECT]));
                $parent_class = $current_object[MIDCOM_NAV_OBJECT]->parent();
                
                $parent = new $parent_class();
                
                
                $parent->get_by_id($current_object->{$up_attribute});
                        
                $toolbar->top->add_item(
                    Array 
                    ( 
                        MIDCOM_TOOLBAR_URL => "objectbrowser/create/{$parent->guid}/{$name}/{$class_name}/leaf.html", 
                        MIDCOM_TOOLBAR_LABEL => $label,
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                        MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $current_object) == false)
                    ));
            } else {
                $toolbar->top->add_item(
                    Array 
                    (
                        MIDCOM_TOOLBAR_URL => "objectbrowser/create/{$current_object_guid}/{$class_name}/{$name}/node.html", 
                        MIDCOM_TOOLBAR_LABEL => $label,
                        MIDCOM_TOOLBAR_HELPTEXT => sprintf($request_data["l10n_midcom"]->get("Create a leaf object of type %s"),$desc),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                        MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $current_object) == false)
                    ));
            }
        }
        /*
         * If there is no current object, do not show the nodes related to that
         */
        if (!mgd_is_guid($current_object_id)) 
        {
            return;
        }
        
        /* ad create links for the children */
        if ($this->_schema->is_node($this->_schema->get_current_type())) 
        {
            foreach ($this->_schema->get_children($this->_schema->get_current_type()) as $child_class => $nada) 
            {
                foreach ($this->_schema->list_schemas($child_class) as $name => $c_desc) 
                {
                    
                    $text = sprintf($request_data["l10n_midcom"]->get('create %s'), $c_desc);
                    $toolbar->top->add_item(
                        Array 
                        (
                            MIDCOM_TOOLBAR_URL => "objectbrowser/create/{$current_object_guid}/{$name}/{$child_class}/leaf.html", 
                            MIDCOM_TOOLBAR_LABEL => $text,
                            MIDCOM_TOOLBAR_HELPTEXT => sprintf($request_data["l10n_midcom"]->get("create an %s"),$c_desc),
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_new.png',
                            MIDCOM_TOOLBAR_ENABLED => true,
                            MIDCOM_TOOLBAR_HIDDEN => ($_MIDCOM->auth->can_do('midgard:create', $current_object) == false)
                        ));
                }
            }
        }

        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "objectbrowser/edit/{$current_object_guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("edit"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit-folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "objectbrowser/delete/{$current_object_guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("delete"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "objectbrowser/move/{$current_object_guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n_midcom"]->get('move'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/object-score.png',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => (($_MIDCOM->auth->can_do('midgard:update', $current_object) == false))
        ));
        /* todo make attachmentshandler... */
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "objectbrowser/attachment/{$current_object_guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("attachments"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/attach.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "objectbrowser/parameters/{$current_object_guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("Parameters"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/folder.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        
        
        $toolbar->top->add_item(Array(
            MIDCOM_TOOLBAR_URL => "rcs/no.bergfald.objectbrowser/{$current_object_guid}.html",
            MIDCOM_TOOLBAR_LABEL => $request_data["l10n"]->get("object changes"),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/topic-score.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
    }
   
}
?>
