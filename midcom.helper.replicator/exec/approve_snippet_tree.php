<?php
$_MIDCOM->auth->require_admin_user();

//Disable limits
// TODO: Could this be done more safely somehow
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);


if (   !isset($_REQUEST['root_id'])
    || empty($_REQUEST['root_id']))
{
    $default_id = ''; 
    $sd = new midgard_snippetdir();
    $sd->get_by_path($GLOBALS['midcom_config']['midcom_sgconfig_basedir']);
    if ($sd->id)
    {
        $default_id = $sd->id; 
    }
?>
<h1>Approve snippet tree</h1>
<p>Enter id of snippetdir to start from<?php
if ($default_id)
{
    echo ", we found snippetdir <tt>{$GLOBALS['midcom_config']['midcom_sgconfig_basedir']}</tt> with id {$sd->id}";
}
?>.</p>
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
    $qb = midcom_baseclasses_database_snippetdir::new_query_builder();
    $qb->begin_group('OR');
        $qb->add_constraint('id', '=', $root);
        $qb->add_constraint('up', 'INTREE', $root);
    $qb->end_group();
    $styles = $qb->execute();
    unset($qb);
    $qb = midcom_baseclasses_database_snippet::new_query_builder();
    $qb->add_constraint('up', 'INTREE', $root);
    $elements = $qb->execute();
    unset($qb);
    $objects = array_merge($styles, $elements);
    unset($styles, $elements);
    foreach ($objects as $obj)
    {
        $class = get_class($obj);
        $meta =& midcom_helper_metadata::retrieve($obj);
        echo "Approving {$class} #{$obj->id}, ";
        $meta->approve();
        echo mgd_errstr() . "<br/>\n";
        flush();
    }
    ob_start();
}
?>