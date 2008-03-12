<?php
// Get the form with output buffering for modifications
ob_start();
?>
<div id="midgard_admin_asgard_copytree">
<h2><?php echo $_MIDCOM->i18n->get_string('copy tree', 'midgard.admin.asgard'); ?></h2>
<?php
$data['tree']->show_link = true;
$data['tree']->draw();
?>
</div>
<?php
$tree_select = ob_get_contents();
ob_end_clean();
?>
<h1><?php echo $data['page_title']; ?></h1>
<?php
// Get the form with output buffering for modifications
ob_start();
$data['controller']->display_form();
$form = ob_get_contents();
ob_end_clean();

// Inject to the form
echo preg_replace('/(<form.*?>)/i', '\1' . $tree_select, $form);
?>

