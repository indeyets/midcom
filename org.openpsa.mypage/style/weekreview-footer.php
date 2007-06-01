<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<p>
<?php
echo $data['l10n']->get('totals') . ': ' . sprintf($data['l10n']->get('%d hours (%d invoiceable) reported'), $data['week_hours_total'], $data['week_hours_invoiceable']);
?>
</p>