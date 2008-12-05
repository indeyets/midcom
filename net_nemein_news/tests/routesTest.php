<?php
/**
 * @package net_nemein_news
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

require_once('tests/testcase.php');

/**
 * Test that dispatches all routes of the component
 */
class net_nemein_news_tests_routes extends net_nemein_news_tests_base
{
    
    public function testDispatch()
    {
        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo __FUNCTION__ . "\n";
            echo "Loading all routes\n\n";
        }
        
        $component_name = 'net_nemein_news';
        $manifest = $_MIDCOM->componentloader->manifests[$component_name];
        
        // Enter new context
        $_MIDCOM->context->create();
        try
        {
            $_MIDCOM->dispatcher->initialize($component_name);
        }
        catch (Exception $e)
        {
            $_MIDCOM->context->delete();
            $this->fail("Component failed to load");
        }
        
        if (!$_MIDCOM->context->component_instance)
        {
            $_MIDCOM->context->delete();
            $this->fail("Component failed to load");
        }

        if (!$_MIDCOM->context->component_instance->configuration->exists('routes'))
        {
            // No routes in this component, skip
            if (MIDCOM_TESTS_ENABLE_OUTPUT)
            {
                echo "no routes found\n";
            }
            $_MIDCOM->context->delete();
            return;
        }

        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo "Running {$component_name}...\n";
        }

        $routes = $_MIDCOM->dispatcher->get_routes();
        
        foreach ($routes as $route_id => $route_configuration)
        {
            // Generate fake arguments
            preg_match_all('/\{(.+?)\}/', $route_configuration['route'], $route_path_matches);
            $route_string = $route_configuration['route'];
            $args = array();
            foreach ($route_path_matches[1] as $match)
            {
                $args[$match] = 'test';
                $route_string = str_replace("{{$match}}", "[{$match}: {$args[$match]}]", $route_string);
            }

            $_MIDCOM->dispatcher->set_route($route_id, $args);
            if (MIDCOM_TESTS_ENABLE_OUTPUT)
            {
                echo "    {$route_id}: {$route_string}\n";
            }

            try
            {
                $_MIDCOM->dispatcher->dispatch();
            }
            catch (Exception $e)
            {
                if (MIDCOM_TESTS_ENABLE_OUTPUT)
                {
                    echo "        " . get_class($e) . ': ' . $e->getMessage() . "\n";
                }
            }

            try
            {
                if (MIDCOM_TESTS_ENABLE_OUTPUT)
                {
                    echo "        returned keys: " . implode(', ', array_keys($_MIDCOM->context->$component_name)) . "\n";
                }
            }
            catch (Exception $e)
            {
                if (MIDCOM_TESTS_ENABLE_OUTPUT)
                {
                    echo "        returned no data\n";
                }
            }
        }
        
        // Delete the context
        $_MIDCOM->context->delete();

        if (MIDCOM_TESTS_ENABLE_OUTPUT)
        {
            echo "\n";
        }
 
        $this->assertTrue(true);
    }

}
?>