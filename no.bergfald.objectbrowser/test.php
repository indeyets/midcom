<?php
/**
 * @Todo: remove this one.
 * @pacakge no.bergfald.objectbrowser
 */

mgd_config_init('midgard.conf');
mgd_auth_midgard("tarjei+Aegirv2", "tarjei",false);

function debug_push_class($v , $v) {}
function debug_pop(){}
function debug_add($var, $var2 = '') {
    print $var . "\n";
}

function midcom_get_snippet_content ($file) {
    print $file . "\n";
}

foreach ($_MIDGARD['schema']['types'] as $key => $val ) {

    print $key . "\n";
}
exit;
include 'schema.php';
//$topics = mgd_list_topics(0);

$schema = new no_bergfald_objectbrowser_schema();

if ($schema->is_leaf('midgard_element')) {
    print "Element is child!\n";
}


$guid = "8e0e98d358a54766a4229435c98dc411";

$page = new midgard_topic();
$page->get_by_id(23);

$obj = new MidgardQueryBuilder('midgard_article');
$obj->add_constraint('topic','=', 22);
//$obj->add_constraint($up_attribute, '=',$page->sitegroup);

$res = $obj->execute();

var_dump($res);
//var_dump($page);
print $page->name . "\n"  . $page->guid . "\n";

if (0) while ($topics->fetch()) {


    print "Topic: " . $topics->id . " " .  $topic->name ."\n";
    
$object = mgd_get_topic(12);
$schema = new no_bergfald_objectbrowser_schema();

$child = 'midgard_article';
$qb = new MidgardQueryBuilder($child);
if (!$qb) {
    print ("Failed to create querybuilder for $child type object!");
}
//$up_attribute = $schema->get_leaf_up_attribute($child);
if (!is_int($object->id)) {
    print "\nObject not id\n";
}
            
            if (!$up_attribute) {
                print("No up attribute for $child" );
            }
            $qb->add_constraint('topic', '=', (int) $topics->id);
           
print "Up attribute: $up_attribute\n";
            if ($object->sitegroup ) {
            }
            
            // $qb->add_order($sort);
            $result = $qb->execute();
        
print_r($result);

print "COUNT:" . count($result) . "\n";
}
?>
