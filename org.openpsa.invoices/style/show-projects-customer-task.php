<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<tr>
    <td>
        <input type="checkbox"<?php echo $data['disabled']; ?> name="org_openpsa_invoices_invoice_tasks[<?php echo $data['task']->id; ?>]" checked="checked" value="1" />
    </td>
    <td>
        <?php
        if ($data['projects_node'])
        {
            echo "<a href=\"{$data['projects_node'][MIDCOM_NAV_FULLURL]}task/{$data['task']->guid}/\">{$data['task']->title}</a>\n";
        }
        else
        {
            echo $data['task']->title;
        }
        ?>
    </td>
    <td>
        <?php echo $data['invoiceable_hours']; ?>
    </td>
    <td>
        <input type="text"<?php echo $data['disabled']; ?> size="6" name="org_openpsa_invoices_invoice_tasks_price[<?php echo $data['task']->id; ?>]" value="<?php echo $data['default_price']; ?>" />
    </td>
</tr>