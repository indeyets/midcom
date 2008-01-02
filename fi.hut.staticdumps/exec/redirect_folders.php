<?php
$_MIDCOM->auth->require_valid_user('basic');
//$_MIDCOM->auth->require_admin_user();
$_MIDCOM->cache->content->content_type('text/plain');
$_MIDCOM->cache->content->no_cache();

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

    $node = $nap->get_node($topic->id);
    echo "{$node[MIDCOM_NAV_RELATIVEURL]}\n";

}


?>