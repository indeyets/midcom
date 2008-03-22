<h1>&(data['view_title']:h);</h1>
<form action="&(_MIDGARD['uri']);" method="post">
    <p>
        <input type="submit" name="midgard_admin_asgard_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
        <input type="submit" name="midgard_admin_asgard_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
    </p>
</form>
<div class="object_view">
   <?php $data['datamanager']->display_view(); ?>
</div>
<form action="&(_MIDGARD['uri']);" method="post">
    <p>
        <input type="submit" name="midgard_admin_asgard_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
        <input type="submit" name="midgard_admin_asgard_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
    </p>
</form>
<h2><?php echo $data['l10n']->get('all of the following items will be deleted'); ?></h2>
<div id="midgard_admin_asgard_deletetree" class="midgard_admin_asgard_tree">
<?php
// Show a list of all of the items that will be deleted
$data['tree']->view_link = true;
$data['tree']->edit_link = true;
$data['tree']->draw();
?>
</div>
