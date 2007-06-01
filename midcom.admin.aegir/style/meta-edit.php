<?php
$i18n =& $_MIDCOM->get_service("i18n");
$l10n = $i18n->get_l10n("midcom.admin.content");
?>
<h2><?php echo $l10n->get('edit metadata') . ": {$GLOBALS['view_title']}"; ?></h2>
<?php
$GLOBALS['view_dm']->display_form();
?>