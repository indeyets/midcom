<?php
$_MIDCOM->auth->require_valid_user('basic');
//$_MIDCOM->auth->require_admin_user();
$_MIDCOM->cache->content->content_type('text/plain');
$_MIDCOM->cache->content->no_cache();
// Disable limits and buffering
ini_set('memory_limit', -1);
ini_set('max_execution_time', 0);
while(@ob_end_flush());
// Give some output right at the start
echo "\n"; flush();

$nap = new midcom_helper_nav();

$site_root = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
$qb = midcom_db_topic::new_query_builder();
$qb->add_constraint('up', 'INTREE', $site_root->id);
$qb->add_constraint('component', '=', 'net.nemein.redirector');
$site_topics = $qb->execute();
unset($qb);
array_unshift($site_topics, $site_root);
foreach ($site_topics as $topic)
{
    flush();
    $node = $nap->get_node($topic->id);
    echo "{$node[MIDCOM_NAV_RELATIVEURL]}\n";

}

// restart buffering to keep midcom happier
ob_start();
?>