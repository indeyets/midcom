<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2>&(data['title']);</h2>

<?php
if (isset($data['message']))
{
    echo $data['message'];
}
if ($data['show_form'])
{
?>
<form method="post">
    <input type="submit" class="delete" name="org_maemo_devcodes_delete_confirm" value="<?php echo $data['l10n_midcom']->get('delete'); ?>" />
    <input type="submit" class="cancel" name="org_maemo_devcodes_delete_cancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>
<?php
}
?>