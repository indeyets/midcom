<?php
/**
 * Created on Aug 3, 2005
 *
 * This is the handler that lets you run the testcases you have made for your midcom.
 * 
 * Some notes:
 * To run tests, place a file named test_<something> in the tests directory of your 
 * module. The file should contain the following:
 * <?php
 * $GLOBALS['testclasses'] = array ( 'name_of_test_class' => boolean config) ;
 * $GLOBALS['testconfig'] = 'classname'; // Optional! 
 * ?>
 * 
 * The file must also ensure that any classes used in the tests are loaded.
 * 
 * BIG NOTE ON CONFIG
 * If you set the config option to true, a config class will be passed to your
 * constructor when the test is loaded. This implies that you _HAVE_ to make a constuctor
 * for your class that takes hand of this option. To make sure errors do not happen, 
 * the interface should be:
 * function test_constructor_name ($label = false, $config);
 * The reason for this is so this will make sure that if you make a class w/o a config,
 * you'll get an error when running the class instead of strange loops when running 
 * the test. 
 * 
 * 
 * Run tests 
 * @package midcom.tests
 */
 
 
class midcom_tests extends midcom_baseclasses_components_handler  {
    
    /**
     * Pointer to the toolbarobject
     * @access private
     * @var midcom_helper_toolbars 
     */
     var $_toolbars = null;
    
    
	function midcom_tests  () 
    {
	         parent::midcom_baseclasses_components_handler();
	}
	
	function _on_initialize() 
    {
		// Populate the request data with references to the class members we might need
        $this->_request_data['toolbars']    = & midcom_helper_toolbars::get_instance();        
                
	} 
    
    /**
     * A simple wrapper to handle the situation where simpletest is missing.
     * @return boolean false if the files could not be loaded.
     */
    function _load_files() 
    {
        // this should catch the situation where simpletest isn't included.
        if ( (@include_once('simpletest/unit_tester.php')) == true ) {
            //require_once 'simpletest/unit_tester.php';
            // while require here should give an error if the package isn't complete.
            require_once 'simpletest/web_tester.php';
            require_once 'simpletest/reporter.php';
            require 'config.php';
            require 'lib/web.php';  // needs simpletest
            require 'reporter.php'; // requires reporter
            return true;
        } 
        return false;
    }
    /**
     * A simple function to generate the location bar.
     * First, we have to set the current node and leaf.
     * @param array the args array from the method.
     */
    function generate_location_bar($node, $leaf = false) 
    {
        
        $this->_request_data['aegir_interface']->set_current_node($node);
        if ($leaf) 
        {
            $this->_request_data['aegir_interface']->set_current_leaf($leaf);
        }
        $this->_request_data['aegir_interface']->generate_location_bar();
    }
    /**
     * This internal helper adds the edit and delete links to the local toolbar.
     * 
     * @access private
     */
    function _prepare_local_toolbar()
    {
        /* call static instance to make sure the topic toolbar is also added. */
        
		$this->_toolbars = &midcom_helper_toolbars::get_instance();
        $this->_toolbars->bottom->add_item(Array(
            MIDCOM_TOOLBAR_URL => "",
            MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('index'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
            MIDCOM_TOOLBAR_ENABLED => true,
			MIDCOM_TOOLBAR_HIDDEN => false
        ));    
    }
    
    function _handler_index ($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        /* load the maifest and scan each component for a 
         * tests dir and if so, lsit the files within that start with test_
         * 
         * */
        
        debug_pop();       
        return true;
    }
    /**
     * Check if simpletest exists and if so just show an index.
     */
    function _show_index() {
        
        if (!$this->_load_files()) {
            midcom_show_style("nosimpletest"); 
        } else { 
            midcom_show_style("index");
        }
    }
    
    /**
     * Runs the tests defined in the $args[1] for the component
     * defined in $args[0].
     */
    
    function _handler_run_test($handler_id, $args, &$data) {
        
        if (!$this->_load_files()) {
            $_MIDCOM->relocate('tests/'); // relocate to index...
        }
        
        
        /* adding the needed css to head. */
        $_MIDCOM->add_style_head(midcom_tests_reporter::_getCss());
        /* resetting the array */
        $GLOBALS['testclasses'] = array();
        $testfile = MIDCOM_ROOT . "/" . str_replace('.', '/', $args[0]) . "/tests/" . $args[1] . ".php";
        
        /* if we cant find the file, then something is utterly wrong. We fail. */
        if (!file_exists($testfile)) {
            return false;
        }
        
        $this->generate_location_bar($args[0], $testfile);
        
        // this file _MUST_ export an array  in $GLOBALS['testclasses'] that lsits the testcases to run!
        include ($testfile);
        
        
        $this->_request_data['grouptest'] = &new GroupTest("All tests");
        
        $config = new midcom_tests_config(&$this->_request_data['aegir_interface']->module_config);
        
        /*
         * Note: The reason all constructors get the label first is because the
         * simpletest constructor expects a label as an argument to the costructor! 
         */
        foreach ($GLOBALS['testclasses'] as $test => $config) {
            if (!array_key_exists('testconfig', $GLOBALS) ){
                if ($config) {
                    $this->_request_data['grouptest']->addTestCase(new $test (false, &$config));
                } else {
                    $this->_request_data['grouptest']->addTestCase(new $test (false));
                }                         
            } else {
                if ($config) {
                    $localconfig = new $GLOBALS['testconfig'](
                            &$this->_request_data['aegir_interface']->module_config
                        );
                $this->_request_data['grouptest']->addTestCase(  new $test (false, $localconfig)  );
                } else {
                    $this->_request_data['grouptest']->addTestCase(  new $test (false)  );
                }
            }
        }
        
        return true;
    }
    
    function _show_run_test() {
        
        $this->_request_data['grouptest']->run(new midcom_tests_reporter());
        if ($this->_request_data['grouptest']->getSize() == 0) {
            echo "<p>" . $this->_request_data['l10n']->get("Note: If you are getting no tests, you have probably forgotten to define the GLOBALS[testclasses] and  GLOBALS[testconfig]!</p>");
        }
    }
    
    function _handler_show_tests_for_midcom($handler_id, $args, &$data) {
        $this->generate_location_bar($args[0]);
        return true;   
    }
    function _show_show_tests_for_midcom () {
        midcom_show_style("index");
    }
 }


