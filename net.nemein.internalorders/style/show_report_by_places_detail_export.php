<!-- Show-report -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>
<?

$person = mgd_get_person($data['person']);

?>


<table cellpadding="0" cellspacing="0" border="0" class="internalorders">
    <tr>
        <td colspan="6"><h1><?php echo $data['l10n']->get('Show-internalorders'); ?></h1></td>
    </tr>
    <tr><td colspan="6">&nbsp;</td></tr>
    <tr>
        <td colspan="6"><?php echo "<h2>".$person->name."</h2>"; ?></td>
    </tr>
    <tr><td colspan="6">&nbsp;</td></tr>
    <tr>
        <td><strong>Nro.</strong></td>
        <?php
        if ($data['sent_receive'] == 0)
        {
        ?>
        <td><strong>Vastaanottaja</strong></td>
        <?php
        }
        else
        {
        ?>
        <td><strong>L&auml;hett&auml;j&auml;</strong></td>
        <?php } ?>
        <td><strong>L&auml;hetysaika</strong></td>
        <td><strong>L&auml;hetyksen kokonaissumma</strong></td>
        <td><strong>Kokonaism&auml;&auml;r&auml;</strong></td>
    </tr>
<?php

$tmp_sum = 0;
$tmp_quant = 0;
$tmp_orders_count = 0;
setlocale(LC_MONETARY, 'fi_FI.UTF');
foreach($data['detail'] as $sender => $event)
{
if ($data['sent_receive'] == 0)
{
$person_tmp = mgd_get_person($event->extra);
}
else
{
$person_tmp = mgd_get_person($event->creator);
}

/*    echo "<pre>";
    print_r($event);
    echo "<pre>";*/
    $sub_orders_sum = 0;
    $sub_orders_quant_send = 0;
    $sub_orders_quant_received = 0;
    $sub_orders = mgd_list_events($event->id, 'creator');
    if ($sub_orders)
    {
        while ($sub_orders->fetch())
        {
            $order = mgd_get_event($sub_orders->id);
            $sub_orders_sum = $sub_orders_sum + $order->parameter('net.nemein.internalorders', 'sum');
            $sub_orders_quant_send = $sub_orders_quant_send + $order->parameter('net.nemein.internalorders', 'quantity');
            $sub_orders_quant_received = $sub_orders_quant_received + $order->parameter('net.nemein.internalorders', 'quantity_received');
        }
    }

    echo "    <tr>\n";
    echo "        <td><a href=\"".$_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."/view/".$event->guid().".html\">".$event->title."</a></td>";
    echo "        <td>".$person_tmp->name."</td>\n";
    echo "        <td>&nbsp;".date("d.m.Y G:i", $event->start)."</td>\n";
    echo "        <td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $sub_orders_sum)))."</td>\n";
    if ($sub_orders_quant_received == $sub_orders_quant_send)
    {
    echo "        <td align=\"right\">".$sub_orders_quant_send."</td>\n";
    }
    else
    {
    echo "        <td class=\"note\" align=\"right\">".$sub_orders_quant_send."</td>\n";
    }
    echo "    </tr>\n";
    $tmp_sum = $tmp_sum + $sub_orders_sum;
    $tmp_quant = $tmp_quant + $sub_orders_quant_send;
    $tmp_orders_count++;
    
}
echo "\t<tr>\n";
echo "\t\t<td colspan=\"6\">&nbsp;</td>\n";
echo "\t</tr>\n";
echo "\t<tr>\n";
echo "\t\t<td align=\"right\" colspan=\"2\"><strong>Yhteens&auml;</strong></td>\n";
echo "\t\t<td align=\"right\">".$tmp_orders_count."&nbsp;kpl</td>\n";
echo "\t\t<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $tmp_sum)))."</td>\n";
echo "\t\t<td align=\"right\">".$tmp_quant."</td>\n";
echo "\t</tr>\n";
echo "</table>";
?>



<!-- / Show-report -->
