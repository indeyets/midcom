<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$header = sprintf($data['l10n']->get('modified since %s'), strftime('%x', $data['edited_since']));
?>
<h1>&(header);</h1>
<?php midcom_show_style('date_form'); ?>
