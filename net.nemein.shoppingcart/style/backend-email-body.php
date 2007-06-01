<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
echo  '## ' . $data['l10n']->get('ordered items') . "\n\n";
midcom_show_style('backend-email-body_cart');
echo "\n";
echo  '## ' . $data['l10n']->get('contact information') . "\n\n";
midcom_show_style('backend-email-body_formdata');
?>