<?php
$_MIDCOM->auth->require_valid_user();
// Clear OB
while(@ob_end_flush());
// Disable time limit
ini_set('max_execution_time', 0);
// We need this
class net_nemein_beaexporter_dummyobject
{
    var $guid = false;
    var $revised = false;
    var $created = false;
    var $title = 'dummy title';
    var $abstract = 'dummy abstract';
    var $author = false;
    
    function __construct()
    {
        $this->revised = time();
        $this->created = time();
        $this->author = $_MIDGARD['user'];
    }
}


$handler_master = new net_nemein_beaexporter();
$handler_master->mode = 'multiple';


echo "<h1>Export by date</h1>\n";

if (   !isset($_REQUEST['datetime'])
    || !isset($_REQUEST['root_id'])
    || empty($_REQUEST['root_id'])
    || empty($_REQUEST['datetime'])
    )
{
if (!isset($_REQUEST['datetime']))
{
    $_REQUEST['datetime'] = false;
}
if (!isset($_REQUEST['root_id']))
{
    $_REQUEST['root_id'] = false;
}

?>
<form method="post">
<p>Date & time (YYYY-MM-DD HH:MIN): <input type="text" name="datetime" value="<?php echo $_REQUEST['datetime']; ?>" size=15 /> <br/>
Root topic guid/id: <input type="text" name="root_id" value="<?php echo $_REQUEST['root_id']; ?>" size=80 /></p>
<p><input type="submit" value="Dump" /></p>
</form>
<?php
}
else
{
    $start = strtotime($_REQUEST['datetime']);
    $root_topic = new midcom_db_topic($_REQUEST['root_id']);
    $do = true;
    if (   !is_object($root_topic)
        || !$root_topic->id)
    {
        echo "<p>ERROR: Could not resolve topic '{$_REQUEST['root_id']}'</p>\n";
        $do = false;
    }
    if ($start < 10)
    {
        echo "<p>ERROR: Could not resolve time '{$_REQUEST['datetime']}'</p>\n";
        $do = false;
    }
    if ($do)
    {
        echo "<p>Dumping data from " . date('Y-m-d H:i', $start) . " onwards</p>\n";
        // First thing, raise the lock flag
        $handler_master->set_lock();
        
        // Start with deletes
        echo "<p>Dumping delete log<br/>\n";
        $qb = net_nemein_beaexporter_state_dba::new_query_builder();
        $qb->add_constraint('timestamp', '>', $start);
        $qb->add_constraint('objectaction', '=',  'deleted');
        $states = $qb->execute();
        foreach ($states as $state)
        {
            debug_add("state\n===\n" . sprint_r($state) . "===\n");
            flush();
            $dummyobject = new net_nemein_beaexporter_dummyobject();
            $dummyobject->guid = $state->objectguid;
            $handler = $handler_master; // NOTE: this must be by copy so when upgrading for PHP5 remember that
            debug_add("dummyobject before\n===\n" . sprint_r($dummyobject) . "===\n");
            if (!$handler->deleted($dummyobject))
            {
                echo "<p>Error when dumping delete for object {$dummyobject->guid}</p>\n";
                continue;
            }
            debug_add("dummyobject after\n===\n" . sprint_r($dummyobject) . "===\n");
            echo "Delete command dumped for object {$dummyobject->guid}<br>\n";
        }
        echo "</p>\n\n";

        // Then dump articles
        echo "<p>Dumping articles<br/>\n";
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('metadata.revised', '>', $start);
        $articles = $qb->execute();
        foreach ($articles as $article)
        {
            if (!mgd_is_in_topic_tree($root_topic->id, $article->topic))
            {
                // Article is not in correct tree, skip it
                continue;
            }
            flush();
            $handler = $handler_master; // NOTE: this must be by copy so when upgrading for PHP5 remember that
            if (!$handler->updated($article))
            {
                echo "<p>Error when dumping  object {$article->guid}</p>\n";
                continue;
            }
            echo "Object {$article->guid} dumped<br>\n";
        }
        echo "</p>\n\n";

        // All done, clear lock
        $handler_master->unset_lock();
        echo "<p>Done</p>\n";
    }
}