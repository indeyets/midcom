<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo sprintf($_MIDCOM->i18n->get_string('manage feeds of %s', 'net.nemein.rss'), $data['folder']->extra); ?></h1>

<ul>
