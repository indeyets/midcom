<?php
// Required request keys:
// manage_form_url, approve_action, reject_action, rejectdelete_action, rejectnotice_fieldname

//$data =& $_MIDCOM->get_custom_context_data('request_data');

if ($data['manage_form_url'])
{
?>
<form action="&(data['manage_form_url']);" method="POST">
    <p>
        <input type="submit" name="&(data['approve_action']);" value="<?php $data['l10n']->show('approve registration'); ?>" />
        <input type="submit" name="&(data['reject_action']);" value="<?php $data['l10n']->show('reject registration'); ?>" />
        <input type="submit" name="&(data['rejectdelete_action']);" value="<?php $data['l10n']->show('reject registration and delete registrar'); ?>" /><br />
        <?php $data['l10n']->show('reject reason'); ?><br />
        <textarea name="&(data['rejectnotice_fieldname']);" cols="60" rows="5" wrap="physical"></textarea>
    </p>
</form>
<?php } ?>