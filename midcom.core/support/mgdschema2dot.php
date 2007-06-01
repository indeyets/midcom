<?php
/**
 * Script for generating GraphViz graphs from MgdSchema files
 *
 * Henri Bergius <henri.bergius@iki.fi>
 */
if (count($_SERVER['argv']) != 2)
{
    die("mgdschema2dot.php - Generate GraphViz graphs from MgdSchema files\n\nUsage:\n $ php mgdschema2dot.php mgdschema.xml\n\nExample:\n $ php mgdschema2dot.php mgdschema.xml |neato -Tpng > mgdschema.png\n");
}

if (!function_exists('simplexml_load_string'))
{
    die("PHP simplexml extension is required to run this.\n");
}

if (!file_exists($_SERVER['argv'][1]))
{
    die("File '{$_SERVER['argv'][1]}' not found.\n");
}

function mgdschema2dot_type_skeleton()
{
    return array
    (
        'parent' => '',
        'parentfield' => '',
        'properties' => array
        (
            'guid' => array
            (
                'type' => 'string',
            ),
            'metadata' => array
            (
                'type' => 'midgard_metadata object',
            ),
        ),
    );
}

$xml = file_get_contents($_SERVER['argv'][1]);
$schema = simplexml_load_string($xml);

// Run through the types
$types = array();
$links = array();

foreach ($schema->type as $type)
{
    $type_name = '';
    foreach ($type->attributes() as $attribute => $value)
    {
        if ($attribute == 'name')
        {
            $type_name = sprintf('%s', $value);
        }
    }
    
    if (empty($type_name))
    {
        // No name for this type, skip
        continue;
    }
    
    $types[$type_name] = mgdschema2dot_type_skeleton();
    
    foreach ($type->property as $property)
    {
        $property_array = array();
        $property_name = '';
        foreach ($property->attributes() as $attribute => $value)
        {
            switch ($attribute)
            {
                case 'name':
                    $property_name = sprintf('%s', $value);
                    break;
                
                case 'link':
                    $link = explode(':', sprintf('%s', $value));
                    $linked_type = $link[0];
                    $linked_property = $link[1];
                    if (!isset($types[$linked_type]))
                    {
                        $types[$linked_type] = mgdschema2dot_type_skeleton();
                    }
                    $types[$linked_type]['properties'][$linked_property] = array();
                    
                    $links["\"{$type_name}\":<{$property_name}>"] = "\"{$linked_type}\":<{$linked_property}>";
                    
                    // Fall-through intentional
                default:
                    $property_array[$attribute] = sprintf('%s', $value);
                    break;
            }
        }
        
        if (empty($property_name))
        {
            // Invalid property, skip
            continue;
        }
        
        $types[$type_name]['properties'][$property_name] = $property_array;
    }
}

// Graph headers
echo "digraph g {\n";
echo "graph [\n";
echo "rankdir = \"LR\"\n";
//echo "overlap = \"scale\"\n";
echo "overlap = \"false\"\n";
//echo "mode = \"ipsep\"\n";
echo "];\n";

// Populate types
foreach ($types as $type_name => $type_definition)
{
    echo "\"{$type_name}\" [\n";
    echo "label = \"{<f0> \\l{$type_name}";
    
    // Go through the properties
    $i = 0;
    foreach ($type_definition['properties'] as $property => $property_definition)
    {
        $i++;
        if (!isset($property_definition['type']))
        {
            if ($property == 'id')
            {
                $property_definition['type'] = 'integer';
            }
            else
            {
                $property_definition['type'] = 'string';
            }
        }
        
        echo "|<{$property}> {$property}: {$property_definition['type']}";
    }
    
    echo "}\"\n";
    echo "shape = \"Mrecord\"\n";
    echo "style = \"filled\"\n";
    echo "fillcolor = \"silver\"\n";
    echo "];\n";
}

// Populate links
foreach ($links as $from => $to)
{
    echo "{$from} -> {$to}\n";
}

// Graph footers
echo "}\n";
?>