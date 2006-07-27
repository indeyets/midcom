<?php
/**
 * Created on Oct 30, 2005
 * @author tarjei huse
 * @package 
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */

require_once('config.php');
require_once('login.php');

$GLOBALS['testclasses'] =  array('midcom_admin_aegir_tests_ajaxmenu_functional' => 1);
$GLOBALS['testconfig'] = 'midcom_admin_aegir_tests_config';
      
class midcom_admin_aegir_tests_ajaxmenu_functional extends aegir_login_test {

    function setUp() {
        $this->get($this->config->get_login_url());
        $this->browser = $this->getBrowser();
        $this->login();
    }

    function testDeepLinksInajaxMenu() {
        print "Testing deep links";
        foreach ($this->config->registry as $module => $module_config) {
            if (!$module_config['hide']) {
                $this->assertAllLinks($this->config->get_base_url() . '/ajaxmenu/' . $module . '/0/1');
            }
        }
    
    }
    /**
     * This is a recursive function that goes through the different links
     * and checks every pageload so that none return errors. 
     */
    function assertAllLinks($start) {
        print $start . "\n";
        $this->browser->get($start);
        $this->assertPageDoesNotContainErrors();
        // the simpletest framework is still missing some stuff...
        foreach ($this->browser->_page->_links as $link) {
            $class   = $link->getAttribute('class');
            $onclick = $link->getAttribute("onclick");
            if ( $onclick && $class == "nav_closedFolder" ) {
                
                $matches = array();
                if ( preg_match("/getSubElements\('(.*)','(.+)'/i", $onclick,$matches) ) {
                    $url = $matches[1];
                    $id  = $matches[2];
                    if ( !$this->assertTrue($url != '' , "Url caught from $onclick ($class, $start) was empty!")) {
                        return;
                    }
                                    
                    $this->assertAllLinks($url);
                }
            }
        }
        return;        
    }
   
}





/* brukes for å kunne integrere i flere filer */
if (realpath($_SERVER['PHP_SELF']) == __FILE__) {
    $config = midcom_admin_aegir_tests_config();
    $test = new AjaxMenuFunctionalTest(&$config);
    $test->run(new TextReporter);
}


?>