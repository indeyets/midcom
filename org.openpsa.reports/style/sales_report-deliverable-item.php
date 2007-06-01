<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$customer = $data['customer'];
$salesproject = $data['salesproject'];
$deliverable = $data['salesproject'];
?>
    <tr&(data['row_class']:h);>
        <td class="invoices">
            <ul>
                &(data['invoice_string']:h);
            </ul>
        </td>

        <?php
        if ($data['handler_id'] != 'deliverable_report')
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
        <td>&(data['price']);</td>
        <td>&(data['cost']);</td>
        <td>&(data['profit']);</td>
        <td>&(data['calculation_basis']);</td>
    </tr>