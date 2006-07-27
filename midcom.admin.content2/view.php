<?php
/**
 * Created on Jan 21, 2006
 * @author tarjei huse
 * @package midcom.admin.content2
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */

/**
 * @package midcom.admin.content2
 * 
 */

class midcom_admin_content2_viewer  extends midcom_baseclasses_components_request {
    
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
     * @var midcom_baseclasses_database_topic
     * @access private
     */
    var $_content_topic = null;
    
    /**
     * stuff used by the ne ajaxmenu 
     */
    var $_menu = array();
    var $_nav = null;
    /**
     * The context to run show() in. Set by the handler if needed.
     */
    var $context = null; 
    
    
    
    function midcom_admin_aegir_viewer($topic, $config) 
    {
        //$page = mgd_get_object_by_guid($config->get("root_page"));
        parent::midcom_baseclasses_components_request($topic, $config);
        var_dump($topic);
        $this->_content_topic = $topic;
        $this->msg = "";
        //$this->_session = new midcom_service_session();
    }

    function _on_initialize() 
    {
        /*
         * set the view_contentmgr key in globals so DM1 understands it should include javascript. 
         */    
        $GLOBALS['view_contentmgr'] = null;
                
        $_MIDCOM->cache->content->no_cache();
        
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
        $style_attributes['href'] = MIDCOM_STATIC_URL ."/midcom.admin.content/aegir_style.css";
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
        $this->_request_data['aegir_interface'] = new midcom_admin_content2_aegir();
        
        
        //$this->_get_request_switch();
        
        
        // set the current contextid to 0 untill something changes it.
        $this->_request_data['context'] = 0;
        // set msg to null.
        $this->_request_data['msg'] = "";
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
        
        
        $this->_request_switch[] = Array
        (
            'handler' => 'index'
            // No further arguments, we have neither fixed nor variable arguments.
        );
        
        if ($argc > 0 ) 
        {
            
         
            if ($argv[0] == 'login') 
            {
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
                debug_pop();
                return parent::can_handle($argc,$argv); 
            
            }
            
            // ais it the namespace for stuff that is handled by other components.
            if ($argv[0] == 'topic')
            {
                $this->_request_switch =  midcom_admin_content2_aegir::get_request_switch();
            } else 
            {
                $this->_request_switch  = array_merge (
                     Array ( 0 => Array 
                        (
                            'fixed_args' => array(),
                            'handler' => array('midcom_admin_content2_component','component'),
                            'variable_args' => $argc,
                        )),
                        $this->_request_switch
                );
            }
            
            $this->_request_data['aegir_interface']->current    = 'ais';
            $this->_request_data['aegir_interface']->_class     = 'midcom_admin_content2';
            $this->_request_data['aegir_interface']->_argv       = $argv;
            $this->_request_data['aegir_interface']->_argc       = $argc;
            // add the styledirectory containing the style-init element we want.
            $_MIDCOM->style->append_styledir('/midcom/admin/content2/base_style');
            $_MIDCOM->style->append_styledir('/midcom/admin/aegir/style');
            /* prepend the component styledirectory */
            //$_MIDCOM->style->prepend_component_styledir($registry[$argv[0]]['component']);
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

    /**
     * Get the prefix that components should use.
     * Returns empty string. (Aegir returns something else :-) )
     */
    function get_prefix() 
    {
        return "";
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
    function generate_menu () 
    {
        $toolbars = &midcom_helper_toolbars::get_instance();
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
    
    function _get_request_switch() 
    {
        
        
        // Note: if you do not set the handlerclass explicitly, the handler you're
        // refering to will be in the Aegir main request.
        $request_switch[] = Array
        (
            'handler' => 'index',
            
            // No further arguments, we have neither fixed nor variable arguments.
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('topic', 'configure'),
            'handler' => array('midcom_admin_content2_config','edit'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('topic','configure', 'edit'),
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
            'fixed_args' => array('topic','edit'),
            'handler' => array('midcom_admin_content2_config','edit'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('topic','create'),
            'handler' => array('midcom_admin_simplecontent_topic','create'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('topic','delete'),
            'handler' => array('midcom_admin_simplecontent_topic','delete'),
            'variable_args' => 1,
        );
        $request_switch[] = Array
        (
            'fixed_args' => array('topic','move'),
            'handler' => array('midcom_admin_simplecontent_topic','move'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('topic', 'score'),
            'handler' => array('midcom_admin_simplecontent_topic','score'),
            'variable_args' => 1,
        );
        
        $request_switch[] = Array
        (
            'fixed_args' => array('topic'),
            'handler' => array('midcom_admin_simplecontent_topic','topic'),
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
            'fixed_args' => array('topic'),
            'handler' => array('midcom_admin_content2_config','view'),
            'variable_args' => 0,
        );
        return $request_switch;
    }
}

