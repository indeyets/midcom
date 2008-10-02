<?php
$_MIDCOM->auth->require_admin_user();
$_MIDCOM->load_library('midcom.helper.reflector');

function reqeue_object_reflectorrecursive(&$obj, &$qm)
{
    $class = get_class($obj);
    echo "Re-Queuing {$class} #{$obj->id}, ";
    $stat = $qm->add_to_queue($obj);
    echo (int)$stat . "<br>\n";
    flush();
    $children = midcom_helper_reflector_tree::get_child_objects($obj);
    if (empty($children))
    {
        return;
    }
    foreach ($children as $child_class => $child_objects)
    {
        foreach ($child_objects as $k => $child)
        {
            reqeue_object_reflectorrecursive($child, $qm);
            unset($child_objects[$k], $children[$child_class][$k], $child);
        }
        unset($children[$child_class], $child_objects);
    }
    unset($children);
}

if (   !isset($_REQUEST['root_id'])
    || empty($_REQUEST['root_id']))
{
    $site_root = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ROOTTOPIC);
    $default_id = $site_root->id;

    if ($default_id == 0)
    {
        $default_id = '';
    }
?>
<h1>Re-qeue topic tree</h1>
<p>Enter id of topic to start from, current root_topic is <?php echo $default_id; ?>.</p>
<form method="post">
    Root topic id: <input name="root_id" type="text" size=5 value="<?php echo $default_id; ?>" />
    <input type="submit" value="reqeue" />
</form>
<?php
}
else
{

    $root = (int)$_REQUEST['root_id'];
    $root_topic = new midcom_db_topic($root);
    if (!is_a($root_topic, 'midcom_db_topic'))
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not fetch topic #{$root}");
        // This will exit();
    }

    $GLOBALS['midcom_helper_replicator_exporter_retry_mode'] = true;
    $qm =& midcom_helper_replicator_queuemanager::get();
    while(@ob_end_flush());
    //Disable limits
    // TODO: Could this be done more safely somehow
    @ini_set('memory_limit', -1);
    @ini_set('max_execution_time', 0);

    echo "Starting<br>\n"; flush();
    reqeue_object_reflectorrecursive($root_topic, $qm);
    echo "Done<br>\n"; flush();

    ob_start();
}
?>