<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX,0);

?>
<FORM METHOD=GET ACTION="">
<?php echo $data['l10n']->get('search for:') ?> <INPUT TYPE="text" NAME="q" SIZE=50 VALUE="&(data['query']);">
<INPUT TYPE="submit" VALUE="<?php echo $data['l10n']->get('search') ?>"><BR>
</form>