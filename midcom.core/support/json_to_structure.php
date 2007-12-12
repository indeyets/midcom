<?php
if (count($argv) != 2)
{
    die("Usage: php json_to_structure sitemap.json\n");
}

$json_file = $argv[1];
$structure_name = basename($json_file);
if (!file_exists($json_file))
{
    die("Error: File {$json_file} not found\n");
}

$json = file_get_contents($json_file);
$data = json_decode($json);

if (   !is_object($data)
    || !isset($data->pages))
{
    die("Error: faulty JSON data\n");
}

$structure = array();
$structure[$structure_name] = array();
$structure[$structure_name]['name'] = $structure_name;
$structure[$structure_name]['title'] = $structure_name;

$nodes = array();

foreach ($data->pages as $page)
{
    $node = array();
    $node['title'] = $page->name;
    
    // Users can define the components in page notes    
    if ($page->notes)
    {
        // If notes doesn't contain a valid component name fall back to static
        if (!preg_match('/[a-z][a-z0-9]\.[a-z][a-z0-9].[a-z][a-z0-9]/', $page->notes))
        {
            $page->notes = 'net.nehmer.static';
        }

        $node['component'] = $page->notes;
    }
    
    $node_id = substr($page->id, 5);
    $parent_id = substr($page->id, 5, -4);
    $nodes[$node_id] = $node;

    $node['acl'] = array();
    $node['parameters'] = array();
    $node['nodes'] = array();
    if (empty($parent_id))
    {
        // Root node, note the reference here
        $structure[$structure_name]['root'] =& $nodes[$node_id];
    }
    
    if (isset($nodes[$parent_id]))
    {
        // Subnode
        $nodes[$parent_id]['nodes'][$node_id] = $node;
    }
}

function draw_array($array, $prefix = '')
{
    $data = '';
    foreach ($array as $key => $val)
    {
        $data .= $prefix;
        if (!is_numeric($key))
        {
            $data .= "'{$key}' => ";
        }
        
        switch(gettype($val))
        {
            case 'boolean':
                $data .= ($val)?'true':'false';
                break;
            case 'array':
                if (empty($val))
                {
                    $data .= 'array()';
                }
                else
                {
                    $data .= "array\n{$prefix}(\n" . draw_array($val, "{$prefix}    ") . "{$prefix})";
                }
                break;

            default:
                if (is_numeric($val))
                {
                    $data .= $val;
                }
                else
                {
                    $data .= "'{$val}'";
                }
        }
        
        $data .= ",\n";

    }
    return $data;
}

echo draw_array($structure);
?>