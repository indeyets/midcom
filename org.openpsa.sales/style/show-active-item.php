<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view_data['salesproject_url'] = "{$prefix}salesproject/{$view_data['salesproject']->guid}/";
$view_data['owner_widget'] = new org_openpsa_contactwidget($view_data['owner']);
?>
<tr>
    <td><?php
        if ($view_data['customer'])
        {
            if ($view_data['contacts_node'])
            {
                echo "<a href=\"{$view_data['contacts_node'][MIDCOM_NAV_FULLURL]}group/{$view_data['customer']->guid}/\">{$view_data['customer']->official}</a>"; 
            }
            else
            {
                echo $view_data['customer']->official; 
            }
        }
        else
        {
            echo $view_data['l10n']->get('no customer');
        }
        ?></td>
    <td><?php echo $view_data['owner_widget']->show_inline(); ?>
    <td><?php echo "<a href=\"{$view_data['salesproject_url']}\">{$view_data['salesproject']->title}</a>"; ?></td>
    <td><?php   
        if ($view_data['salesproject_dmdata']['close_est']['timestamp'])
        {
            echo $view_data['salesproject_dmdata']['close_est']['local_strfulldate'];
        }
        ?></td>
    <td><?php echo $view_data['salesproject_dmdata']['probability'] . '%'; ?></td>    
    <td><?php echo $view_data['salesproject_dmdata']['value']; ?></td>
    <td><?php echo $view_data['salesproject_dmdata']['value'] / 100 * $view_data['salesproject_dmdata']['probability']; ?></td>
    <td><?php 
        $view_data['action'] = $view_data['salesproject']->prev_action;
        midcom_show_style('show-action-simple');
        ?></td>
   <td><?php 
        $view_data['action'] = $view_data['salesproject']->next_action;
        midcom_show_style('show-action-simple');
        ?></td>
</tr>