<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

echo "<p>" . sprintf($data['l10n']->get('no reservations on %s'), strftime('%x', $data['show_date'])) . "</p>\n";
?>