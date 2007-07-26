<?php
/**
 * Handler for the tags-widget searches
 *
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: universalchooser_handler.php 11263 2007-07-17 23:26:37Z flack $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

//debug_print_r('_REQUEST',  $_REQUEST);

// Common variables
$encoding = 'UTF-8';
$items = array();
$mode = 'callback';
$_callback = null;
$callback_args = array();

// Common headers
$_MIDCOM->cache->content->content_type('text/xml');
$_MIDCOM->header('Content-type: text/xml; charset=' . $encoding);
echo '<?xml version="1.0" encoding="' . $encoding . '" standalone="yes"?>' . "\n";
echo "<response>\n";

if (! isset($_REQUEST["query"]))
{
    echo "    <status>0</status>\n";
    echo "    <errstr>Search term not defined</errstr>\n"; //TODO: Localize message
    echo "</response>\n";

    debug_add("Empty query string. Quitting now.");
    debug_pop();
    $_MIDCOM->finish();
    exit();
}

// $q = strtolower($_REQUEST["q"]);
$query = $_REQUEST["query"];

// // Convert tradiotional wildcard to SQL wildcard
// $query = str_replace('*', '%', $_REQUEST['query']);
// // Make sure we don't have multiple successive wildcards (performance killer)
// $query = preg_replace('/%+/', '%', $query);

// Get local copies of other variables from request
$map = array('component', 'class', 'object_id', 'id_field', 'callback', 'callback_args');
foreach ($map as $varname)
{
    if (   isset($_REQUEST[$varname])
        && !empty($_REQUEST[$varname]))
    {
        $$varname = $_REQUEST[$varname];
        if ($varname == 'callback_args')
        {
            $callback_args = json_decode($callback_args);
        }
    }
    else
    {
        $$varname = false;
    }
}

if (   !empty($component)
    && !empty($class)
    && !empty($object_id)
    && !empty($id_field))
{
    $mode = 'object';
}

if ($mode == 'object')
{
    $_MIDCOM->load_library('net.nemein.tag');
    
    // Load component if required
    if (!class_exists($class))
    {
        $_MIDCOM->componentloader->load_graceful($component);
    }
    // Could not get required class defined, abort
    if (!class_exists($class))
    {
        echo "    <status>0</status>\n";
        echo "    <errstr>Class {$class} could not be loaded</errstr>\n";
        echo "</response>\n";
        $_MIDCOM->finish();
        exit();
    }
    
    $qb = call_user_func(array($class, 'new_query_builder'));
    $qb->add_constraint($id_field, '=', $object_id);
    $results = $qb->execute();
    
    if ($results === false)
    {
        echo "    <status>0</status>\n";
        echo "    <errstr>Error when executing QB</errstr>\n";
        echo "</response>\n";
        $_MIDCOM->finish();
        exit();
    }
    
    $object = $result[0];
    
    $tags = net_nemein_tag_handler::get_object_tags($object);
    
    foreach($tags as $name => $link)
    {
        $data = array(
            'id' => $name,
            'name' => $name,
            'color' => '8596b6'
        );
        $items[$name] = $data;
    }
}
else
{
    if (! class_exists($callback))
    {
        // Try auto-load.
        $path = MIDCOM_ROOT . '/' . str_replace('_', '/', $callback) . '.php';
        if (! file_exists($path))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Auto-loading of the callback class {$callback} from {$path} failed: File does not exist.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        require_once($path);
    }

    if (! class_exists($callback))
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("The callback class {$callback} was defined as option for the field {$this->name} but did not exist.", MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }
    $_callback = new $callback($callback_args);
    
    $all_items = $_callback->list_all();
    foreach ($all_items as $id => $data)
    {
        $items[$id] = $data;
    }
    
}

$results = array();
$added_keys = array();
foreach ($items as $id => $item)
{
    foreach ($item as $key => $value)
    {
        if (strpos(strtolower($value), $query) !== false)
        {
            if (! array_key_exists($id, $added_keys))
            {
                $results[] = $item;
                $added_keys[$id] = true;
            }
        }
    }
}

if (empty($results))
{
    echo "    <status>0</status>\n";
    echo "    <errstr>No results</errstr>\n"; //TODO: Localize message
    echo "</response>\n";

    debug_add("No results.");
    debug_pop();
    $_MIDCOM->finish();
    exit();    
}

echo "    <status>1</status>\n";
echo "    <results>\n";
foreach ($results as $i => $result)
{
    echo "        <result>\n";
    echo "            <id>{$result['id']}</id>\n";
    echo "            <name>{$result['name']}</name>\n";
    echo "            <color>{$result['color']}</color>\n";
    echo "        </result>\n";
}
echo "    </results>\n";
echo "</response>\n";

//debug_print_r('Got results',$results);

debug_pop();
$_MIDCOM->finish();
exit();

?>