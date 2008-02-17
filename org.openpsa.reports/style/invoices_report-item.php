<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$customer_card = $data['l10n']->get('no customer');
$customerContact = new midcom_db_person($data['invoice']->customerContact);
$customerContact_card = new org_openpsa_contactwidget($customerContact);
if ($data['invoice']->customer)
{
    if ($data['contacts_node'])
    {
        $customer_card = "<a href=\"{$data['contacts_node'][MIDCOM_NAV_FULLURL]}group/{$data['customer']->guid}/\">{$data['customer']->official}</a>";
    }
    else
    {
        $customer_card =  $data['customer']->official;
    }
}
?>
    <tr&(data['row_class']:h);>
        <td class="id">&(data['invoice_string']:h);</td>
    	<td><?php echo strftime('%x', $data['invoice']->due); ?></td>
        <td>&(customer_card:h);</td>
        <td class="contact"><?php echo $customerContact_card->show_inline(); ?></td>
        <td class="sum"><?php echo sprintf("%01.2f", $data['invoice']->sum); ?></td>
        <td><?php echo $data['invoice']->vat . " %"; ?></td>
    </tr>