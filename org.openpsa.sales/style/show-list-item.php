<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$data['salesproject_url'] = "{$prefix}salesproject/{$data['salesproject']->guid}/";
$data['owner_widget'] = new org_openpsa_contactwidget($data['owner']);

if ($data['even'])
{
    $class = 'even';
}
else
{
    $class = 'odd';
}
?>
<tr class="&(class);">
    <td class="salesproject"><?php echo "<a href=\"{$data['salesproject_url']}\">{$data['salesproject']->title}</a>"; ?></td>
    <td><?php
        if ($data['customer'])
        {
            if ($data['contacts_node'])
            {
                echo "<a href=\"{$data['contacts_node'][MIDCOM_NAV_FULLURL]}group/{$data['customer']->guid}/\">{$data['customer']->official}</a>";
            }
            else
            {
                echo $data['customer']->official;
            }
        }
        else
        {
            echo $data['l10n']->get('no customer');
        }
        ?></td>
    <td><?php echo $data['owner_widget']->show_inline(); ?>
    <td><?php
        if ($data['salesproject_dmdata']['close_est']['timestamp'])
        {
            echo $data['salesproject_dmdata']['close_est']['local_strfulldate'];
        }
        ?></td>
    <td><?php echo $data['salesproject_dmdata']['probability'] . '%'; ?></td>
    <td><?php echo $data['salesproject_dmdata']['value']; ?></td>
    <td><?php echo $data['salesproject_dmdata']['value'] / 100 * $data['salesproject_dmdata']['probability']; ?></td>
    <td><?php echo $data['salesproject_dmdata']['profit']; ?></td>
    <td><?php
        $data['action'] = $data['salesproject']->prev_action;
        midcom_show_style('show-action-simple');
        ?></td>
   <td><?php
        $data['action'] = $data['salesproject']->next_action;
        midcom_show_style('show-action-simple');
        ?></td>
</tr>