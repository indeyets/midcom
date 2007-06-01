<?php
// Available Request keys: groups

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$title = $data['topic']->extra;
?>
<h1>&(title);</h1>

<?php
midcom_show_style('show-search-form');
?>

<p><?php $data['l10n']->show('no groups matching search found.'); ?></p>