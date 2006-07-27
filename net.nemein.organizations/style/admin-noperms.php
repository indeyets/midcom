<?php
global $view;
$data = $view->get_array();
?>
<h3><?php echo $GLOBALS["view_l10n"]->get("no permissions for"); ?>: &(data["official]);</h3>

<p>
<?php echo $GLOBALS["view_l10n"]->get("your account has insufficient permissions"); ?>
</p>