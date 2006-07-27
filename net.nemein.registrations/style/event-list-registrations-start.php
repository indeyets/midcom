<?php
// Available request keys:
// event, view_url, edit_url, delete_url
// registrations

$data =& $_MIDCOM->get_custom_context_data('request_data');
$title = sprintf($data['l10n']->get('list registrations of %s'), $data['event']->title);
?>
<h2><?php echo $data['topic']->extra; ?></h2>
<h3>&(title);</h3>

<ul>