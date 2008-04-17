<?php
$_MIDCOM->auth->require_admin_user();

//Disable limits
// TODO: Could this be done more safely somehow
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);


if (   !isset($_REQUEST['root_id'])
    || empty($_REQUEST['root_id']))
{
    $host = new midcom_db_host($_MIDGARD['host']);
    $page = new midcom_db_page($_MIDGARD['page']);
    $default_id = $host->style;
    if (!empty($page->style))
    {
        $default_id = $page->style;
    }
    if ($default_id == 0)
    {
        $default_id = '';
    }
?>
<h1>Approve style tree</h1>
<p>Enter id of style to start from, current host style is <?php echo $host->style; ?> and page style is <?php echo $page->style; ?>.</p>
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
    $qb = midcom_db_style::new_query_builder();
    $qb->begin_group('OR');
        $qb->add_constraint('id', '=', $root);
        $qb->add_constraint('up', 'INTREE', $root);
    $qb->end_group();
    $styles = $qb->execute();
    unset($qb);
    $qb = midcom_db_element::new_query_builder();
    $qb->add_constraint('style', 'INTREE', $root);
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