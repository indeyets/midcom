<?php

/*
$config_dm =& $_MIDCOM->get_custom_context_data('configuration_dm');
$config =& $_MIDCOM->get_custom_context_data('configuration');
$l10n_midcom =& $_MIDCOM->get_custom_context_data('l10n_midcom');
$topic = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_CONTENTTOPIC);
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
*/
$l10n =& $_MIDCOM->get_custom_context_data('l10n');

$midgard = mgd_get_midgard();
$url_me = $midgard->uri;

?>

<h2><?php echo $l10n->get('update storage amount'); ?></h2>

<p><?php echo $l10n->get('update storage amount helptext'); ?></p>

<form method="POST" action="&(url_me);">

<table cellspacing="5" cellpadding="0" border="0" class="net_nemein_orders_update_storage">

<tr>
    <th><?php echo $l10n->get('code'); ?></th>
    <th><?php echo $l10n->get('product title'); ?></th>
    <th><?php echo $l10n->get('stockmin'); ?></th>
    <th><?php echo $l10n->get('available'); ?></th>
    <th><?php echo $l10n->get('instock'); ?></th>
    <th><?php echo $l10n->get('delivery amount'); ?></th>
</tr>