<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data('configuration_dm');
$config =& $_MIDCOM->get_custom_context_data('configuration');
$l10n =& $_MIDCOM->get_custom_context_data('l10n');
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
*/

$l10n_midcom =& $_MIDCOM->get_custom_context_data('l10n_midcom');

?>

</table>

<p><input type="submit" name="form_submit" value="<?php echo $l10n_midcom->get('save'); ?>" /></p>

</form>