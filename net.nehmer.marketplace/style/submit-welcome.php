<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

// Available request data: category_lister
?>

<h2><?php echo $data['topic']->extra . ': ' . $data['l10n']->get('submit new entry'); ?></h2>

<ul>
    <li><a href="&(prefix);submit/ask.html"><?php $data['l10n']->show('ask'); ?></a></li>
    <li><a href="&(prefix);submit/bid.html"><?php $data['l10n']->show('bid'); ?></a></li>
</ul>