<?php
$_MIDCOM->auth->require_admin_user();
$_MIDCOM->load_library('midcom.helper.reflector');

//Disable limits
// TODO: Could this be done more safely somehow
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);

function approve_object_reflectorrecursive($obj)
{
    $class = get_class($obj);
    // Touch parameters before appoving (which touches the main object)
    $params = $obj->list_parameters();
    foreach ($params as $domain => $domain_params)
    {
        foreach ($domain_params as $name => $value)
        {
            $obj->set_parameter($domain, $name, $value);
        }
    }
    unset($params, $domain_params, $name, $value);
    $meta =& midcom_helper_metadata::retrieve($obj);
    echo "Approving {$class} #{$obj->id}, ";
    $meta->approve();
    echo mgd_errstr() . "<br/>\n";
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
            approve_object_reflectorrecursive($child);
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
<h1>Approve topic tree</h1>
<p>Enter id of topic to start from, current root_topic is <?php echo $default_id; ?>.</p>
<form method="post">
    Root style id: <input name="root_id" type="text" size=5 value="<?php echo $default_id; ?>" />
    <input type="submit" value="approve" />
</form>
<?php
}
else
{
    while(@ob_end_flush());

    $root = (int)$_REQUEST['root_id'];
    $root_topic = new midcom_db_topic($root);
    if (!is_a($root_topic, 'midcom_db_topic'))
    {
        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Could not fetch topic #{$root}");
        // This will exit();
    }

    approve_object_reflectorrecursive($root_topic);

    ob_start();
}
?>