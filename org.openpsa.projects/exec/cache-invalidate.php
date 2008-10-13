<?php
$_MIDCOM->auth->require_admin_user();

// Ensure this is not buffered
$_MIDCOM->cache->content->enable_live_mode();
while(@ob_end_flush())

// TODO: Could this be done more safely somehow
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);

echo "<h1>Invalidating task caches:</h1>\n";

$qb = org_openpsa_projects_task_dba::new_query_builder();
$qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
$tasks = $qb->execute();

foreach ($tasks as $task)
{
    $start = time();
    echo "Invalidating cache for task #{$task->id} {$task->title}... \n";
    flush();
    if ($task->update_cache())
    {
        $time_consumed = time() - $start;
        echo "OK ({$time_consumed} secs, task has {$task->hourCache}h reported)";
    }
    else
    {
        echo "ERROR: " . mgd_errstr();
    }
    echo "<br />\n";
}
?>
<p>All done</p>