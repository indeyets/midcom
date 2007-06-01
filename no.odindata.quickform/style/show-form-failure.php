<?php
// Bind the view data, remember the reference assignment:
// $data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<p>
<?php 
if (   isset($_REQUEST['no_odindata_quickform_error_message'])
    && !empty($_REQUEST['no_odindata_quickform_error_message']))
{
    echo sprintf($data['l10n']->get('failed to send message, error: "%s"'), $_REQUEST['no_odindata_quickform_error_message']);
    echo "<br/>\n" . $data['l10n']->get('see debug log for more information');
}
else
{
    echo $data['l10n']->get('failed to send message');
    echo "<br/>\n" . $data['l10n']->get('see debug log for more information');
}
?>
</p>
