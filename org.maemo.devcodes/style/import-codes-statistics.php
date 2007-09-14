<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$stats =& $data['import_stats'];
$device =& $data['device'];
?>
<h2><?php echo sprintf($data['l10n']->get('imported %d new codes for "%s"'), $stats['ok'], $device->title); ?></h2>
<p><a href="&(prefix);code/list/&(device.guid);.html"><?php echo sprintf($data['l10n']->get('codes for %s'), $device->title); ?></a></p>
<h3><?php echo $data['l10n']->get('import statistics'); ?></h3>
<table>
    <tr>
        <th><?php echo $data['l10n']->get('ok'); ?></th>
        <td>&(stats['ok']);</td>
    </tr>
    <tr>
        <th><?php echo $data['l10n']->get('duplicate'); ?></th>
        <td>&(stats['duplicate']);</td>
    </tr>
    <tr>
        <th><?php echo $data['l10n']->get('failed'); ?></th>
        <td>&(stats['failed']);</td>
    </tr>
</table>
