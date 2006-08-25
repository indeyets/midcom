<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$customer = $view_data['customer'];
$salesproject = $view_data['salesproject'];
$deliverable = $view_data['salesproject'];
?>
    <tr&(view_data['row_class']:h);>
        <td class="invoices">
            <ul>
                &(view_data['invoice_string']:h);
            </ul>
        </td>
        
        <?php
        if ($view_data['handler_id'] != 'deliverable_report')
        {
            $owner = new midcom_db_person($salesproject->owner);
            $owner_card = new org_openpsa_contactwidget($owner);
            ?>
            <td><?php echo $owner_card->show_inline(); ?></td>
            <?php
        }
        ?>
        <td>&(customer.official);</td>
        <td>&(salesproject.title);</td>
        <td>&(deliverable.title);</td>
        <td>&(view_data['price']);</td>
        <td>&(view_data['cost']);</td>
        <td>&(view_data['profit']);</td>
        <td>&(view_data['calculation_basis']);</td>
    </tr>
