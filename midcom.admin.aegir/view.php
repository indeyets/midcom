<?php

/**
 * Request class for handling simple topics and articles.
 * @package midcom.admin.aegir
 * 
 */

class midcom_admin_aegir_viewer  extends midcom_baseclasses_components_request {
    
    var $msg;

	/* the current topic we are in 
	 * @var current_topic
	 * @access public 
	 **/
	var $_current_topic = 0;

    /* pointer to midcom_session_object */
    var $_session = null;
   
    /**  the toolbar */
    var $toolbar = null;
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * NOT IN USE! 
     * @var midcom_baseclasses_database_topic
     * @access private
     */
    var $_content_topic = null;
    
    /**
     * stuff used by the ne ajaxmenu 
     */
    var $_menu = array();
    var $_nav = null;
    
    
	function midcom_admin_aegir_viewer($topic, $config) 
    {
    	//$page = mgd_get_object_by_guid($config->get("root_page"));
        parent::midcom_baseclasses_components_request($topic, $config);
        
        $this->msg = "";
        //$this->_session = new midcom_service_session();
    }

	function _on_initialize() {
		
                
       	$_MIDCOM->cache->content->no_cache();
        $_MIDCOM->skip_page_style = true;
        
        
		//$_MIDCOM->auth->require_valid_user();
		// todo: move this somwhere else.
		/* Check for aegir or normal style: */
        $style_attributes = array ( 'rel'   =>  "stylesheet" ,
                                    'type'  =>  "text/css" ,
                                    'media' =>  "screen"
                                    );
                                    
    	
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.helper.datamanager/datamanager.css";
        $_MIDCOM->add_link_head( $style_attributes);
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/ais.css";
    	$_MIDCOM->add_link_head( $style_attributes); 
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/midcom_toolbar.css";
        $_MIDCOM->add_link_head( $style_attributes);
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.aegir/aegir_style.css";
        $_MIDCOM->add_link_head( $style_attributes);
        $iconurl  = MIDCOM_STATIC_URL . "/midcom.admin.aegir";     
        
        $style =<<<EOL


ul.nav_root li.nav_openFolder a.nav_openFolder  {
    background: url($iconurl/folder_open.png) center left no-repeat;
    padding-left:28px;   
}
ul.nav_root li.nav_openFolder a.nav_nodeLink {
    
}
/* this may look wierd, but it makes a minus when you load a node from xml. */
ul.nav_root li.nav_openFolder a.nav_closedFolder {
    background: url($iconurl/folder_open.png) center left no-repeat;
    padding-left:28px;
    padding-bottom: 0.5em;
    /*margin-left:0px;*/
}
ul.nav_root li.nav_closedFolder a.nav_closedFolder {
    background: url($iconurl/folder_closed.png) center left no-repeat;
    padding-left:28px;
}
ul.nav_root li.nav_item a {
    background: url($iconurl/html.png   ) center left no-repeat;
    padding-left:18px;
    margin-left:10px;
}

ul.nav_root li.nav_folder a {
    background: url($iconurl/folder.png   ) center left no-repeat;
    padding-left:18px;
    margin-left:10px;
}


EOL;
        $_MIDCOM->add_style_head("$style");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL . '/midcom.admin.aegir/ajaxmenu.js');
        
        
        
        // argv has the following format: topic_id/mode
        $this->_request_switch = array();
        
    }    
    
    /**
     * CAN_HANDLE Phase interface,
     * Autoloads components based on the first fixed args. 
     * 
     * @param int $argc The argument count
     * @param Array $argv The argument list
     * @return bool Indicating wether the request can be handled by the class, or not.
     */
    function can_handle($argc, $argv)
    {
        debug_push_class(__CLASS__,__FUNCTION__);
        $registry = $this->_config->get('registry');
        $this->_request_switch[] = Array
        (
            'handler' => 'index'
            // No further arguments, we have neither fixed nor variable arguments.
        );
        
        if ($argc > 0 ) {
            
            if ($argv[0] == 'ajaxmenu') {
                if (!$_MIDCOM->auth->is_valid_user() ) {
                   $_MIDCOM->relocate('login');
                }
                $this->_request_switch[] = Array
                (
                    'fixed_args' => array('ajaxmenu'),
                    'variable_args' => 3,
                    'handler' => 'ajaxmenu',
                );
                $this->_request_switch[] = Array
                (
                    'fixed_args' => array('ajaxmenu'),
                    'variable_args' => 2,
                    'handler' => 'ajaxmenu',
                );  
                  
                $this->_prepare_request_switch();
                
                $_MIDCOM->style->prepend_styledir('/midcom/admin/aegir/style/navigation/ajaxmenu');
                debug_pop();
                return parent::can_handle($argc,$argv); 
                
            } elseif ($argv[0] == 'login') {
                
                $this->_request_switch[] = Array
                (
                    'fixed_args' => array('login'),
                    'handler' => 'login',
                );
                $this->_request_switch[] = Array
                (
                    'fixed_args' => array('login'),
                    'variable_args' => 1,
                    'handler' => 'logout',
                );    
                $this->_prepare_request_switch();
                
                $_MIDCOM->style->prepend_styledir('/midcom/admin/aegir/style/login');
                //$this->generate_menu();
                debug_pop();
                return parent::can_handle($argc,$argv); 
            
            } elseif (array_key_exists($argv[0], $registry)) {
                /* do not use the normal load_libary functon 
                 * so we can load components that has their own viewer as well.
                 * */
                $_MIDCOM->componentloader->load($registry[$argv[0]]['component']);
            } else {
                debug_pop();
                return false;
            }
        
        
            $component = str_replace('.', '_', $registry[$argv[0]]['component'] );        
            $class = $component. '_aegir';
            /**
             * init the aegir interfaceclass. 
             */
            
            $this->_request_data['aegir_interface'] = new $class();
            $this->_request_data['aegir_interface']->registry   = $registry;
            $this->_request_data['aegir_interface']->current    = $argv[0];
            $this->_request_data['aegir_interface']->_argv       = $argv;
            $this->_request_data['aegir_interface']->_argc       = $argc;
            $this->_request_data['aegir_interface']->_initialize();
            
            /* set the correct requestswitch */
            $this->_request_switch  = array_merge(
                            $this->_request_switch,
                            $this->_request_data['aegir_interface']->get_request_switch());
            if ($argv[0] == 'ais' && $argc > 1 && $argv[2] == 'data') {
                
                $this->_request_switch  = array_merge (
                     Array ( 0 => Array 
                        (
                            'fixed_args' => array('ais'),
                            'handler' => array('midcom_admin_content2_component','component'),
                            'variable_args' => $argc-1,
                        )),
                        $this->_request_switch
                );
                //debug_print_r("Request_switch:", $this->_request_switch );
                //debug_print_r("Argv: " ,$argv);
            } 
            /* prepend the component styledirectory */
            $_MIDCOM->style->prepend_component_styledir($registry[$argv[0]]['component']);
            /* add the root location */
            $this->_request_data['toolbars'] = & midcom_helper_toolbars::get_instance();
            $this->_request_data['toolbars']->aegir_location->add_item(
                                        array (
                                            MIDCOM_TOOLBAR_URL =>  $this->_request_data['aegir_interface']->current,
                                            MIDCOM_TOOLBAR_LABEL => $registry[$this->_request_data['aegir_interface']->current]['name'],
                                            MIDCOM_TOOLBAR_HELPTEXT => '',
                                            MIDCOM_TOOLBAR_ICON => '',
                                            MIDCOM_TOOLBAR_ENABLED => true,
                                            MIDCOM_TOOLBAR_HIDDEN => false 
                                            )
                                    );
        
            
        } else {
            // root
            $this->_request_data['aegir_interface'] = new midcom_admin_aegir_module();
            $this->_request_data['aegir_interface']->_navigation_class = 'midcom_admin_aegir_module_navigation';
            $this->_request_data['aegir_interface']->registry = $registry;
            $this->_request_data['aegir_interface']->current  = '';
            //$this->_request_data['aegir_interface']->_initialize();
        }
        
        
        if (!$_MIDCOM->auth->is_valid_user() ) {
            /* url should only be used when at the root of the request, f.x /aegir/
             * */
            $url = "";
            $relocate = join ("/", $argv);
            if (count($argv) == 0 ) {
                $url = $_SERVER['PHP_SELF'];
            }
            $_MIDCOM->relocate($url . 'login?onto=' . urlencode($relocate));
        }
        
        $this->_prepare_request_switch();
        $this->generate_menu();
        debug_pop();
        return parent::can_handle($argc,$argv);
        
    }
    
    function _handler_login($handler_id, $args, &$data) {
        if ($_MIDCOM->auth->is_valid_user()) {
            if (array_key_exists('onto', $_REQUEST)) {
                $_MIDCOM->relocate(urldecode($_REQUEST['onto']));
            } 
            $_MIDCOM->relocate("");
        }
        return true;
    }
    
    function _show_login() {
        
        $_MIDCOM->auth->show_login_form(true);
    }
    
    function _handler_logout( $handler_id, $args, &$data) {
        $_MIDCOM->auth->logout();
        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'login');
        // this will exit;
        return true;
    }
    /**
     * generate the top menu.
     */
    function generate_menu () {
        $toolbars = &midcom_helper_toolbars::get_instance();
        $item = array (
            MIDCOM_TOOLBAR_URL => "",
            MIDCOM_TOOLBAR_LABEL => "Aegir",
            MIDCOM_TOOLBAR_HELPTEXT => '',
            MIDCOM_TOOLBAR_ICON => '',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => false
                );
        $toolbars->aegir_menu->items['aegir'] = $toolbars->aegir_menu->clean_item($item);
        $item = array (
            MIDCOM_TOOLBAR_URL => "" ,
            MIDCOM_TOOLBAR_LABEL => "Portal",
            MIDCOM_TOOLBAR_HELPTEXT => '',
            MIDCOM_TOOLBAR_ICON => '',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => false
        );
        $toolbars->aegir_menu->items['portal'] = $toolbars->aegir_menu->clean_item($item);
        $item = array (
            MIDCOM_TOOLBAR_URL => "#",
            MIDCOM_TOOLBAR_LABEL => "Help",
            MIDCOM_TOOLBAR_HELPTEXT => '',
            MIDCOM_TOOLBAR_ICON => '',
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_HIDDEN => false
        );
        $toolbars->aegir_menu->items['help'] = $toolbars->aegir_menu->clean_item($item);
                
        foreach ($_MIDCOM->componentloader->manifests as $manifest ) {
            if (! array_key_exists('midcom.admin.aegir',$manifest->customdata)
                ||
                ! array_key_exists('menu',$manifest->customdata['midcom.admin.aegir'])
                ) {
                continue;
            }
            foreach ($manifest->customdata['midcom.admin.aegir']['menu'] as $index => $menu) {
                // todo : her må noe fikses
                
                $item = array (
                    MIDCOM_TOOLBAR_URL => $menu['url'] ,
                    MIDCOM_TOOLBAR_LABEL => $menu['text'],
                    MIDCOM_TOOLBAR_HELPTEXT => '',
                    MIDCOM_TOOLBAR_ICON => '',
                    MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_HIDDEN => false
                );
                
                if (!$toolbars->aegir_menu->add_item_to_index($item, $index) ) {
                    $_MIDCOM->generate_error("Item {$item[MIDCOM_TOOLBAR_LABEL]} could not be added to index $index");
                }
            }   
        }
    }
    

   	function _handler_index() 
    {
   		return true;
   	} 
    
    function _show_index() 
    {
       midcom_show_style('index');
    }
    
    function _handler_ajaxmenu ($handler_id, $args, &$data) 
    {
        debug_push_class(__CLASS__,__FILE__);

        require_once('style/navigation/ajaxmenu.php');        
        
        $registry = $this->_config->get('registry');
        $component  = $registry[$args[0]]['component'];
        
        
        $ajaxmenu = new midcom_admin_aegir_navigation_ajaxmenu();
        /* important, prefix is set in to_html() so we have to set it here
         */
        $ajaxmenu->_prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
       
        $rootid  = $args[1];
        $ajaxmenu->maxlevel = $args[2];
        
        $loader =& $GLOBALS["midcom"]->get_component_loader();
        $path = $loader->path_to_snippetpath($component);
        require_once( MIDCOM_ROOT .  $path . "/aegir_navigation.php");
        $class = str_replace('.', '_', $component) . '_aegir_navigation';
        $ajaxmenu->_nav = new $class();
        debug_add("Starting from rootid: $rootid");
        $this->_request_data['htmlmenu'] = $ajaxmenu->create_menu_nodes($rootid, -1,array(),true);
        
        debug_pop();
        return true;

        
    }
    
    function get_prefix() {
        return $this->_request_data['aegir_interface']->current ."/";
    }
    
    function _show_ajaxmenu() {
        echo $this->_request_data['htmlmenu'];
    }

        
    /**
     * Display the content, it uses the handler as determined by can_handle.
     * This overrides the basic show method of the class to include the ais style around the component. 
     * 
     * @see _on_show();
     */
    function show()
    {
     	debug_push_class(__CLASS__,__FILE__);
        
        
        
        // Call the event handler
        $result = $this->_on_show($this->_handler['id']);
        if (! $result)
        {
            debug_add('The _on_show event handler returned false, aborting.');
            debug_pop();
            return;
        }
        
        // Call the handler:
        $handler =& $this->_handler['handler'][0];
        $method = "_show_{$this->_handler['handler'][1]}";
        
        $handler->$method($this->_handler['id'], $this->_request_data);
        debug_pop();
    }

}

?>
