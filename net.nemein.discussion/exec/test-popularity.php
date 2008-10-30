<?php
require_once(MIDCOM_ROOT . '/net/nemein/discussion/calculator.php'); 
$_MIDCOM->auth->require_admin_user();

$calculator = new net_nemein_discussion_calculator();

$cache = false;
if (   isset($_GET['cache'])
    && $_GET['cache'] == true)
{
    $cache = true;
}

$qb = net_nemein_discussion_thread_dba::new_query_builder();
$qb->add_constraint('posts', '>', 0);
$qb->add_order('metadata.score', 'DESC');
$threads = $qb->execute();
$threads_array = array();

foreach ($threads as $thread)
{
    $popularities = $calculator->calculate_thread($thread, $cache);
    $popularity_string = '';
    foreach ($popularities as $source => $popularity)
    {
        $popularity_string .= " {$source}: {$popularity}";
    }
    $popularity_string = trim($popularity_string);
    $thread->tmp = $popularity_string;
    $threads_array[sprintf('%003d', $popularities['popularity'])."_{$thread->guid}"] = $thread;
}
echo count($threads_array) . " threads processed.";

krsort($threads_array);
echo "<ul>\n";
foreach ($threads_array as $thread)
{
    echo "<li>{$thread->name} ({$thread->tmp})</li>\n";
}
echo "</ul>\n";

?>