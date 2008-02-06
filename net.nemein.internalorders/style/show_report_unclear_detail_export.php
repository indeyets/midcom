<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

//echo $data['l10n']->get('Show-internalorders')."\n";

//$person = mgd_get_person($data['person']);

//echo $person->name."\n";
echo "Nro.;";
echo "Lähettäjä;";
echo "Vastaanottaja;";
echo "Lähetysaika;";
echo "Lähetyksen kokonaissumma;";
echo "Lähetetty;";
echo "Vastaanotettu";


$tmp_sum = 0;
$tmp_quant = 0;
$tmp_quant2 = 0;
$tmp_orders_count = 0;
setlocale(LC_MONETARY, 'fi_FI.UTF');
foreach($data['unclear'] as $sender => $event)
{
$person_tmp_receiver = mgd_get_person($event->extra);
$person_tmp_sender = mgd_get_person($event->creator);

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

    echo "\n";
    echo "<a href=\"".$_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."view/".$event->guid().".html\">".$event->title."</a>;";
    echo $person_tmp_sender->name.";";
    echo $person_tmp_receiver->name.";";
    echo date("d.m.Y G:i", $event->start).";";
    echo str_replace('.', ',', str_replace('EUR', '', money_format('%i', $sub_orders_sum)))."  €;";
    echo $sub_orders_quant_send.";";
    echo $sub_orders_quant_received.";";
    $tmp_sum = $tmp_sum + $sub_orders_sum;
    $tmp_quant = $tmp_quant + $sub_orders_quant_send;
    $tmp_quant2 = $tmp_quant2 + $sub_orders_quant_received;
    $tmp_orders_count++;
    
}
echo "\n\n";

echo ";;Yhteensä;";
echo $tmp_orders_count." kpl;";
echo str_replace('.', ',', str_replace('EUR', '', money_format('%i', $tmp_sum)))." €";
echo $tmp_quant.";";
echo $tmp_quant2.";";
echo "\n\n";
?>