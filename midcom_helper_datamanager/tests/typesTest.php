<?php
/**
 * @package midcom_helper_datamanager
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

/**
 * Test to...
 */
class midcom_helper_datamanager_tests_types extends midcom_tests_testcase
{
    
    public function testLoad()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo __FUNCTION__ . "\n";
            echo "Loading types\n\n";
        }
        
        $data = $this->load_data('ajatus_lightning_talk-_fosdem');
                
        $schema_path = 'file:/net_nemein_news/configuration/schema.yml';
        $dm = new midcom_helper_datamanager_datamanager($schema_path);
        $dm->set_schema(null);
        $dm->set_storage($data['article']);
        
        $this->assertTrue(is_array($dm->schema->fields));
        
        foreach ($dm->schema->fields as $name => $data)
        {
            echo "\n\n\$dm->types->{$name}:\n";
            
            var_dump($dm->types->$name);
        }
        
        //var_dump($dm);
    }
    
    
    private function load_data($name)
    {
        $this->create_context('net_nemein_news');
        
        $data = array();
        $args = array(
            'name' => $name
        );
        
        $routes = $_MIDCOM->dispatcher->get_routes();
        $controller_class = $routes['show']['controller'];
        $controller = new $controller_class($_MIDCOM->context->component_instance);
        
        $action_method = "action_show";
        $controller->$action_method('test', &$data, $args);
        $_MIDCOM->context->set_item('net_nemein_news', $data);
        
        $_MIDCOM->context->delete();

        return $data;
    }
}
?>