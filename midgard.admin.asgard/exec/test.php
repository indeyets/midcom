<?php
$_MIDCOM->auth->require_valid_user();
error_reporting(E_ALL);
ini_set('max_execution_time', 0);

uksort($_MIDGARD['schema']['types'], 'strnatcmp');

$methods = get_class_methods('midgard_object_class');
echo "midgard_object_class methods <pre>\n";
print_r($methods);
echo "</pre>\n";
$methods = get_class_methods('midgard_reflection_property');
echo "midgard_reflection_property methods <pre>\n";
print_r($methods);
echo "</pre>\n";

$root_types = midgard_admin_asgard_reflector_tree::get_root_classes();
echo "<hr/>\nroot_types (from midgard_admin_asgard_reflector_tree::get_root_classes())<pre>\n";
print_r($root_types);
echo "</pre>\n";

$topic = new midcom_db_topic('9615374e7b9411db8a62e709ab0e585a585a');
$children = midgard_admin_asgard_reflector_tree::get_child_objects($topic);
echo "<hr/>\nchildren of topic {$topic->extra}<pre>\n";
print_r($children);
echo "</pre>\n";

$parent = midgard_admin_asgard_reflector_tree::get_parent($topic);
$parent_class = get_class($parent);
do
{
    echo "got {$parent_class} {$parent->guid} as parent<br>\n";
    $siblings = midgard_admin_asgard_reflector_tree::get_child_objects($parent);
    echo "** and the following as siblings (parents children) <pre>\n";
    print_r($siblings);
    echo "</pre>\n";
    $parent = midgard_admin_asgard_reflector_tree::get_parent($parent);
    $parent_class = get_class($parent);
} while (!empty($parent));

$_MIDCOM->componentloader->load('org.openpsa.projects');
$type = 'org_openpsa_task';
$ref = new midgard_admin_asgard_reflector_tree($type);
$label = $ref->get_class_label();
echo "Class label for {$type} '{$label}'<br/>\n";
$child_classes = $ref->get_child_classes();
echo "child classes of {$type}<pre>\n";
print_r($child_classes);
echo "</pre>\n";

$type = 'midgard_topic';
$ref = new midgard_admin_asgard_reflector_tree($type);
$label = $ref->get_class_label();
echo "Class label for {$type} '{$label}'<br/>\n";
$child_classes = $ref->get_child_classes();
echo "child classes of {$type}<pre>\n";
print_r($child_classes);
echo "</pre>\n";


echo "<hr/>\nroot objects per type<br/>\n";
foreach($root_types as $schema_type)
{
    $ref = new midgard_admin_asgard_reflector_tree($schema_type);
    $count = $ref->count_root_objects();
    echo "Found {$count} root objects for type <tt>{$schema_type}</tt><br/>\n";
}

echo "<hr/>\n\$_MIDGARD['schema']['types'] (" . count($_MIDGARD['schema']['types']) . ")<pre>\n";
print_r($_MIDGARD['schema']['types']);
echo "</pre>\n";

echo "<hr/>\nLink info per type<br/>\n";
foreach ($_MIDGARD['schema']['types'] as $schema_type => $val)
{
    $ref = new midgard_admin_asgard_reflector($schema_type);
    $label_pro = $ref->get_label_property();
    echo "label property for {$schema_type} is {$label_pro}<br>\n";    
    $info = $ref->get_link_properties();
    echo "link properties for {$schema_type}<pre>\n";
    print_r($info);
    echo "</pre>\n";

    $info = $ref->get_search_properties();
    echo "search properties for {$schema_type}<pre>\n";
    print_r($info);
    echo "</pre>\n";
}

?>