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

debug_push_class('midcom_helper_datamanager2_widget_chooser_handler', 'initialize');

debug_print_r('_REQUEST',  $_REQUEST);

// Common variables
$encoding = 'UTF-8';
$items = array();

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

$query = $_REQUEST["query"];
$query = str_replace("*","%", $query);

$map = array
(
    'component', 'class',
    '_callback_class', '_callback_args',
    '_renderer_callback_class', '_renderer_callback_args',
    'constraints', 'searchfields', 'orders',
    'result_headers',
    'auto_wildcards',
    'reflector_key'
);
$extra_params = unserialize(base64_decode($_REQUEST['extra_params']));
foreach ($map as $map_key)
{
    debug_add("map extras :: checking map_key {$map_key}");
    if (   isset($extra_params[$map_key])
        && !empty($extra_params[$map_key]))
    {
        debug_add("found");
        $$map_key = $extra_params[$map_key];
    }
    else
    {
        debug_add("Not found");
        $$map_key = false;
    }
}

if (! empty($reflector_key))
{
    $_MIDCOM->componentloader->load_graceful('midgard.admin.asgard');
}

debug_pop();
debug_push_class('midcom_helper_datamanager2_widget_chooser_handler', 'search');

// Handle automatic wildcards
if (   !empty($auto_wildcards)
    && strpos($query, '%') === false)
{
    switch($auto_wildcards)
    {
        case 'both':
            $query = "%{$query}%";
            break;
        case 'start':
            $query = "%{$query}";
            break;
        case 'end':
            $query = "{$query}%";
            break;
        default:
            debug_add("Don't know how to handle auto_wildcards value '{$auto_wildcards}'", MIDCOM_LOG_WARN);
            break;
    }
}

if (!empty($_callback_class))
{
    if (! class_exists($_callback_class))
    {
        // Try auto-load.
        $path = MIDCOM_ROOT . '/' . str_replace('_', '/', $_callback_class) . '.php';
        if (! file_exists($path))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Auto-loading of the callback class {$_callback_class} from {$path} failed: File does not exist.", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        require_once($path);
    }

    if (! class_exists($_callback_class))
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("The callback class {$_callback_class} was defined as option for the field {$this->name} but did not exist.", MIDCOM_LOG_ERROR);
        debug_pop();
        return false;
    }
    $_callback = new $_callback_class($_callback_args);
    $results = $_callback->run_search($query,&$_REQUEST);
}
else
{
    debug_add("Using component: {$component}");
    debug_add("Using class: {$class}");
    
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
    
    // No fields to search by, abort
    if (empty($searchfields))
    {
        echo "    <status>0</status>\n";
        echo "    <errstr>No fields to search for defined</errstr>\n";
        echo "</response>\n";
        $_MIDCOM->finish();
        exit();
    }

    // if (!empty($reflector_key))
    // {
    //     $qb = new midgard_query_builder($class);
    // }
    // else
    // {
    //     $qb = call_user_func(array($class, 'new_query_builder'));
    // }
    
    $qb = @call_user_func(array($class, 'new_query_builder'));
    if (! $qb)
    {
        debug_add("use midgard_query_builder");
        $qb = new midgard_query_builder($class);
    }
    
    if (   is_array($constraints)
        && !empty($constraints))
    {
        ksort($constraints);
        reset($constraints);
        foreach ($constraints as $key => $data)
        {
            if (   !array_key_exists('field', $data)
                || !array_key_exists('op', $data)
                || !array_key_exists('value', $data)
                || empty($data['field'])
                || empty($data['op'])
                )
            {
                debug_add("addconstraint loop: Constraint #{$key} is not correctly defined, skipping", MIDCOM_LOG_WARN);
                continue;
            }
            debug_add("Adding constraint: {$data['field']} {$data['op']} '{$data['value']}'");
            $qb->add_constraint($data['field'], $data['op'], $data['value']);
        }
    }

    if (preg_match('/^%+$/', $query))
    {
        debug_add('$query is all wildcards, don\'t waste time in adding LIKE constraints');
    }
    else
    {
        $qb->begin_group('OR');
        foreach ($searchfields as $field)
        {
            debug_add("adding search (ORed) constraint: {$field} LIKE '{$query}'");
            $qb->add_constraint($field, 'LIKE', $query);
        }
        $qb->end_group();
    }

    if (is_array($orders))
    {
        ksort($orders);
        reset($orders);
        foreach ($orders as $data)
        {
            foreach($data as $field => $order)
            {
                debug_add("adding order: {$field}, {$order}");
                $qb->add_order($field, $order);
            }
        }
    }
    
    $results = $qb->execute();
    if ($results === false)
    {
        echo "    <status>0</status>\n";
        echo "    <errstr>Error when executing QB</errstr>\n";
        echo "</response>\n";
        $_MIDCOM->finish();
        exit();
    }
}

echo "    <status>1</status>\n";
echo "    <errstr></errstr>\n";

echo "    <results>\n";

foreach ($results as $object)
{
    if (   isset($reflector_key)
        && !empty($reflector_key))
    {
        debug_add("Using reflector with key {$reflector_key}");
        $reflector_type = get_class($object);
        //$reflector_type_fields = array_keys(get_object_vars($object));
    
        // debug_add("Reflector type: {$reflector_type}");
        // debug_print_r("reflector type fields",$reflector_type_fields);
    
        $reflector = new midgard_reflection_property($reflector_type);

        if ($reflector->is_link($reflector_key))
        {
            $linked_type = $reflector->get_link_name($reflector_key);
            // $linked_type_reflector = midgard_admin_asgard_reflector::get($linked_type);
            // $type = $reflector->get_midgard_type($reflector_key);
            // $type_label = midgard_admin_asgard_plugin::get_type_label($linked_type);
        
            // debug_add("Reflector linked_type: {$linked_type}");
            // debug_add("reflector type_label {$type_label}");
            // debug_add("Reflector type: {$type}");
        
            $object = new $linked_type($object->$reflector_key);
        
            // debug_print_r('$object',$object;
        
            // $reflector_tree = new midgard_admin_asgard_reflector_tree($object);
            // debug_print_r('$reflector_tree',$reflector_tree);
        }

        debug_print_r('reflected object',$object);
    }
    
    $id = @$object->id;
    $guid = @$object->guid;
    
    debug_add("adding result: id={$id} guid={$guid}");
    
    echo "      <result>\n";
    echo "          <id>{$id}</id>\n";
    echo "          <guid>{$guid}</guid>\n";
    
    if (   !empty($reflector_key)
        && !$result_headers)
    {
        $value = @$object->get_label();
        debug_add("adding header item: name=label value={$value}");
        echo "          <label><![CDATA[{$value}]]></label>\n";
    }
    else
    {
        foreach ($result_headers as $header_item)
        {
            $item_name = $header_item['name'];
            $value = @$object->$item_name;

            debug_add("adding header item: name={$item_name} value={$value}");
            echo "          <{$item_name}><![CDATA[{$value}]]></{$item_name}>\n";
        }    
    }
    
    echo "      </result>\n";
}

echo "    </results>\n";
echo "</response>\n";

debug_print_r('Got results',$results);

debug_pop();
$_MIDCOM->finish();

?>