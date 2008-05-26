<?php
$_MIDCOM->auth->require_admin_user();

if (   !isset($_GET['delete'])
    || $_GET['delete'] != 1)
{
    die("Run with ?delete=1 to remove all unapproved objects");
}

$delete_types = $GLOBALS['midcom_component_data']['midcom.helper.replicator']['config']->get('exporter_staging2live_check_approvals_for');

foreach ($delete_types as $type)
{
    $qb = new midgard_query_builder($type);
    $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
    // TODO: With add_constraint_with_property in 1.9 we can make this much more efficient
    $objects = $qb->execute();
    foreach ($objects as $object)
    {
        if ($object->metadata->approved >= $object->metadata->revised)
        {
            // Approved object, skip
            continue;
        }
        
        if (isset($object->title))
        {
            $label = "{$object->title} ({$object->guid})";
        }
        else
        {
            $label = $object->guid;
        }
        
        echo "{$type} {$label} is not approved<br />\n";
        $object->delete();
    }
}
?>