<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<h1><?php echo $data['l10n']->get('register'); ?></h1>
<p>
Really register? Cancel will remove your invitation from the database.
</p>
<form method="post">
<input type="submit" name="net_nehmer_account_register_invitation" value="<?php echo $data['l10n']->get('register'); ?>"/>
<input type="submit" name="net_nehmer_account_cancel_invitation" value="<?php echo $data['l10n']->get('cancel'); ?>"/>
</form>


