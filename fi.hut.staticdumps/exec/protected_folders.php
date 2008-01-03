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
$site_topics = $qb->execute();
unset($qb);
array_unshift($site_topics, $site_root);
foreach ($site_topics as $topic)
{
    flush();
    /* This would be the correct way but seems to have issues
    if (   $topic->can_do('midgard:read', 'ANONYMOUS')
        || $topic->can_do('midgard:read', 'EVERYONE'))
    {
        //echo "Topic #{$topic->id} ({$topic->name}) accessible<br>\n";
        continue;
    }
    */
    /* Alternative way, does not work any better
    if (   $_MIDCOM->auth->can_do('midgard:read', $topic, 'ANONYMOUS')
        || $_MIDCOM->auth->can_do('midgard:read', $topic, 'EVERYONE'))
    {
        //echo "Topic #{$topic->id} ({$topic->name}) accessible<br>\n";
        continue;
    }
    */
    /* Lets do it the hard way */
    /** 
     * In fact this way has the advantage that we only list those nodes that
     * have privilege explicitly set, in stead of all that have inherited
     * the deny, which for our use case is in fact better
     */
    $qb = new midgard_query_builder('midcom_core_privilege_db');
    $qb->add_constraint('objectguid', '=', $topic->guid);
    $qb->add_constraint('name', '=', 'midgard:read');
    $qb->add_constraint('value', '=', MIDCOM_PRIVILEGE_DENY);
    $qb->begin_group('OR');
        $qb->add_constraint('assignee', '=', 'EVERYONE');
        $qb->add_constraint('assignee', '=', 'ANONYMOUS');
    $qb->end_group();
    $topic_read_privileges = $qb->execute();
    //var_dump($topic_read_privileges);
    if (empty($topic_read_privileges))
    {
        //echo "Topic #{$topic->id} ({$topic->name}) accessible<br>\n";
        continue;
    }

    $node = $nap->get_node($topic->id);
    //echo "Node {$node[MIDCOM_NAV_RELATIVEURL]}, read denied for EVERYONE/ANONYMOUS<br/>\n";
    echo "{$node[MIDCOM_NAV_RELATIVEURL]}\n";

}

// restart buffering to keep midcom happier
ob_start();
?>