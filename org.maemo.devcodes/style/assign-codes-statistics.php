<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2>&(data['title']);</h2>
<?php
foreach ($data['action_statictics'] as $action => $stats)
{
    if ($action === 'noop')
    {
        continue;
    }
    echo "<h3>{$data['actions'][$action]}</h3>\n";
    $op_count = count($data['object_actions'][$action]);
?>
<table>
    <tr>
        <th><?php echo $data['l10n']->get('processed ok'); ?></th>
        <td>&(stats['ok']);</td>
    </tr>
    <tr>
        <th><?php echo $data['l10n']->get('failed'); ?></th>
        <td>&(stats['failed']);</td>
    </tr>
    <tr>
        <th><?php echo $data['l10n']->get('total'); ?></th>
        <td>&(op_count);</td>
    </tr>
</table>
<?php
}
?>