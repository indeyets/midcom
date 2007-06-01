<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h1><?php echo $data['topic']->extra; ?>: <?php $data['l10n']->show('available feeds'); ?></h1>

<p><?php $data['l10n']->show('available feeds introduction'); ?></p>

<ul>
    <li><a href="&(prefix);rss.xml"><?php $data['l10n']->show('rss 2.0 feed'); ?></a></li>
    <li><a href="&(prefix);rss1.xml"><?php $data['l10n']->show('rss 1.0 feed'); ?></a></li>
    <li><a href="&(prefix);rss091.xml"><?php $data['l10n']->show('rss 0.91 feed'); ?></a></li>
    <li><a href="&(prefix);atom.xml"><?php $data['l10n']->show('atom feed'); ?></a></li>
</ul>

<p><a href="&(prefix);"><?php $data['l10n_midcom']->show('back'); ?></a></p>