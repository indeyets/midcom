<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view_data['invoice_url'] = "{$prefix}invoice/{$view_data['invoice']->guid}/";

$customer_contact = new midcom_db_person($view_data['invoice']->customerContact);
$customer_card = new org_openpsa_contactwidget($customer_contact);
?>
<tr>
    <td class="id"><?php echo "<a href=\"{$view_data['invoice_url']}\">{$view_data['invoice']->invoiceNumber}</a>"; ?></td>
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
    <td class="contact"><?php echo $customer_card->show_inline(); ?></td>    
    <td class="sum"><?php echo sprintf("%01.2f", $view_data['invoice']->sum); ?></td>
    <td><?php echo strftime('%x', $view_data['invoice']->due); ?></td>
    <?php 
    if ($view_data['list_type'] != 'open')
    {
        ?>
        <td><?php 
        if ($view_data['invoice']->paid)
        {
            echo strftime('%x', $view_data['invoice']->paid); 
        }
        else
        {
            echo $view_data['l10n']->get('not paid');
        }
        ?></td>
        <?php
    }
    ?>    
</tr>