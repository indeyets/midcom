<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo sprintf($data['l10n']->get('delete %s'), $data['layout']); ?></h1>
<?php 
$data['datamanager']->display_view(); ?>
<br /><br />
<form method="post" action="&(_MIDGARD['uri']);">
    <input type="submit" name="f_submit" value="<?php echo $data['l10n_midcom']->get('delete'); ?>" />
    <input type="submit" name="f_cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>