<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$data['invoice_url'] = "{$prefix}invoice/{$data['invoice']->guid}/";

$customer_contact = new midcom_db_person($data['invoice']->customerContact);
$customer_card = new org_openpsa_contactwidget($customer_contact);
$class = 'odd';
if ($data['even'])
{
    $class = 'even';
}
?>
<tr class="&(class);">
    <td class="id"><?php echo "<a href=\"{$data['invoice_url']}\">{$data['invoice']->invoiceNumber}</a>"; ?></td>
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
    <td class="contact"><?php echo $customer_card->show_inline(); ?></td>
    <td class="sum"><?php echo sprintf("%01.2f", $data['invoice']->sum); ?></td>
    <td><?php echo strftime('%x', $data['invoice']->due); ?></td>
    <td>
    <?php
    if ($data['list_type'] != 'paid')
    {
        if ($_MIDCOM->auth->can_do('midgard:update', $data['invoice']))
        {
            $next_marker_url = $prefix . "invoice/mark_" . $data['next_marker'] . "/" . $data['invoice']->guid . ".html";
            ?>
            <form method="post" action="&(next_marker_url);">
                <button type="submit" name="midcom_helper_toolbar_submit"><?php 
                echo $data['l10n']->get('mark ' . $data['next_marker']); 
                ?></button>
              </form><?
        }
    }
    else
    {
        echo strftime('%x', $view_data['invoice']->paid);
    }
    ?>
    </td>
</tr>