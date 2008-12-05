<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * Test that loads all components and dispatches each of their routes
 */

// Argument checks
if (count($argv) != 2)
{
    die("Usage: php find_orphans.php midgardconffile\n");
}
$conffile = $argv[1];

// Start up a Midgard connection
$midgard = new midgard_connection();
$midgard->open($conffile);

// Load MidCOM with the manual dispatcher
require('midcom_core/framework.php');
$_MIDCOM = new midcom_core_midcom('manual');

echo "Loading all components and their routes\n\n";

// Go through the installed components
foreach ($_MIDCOM->componentloader->manifests as $component_name => $manifest)
{
    $_MIDCOM->dispatcher->initialize($component_name);

    if (!$_MIDCOM->dispatcher->component_instance->configuration->exists('routes'))
    {
        // No routes in this component, skip
        echo "Skipping {$component_name}: no routes\n\n";
        continue;
    }
    
    echo "Running {$component_name}...\n";
    
    $routes = $_MIDCOM->dispatcher->component_instance->configuration->get('routes');
    foreach ($routes as $route_id => $route_configuration)
    {
        // Enter new context
        $_MIDCOM->context->create();
    
        // Generate fake arguments
        preg_match_all('/\{\$(.+?)\}/', $route_configuration['route'], $route_path_matches);
        $route_string = $route_configuration['route'];
        $args = array();
        foreach ($route_path_matches[1] as $match)
        {
            $args[$match] = 'test';
            $route_string = str_replace("{\${$match}}", "[{$match}: {$args[$match]}]", $route_string);
        }
        
        $_MIDCOM->dispatcher->set_route($route_id, $args);
        echo "    {$route_id}: {$route_string}\n";
        
        try
        {
            $_MIDCOM->dispatcher->dispatch();
        }
        catch (Exception $e)
        {
            echo "        " . get_class($e) . ': ' . $e->getMessage() . "\n";

        }
        
        try
        {
            echo "        returned keys: " . implode(', ', array_keys($_MIDCOM->context->$component_name)) . "\n";
        }
        catch (Exception $e)
        {
            echo "        returned no data\n";
        }
        
        // Delete the context
        $_MIDCOM->context->delete();
    }
    echo "\n";
}

if ($_MIDCOM->timer)
{
    $_MIDCOM->timer->display();
}
?>