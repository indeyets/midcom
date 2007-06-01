<?php
// Available Request keys: persons

// $data =& $_MIDCOM->get_custom_context_data('request_data');
$title = $data['topic']->extra;
?>
<h1>&(title);</h1>

<?php
midcom_show_style('show-search-form');
?>

<p><?php $data['l10n']->show('no persons matching search found.'); ?></p>