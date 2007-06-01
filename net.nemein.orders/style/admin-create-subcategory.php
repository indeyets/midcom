<?php

/*
$config =& $_MIDCOM->get_custom_context_data("configuration");
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$config_dm =& $_MIDCOM->get_custom_context_data("configuration_dm");
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
*/

$l10n =& $_MIDCOM->get_custom_context_data("l10n");
$l10n_midcom =& $_MIDCOM->get_custom_context_data("l10n_midcom");

?>

<h2><?php echo $l10n->get('create subcategory'); ?></h2>

<form method="post" action="">
	<p><?php echo $l10n->get('enter category name');?>:</p>
	<p>
		<input type="text" name="f_name" value="" size="50" maxlength="250" />
		<input type="submit" name="f_submit" value="<?php echo $l10n_midcom->get('OK') ?>" /> 
	</p> 
</form> 