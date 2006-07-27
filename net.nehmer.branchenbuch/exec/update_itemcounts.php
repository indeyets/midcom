<?php $_MIDCOM->auth->require_admin_user(); ?>
<pre>
<?php
$qb = net_nehmer_branchenbuch_branche::new_query_builder();
$qb->add_constraint('parent', '=', '');
$types = $qb->execute();

debug_push('midcom-exec-net.nehmer.branchenbuch::update_itemcounts');

if ($types)
{
    debug_add('Disabling user-abort.');
    ignore_user_abort();

    foreach ($types as $type)
    {
        echo "Processing Type '{$type->name}' ({$type->type})...\n";
        debug_add("Processing Type '{$type->name}' ({$type->type})...");

        // Keep the script running.
        set_time_limit(120);

        $qb = net_nehmer_branchenbuch_branche::new_query_builder();
        $qb->add_constraint('type', '=', $type->type);
        $branchen = $qb->execute();
        if ($branchen)
        {
            foreach ($branchen as $branche)
            {

                echo "\tUpdating category '{$branche->name}' (#{$branche->id})...\n";
                debug_add ("Updating category '{$branche->name}' (#{$branche->id})...");
                $branche->update_item_count();
            }
        }
    }
}

echo "Done.\n";

debug_pop();

?>
</pre>