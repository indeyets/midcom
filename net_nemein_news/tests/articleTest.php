<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

/**
 * Test that
 */
class net_nemein_news_tests_article extends midcom_tests_testcase
{
    public function testAction_show()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            print "testLoadArticle()\nLoading article\n\n";
        }
        
        $this->create_context('net_nemein_news');
        
        $data = array();
        $args = array(
            'name' => 'ajatus_lightning_talk-_fosdem'
        );
        
        $routes = $_MIDCOM->dispatcher->get_routes();
        $controller_class = $routes['show']['controller'];
        $controller = new $controller_class($_MIDCOM->context->component_instance);
        
        $action_method = "action_show";
        $controller->$action_method('test', &$data, $args);
        $_MIDCOM->context->set_item('net_nemein_news', $data);

        $this->assertTrue(array_key_exists('article', $data));
        
        $_MIDCOM->context->delete();
    }
}

?>