<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<tr>
    <td>
        <input type="checkbox"<?php echo $view_data['disabled']; ?> name="org_openpsa_invoices_invoice_tasks[<?php echo $view_data['task']->id; ?>]" checked="checked" value="1" />
    </td>
    <td>
        <?php 
        if ($view_data['projects_node'])
        {
            echo "<a href=\"{$view_data['projects_node'][MIDCOM_NAV_FULLURL]}task/{$view_data['task']->guid}/\">{$view_data['task']->title}</a>\n";
        }
        else
        {
            echo $view_data['task']->title;
        }
        ?>
    </td>
    <td>
        <?php echo $view_data['invoiceable_hours']; ?>
    </td>
    <td>
        <input type="text"<?php echo $view_data['disabled']; ?> size="6" name="org_openpsa_invoices_invoice_tasks_price[<?php echo $view_data['task']->id; ?>]" value="<?php echo $view_data['default_price']; ?>" />
    </td>
</tr>