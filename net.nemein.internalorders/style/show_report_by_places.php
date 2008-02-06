<!-- Show-report -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<script>
function change_view(divToChange)
{
    if (divToChange == '1')
    {
        elementToChange = document.getElementById('reports_flick_1');
    }
    else if (divToChange == '2')
    {
        elementToChange = document.getElementById('reports_flick_2');
    }
    else if (divToChange == '3')
    {
        elementToChange = document.getElementById('reports_flick_3');
    }
    if(elementToChange.style.display == 'none')
    {
        elementToChange.style.display = 'block';
    }
    else
    {
        elementToChange.style.display = 'none';
    }
}


</script>

<style>
#reports_flick_1, #reports_flick_2, #reports_flick_3
{
    display: none;
}

</style>


<div style="float:right; margin:20px;"><a href="../">Omat</a></div>
<h1><?php echo $data['l10n']->get('Show-internalorders'); ?></h1>

<?php
echo "Muokattavissa: ".$data['open']."<br />\n";
echo "Avoimet: ".$data['sent']."<br />\n";
echo "Suljetut: ".$data['closed']."<br />\n";
echo "Poistetut: ".$data['removed']."<br />\n";

?>

<a href="#" onclick="change_view(1); return false;"><h2>Vastaanottamattomat</h2></a>
<div id="reports_flick_1">
<h3>L&auml;hetyksen mukaan</h3>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="150"><strong>Toimipaikka</strong></td>
        <td width="100" align="right"><strong>L&auml;hetyksi&auml;</strong></td>
        <td width="100" align="right"><strong>Summa</strong></td>
        <td width="100" align="right"><strong>L&auml;hetetty kpl</strong></td>
        <td width="130" align="right"><strong>Vastaanotettu kpl</strong></td>
    </tr>
<?

$tmp_orders_sum = 0;
$tmp_orders_quant_send = 0;
$tmp_orders_quant_received = 0;
$tmp_count = 0;
setlocale(LC_MONETARY, 'fi_FI.UTF');
foreach($data['by_sent_not_received'] as $sender => $events)
{
/*
echo "<pre>";
print_r($events);
echo "</pre>";
*/

        $person = mgd_get_person($sender);
    echo "\t<tr>\n";
        echo "\t\t<td><a href=\"sent_2/".$person->id.".html\">".$person->name."</a></td>\n";
    echo "\t\t<td>".count($events)."</td>\n";

    $tmp_count = $tmp_count + count($events);

    $orders_sum = 0;
    $orders_quant_send = 0;
    $orders_quant_received = 0;

    foreach($events as $counter => $event)
    {
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
        $orders_sum = $orders_sum + $sub_orders_sum;
        $orders_quant_send = $orders_quant_send + $sub_orders_quant_send;
        $orders_quant_received = $orders_quant_received + $sub_orders_quant_received;
    }
    echo "\t\t<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $orders_sum)))."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_send."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_received."</td>\n";
    $tmp_orders_sum = $tmp_orders_sum + $orders_sum;
    $tmp_orders_quant_send = $tmp_orders_quant_send + $orders_quant_send;
    $tmp_orders_quant_received = $tmp_orders_quant_received + $orders_quant_received;
    echo "\t</tr>\n";
}
    echo "\t<tr>\n";
        echo "\t\t<td><strong>Yhteens&auml;:</strong></td>\n";
    echo "\t\t<td><strong>".$tmp_count."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $tmp_orders_sum)))."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_send."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_received."</strong></td>\n";
    echo "\t</tr>\n";
?>
</table>
<h3>Vastaanoton mukaan</h3>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="150"><strong>Toimipaikka</strong></td>
        <td width="100" align="right"><strong>L&auml;hetyksi&auml;</strong></td>
        <td width="100" align="right"><strong>Summa</strong></td>
        <td width="100" align="right"><strong>L&auml;hetetty kpl</strong></td>
        <td width="130" align="right"><strong>Vastaanotettu kpl</strong></td>
    </tr>

<?
$tmp_orders_sum = 0;
$tmp_orders_quant_send = 0;
$tmp_orders_quant_received = 0;
$tmp_count = 0;
foreach($data['by_receive_not_received'] as $sender => $events)
{
        $person = mgd_get_person($sender);
    echo "\t<tr>\n";
        echo "\t\t<td><a href=\"receive_2/".$person->id.".html\">".$person->name."</a></td>\n";
    echo "\t\t<td>".count($events)."</td>\n";

    $tmp_count = $tmp_count + count($events);

    $orders_sum = 0;
    $orders_quant_send = 0;
    $orders_quant_received = 0;

    foreach($events as $counter => $event)
    {
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
        $orders_sum = $orders_sum + $sub_orders_sum;
        $orders_quant_send = $orders_quant_send + $sub_orders_quant_send;
        $orders_quant_received = $orders_quant_received + $sub_orders_quant_received;
    }
    echo "\t\t<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $orders_sum)))."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_send."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_received."</td>\n";
    $tmp_orders_sum = $tmp_orders_sum + $orders_sum;
    $tmp_orders_quant_send = $tmp_orders_quant_send + $orders_quant_send;
    $tmp_orders_quant_received = $tmp_orders_quant_received + $orders_quant_received;
    echo "\t</tr>\n";
}
    echo "\t<tr>\n";
        echo "\t\t<td><strong>Yhteens&auml;:</strong></td>\n";
    echo "\t\t<td><strong>".$tmp_count."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $tmp_orders_sum)))."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_send."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_received."</strong></td>\n";
    echo "\t</tr>\n";
?>
</table>
</div>

<a href="#" onclick="change_view(2); return false;"><h2>Vastaanotetut</h2></a>
<div id="reports_flick_2">
<h3>L&auml;hetyksen mukaan</h3>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="150"><strong>Toimipaikka</strong></td>
        <td width="100" align="right"><strong>L&auml;hetyksi&auml;</strong></td>
        <td width="100" align="right"><strong>Summa</strong></td>
        <td width="100" align="right"><strong>L&auml;hetetty kpl</strong></td>
        <td width="130" align="right"><strong>Vastaanotettu kpl</strong></td>
    </tr>
<?
$tmp_orders_sum = 0;
$tmp_orders_quant_send = 0;
$tmp_orders_quant_received = 0;
$tmp_count = 0;
foreach($data['by_sent'] as $sender => $events)
{
        $person = mgd_get_person($sender);
    echo "\t<tr>\n";
        echo "\t\t<td><a href=\"sent/".$person->id.".html\">".$person->name."</a></td>\n";
    echo "\t\t<td>".count($events)."</td>\n";

    $tmp_count = $tmp_count + count($events);

    $orders_sum = 0;
    $orders_quant_send = 0;
    $orders_quant_received = 0;

    foreach($events as $counter => $event)
    {
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
        $orders_sum = $orders_sum + $sub_orders_sum;
        $orders_quant_send = $orders_quant_send + $sub_orders_quant_send;
        $orders_quant_received = $orders_quant_received + $sub_orders_quant_received;
    }
    echo "\t\t<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $orders_sum)))."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_send."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_received."</td>\n";
    $tmp_orders_sum = $tmp_orders_sum + $orders_sum;
    $tmp_orders_quant_send = $tmp_orders_quant_send + $orders_quant_send;
    $tmp_orders_quant_received = $tmp_orders_quant_received + $orders_quant_received;
    echo "\t</tr>\n";
}
    echo "\t<tr>\n";
        echo "\t\t<td><strong>Yhteens&auml;:</strong></td>\n";
    echo "\t\t<td><strong>".$tmp_count."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $tmp_orders_sum)))."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_send."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_received."</strong></td>\n";
    echo "\t</tr>\n";
?>
</table>
<h3>Vastaanoton mukaan</h3>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="150"><strong>Toimipaikka</strong></td>
        <td width="100" align="right"><strong>L&auml;hetyksi&auml;</strong></td>
        <td width="100" align="right"><strong>Summa</strong></td>
        <td width="100" align="right"><strong>L&auml;hetetty kpl</strong></td>
        <td width="130" align="right"><strong>Vastaanotettu kpl</strong></td>
    </tr>
<?
$tmp_orders_sum = 0;
$tmp_orders_quant_send = 0;
$tmp_orders_quant_received = 0;
$tmp_count = 0;
foreach($data['by_receive'] as $sender => $events)
{
        $person = mgd_get_person($sender);
    echo "\t<tr>\n";
        echo "\t\t<td><a href=\"receive/".$person->id.".html\">".$person->name."</a></td>\n";
    echo "\t\t<td>".count($events)."</td>\n";

    $tmp_count = $tmp_count + count($events);

    $orders_sum = 0;
    $orders_quant_send = 0;
    $orders_quant_received = 0;

    foreach($events as $counter => $event)
    {
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
        $orders_sum = $orders_sum + $sub_orders_sum;
        $orders_quant_send = $orders_quant_send + $sub_orders_quant_send;
        $orders_quant_received = $orders_quant_received + $sub_orders_quant_received;
    }
    echo "\t\t<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $orders_sum)))."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_send."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_received."</td>\n";
    $tmp_orders_sum = $tmp_orders_sum + $orders_sum;
    $tmp_orders_quant_send = $tmp_orders_quant_send + $orders_quant_send;
    $tmp_orders_quant_received = $tmp_orders_quant_received + $orders_quant_received;
    echo "\t</tr>\n";
}
    echo "\t<tr>\n";
        echo "\t\t<td><strong>Yhteens&auml;:</strong></td>\n";
    echo "\t\t<td><strong>".$tmp_count."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $tmp_orders_sum)))."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_send."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_received."</strong></td>\n";
    echo "\t</tr>\n";
?>
</table>
</div>

<a href="#" onclick="change_view(3); return false;"><h2>Poistetut</h2></a>
<div id="reports_flick_3">
<h3>L&auml;hetyksen mukaan</h3>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="150"><strong>Toimipaikka</strong></td>
        <td width="100" align="right"><strong>L&auml;hetyksi&auml;</strong></td>
        <td width="100" align="right"><strong>Summa</strong></td>
        <td width="100" align="right"><strong>L&auml;hetetty kpl</strong></td>
        <td width="130" align="right"><strong>Vastaanotettu kpl</strong></td>
    </tr>
<?
$tmp_orders_sum = 0;
$tmp_orders_quant_send = 0;
$tmp_orders_quant_received = 0;
$tmp_count = 0;
foreach($data['by_sent_deleted'] as $sender => $events)
{
        $person = mgd_get_person($sender);
    echo "\t<tr>\n";
        echo "\t\t<td><a href=\"sent_3/".$person->id.".html\">".$person->name."</a></td>\n";
    echo "\t\t<td>".count($events)."</td>\n";

    $tmp_count = $tmp_count + count($events);

    $orders_sum = 0;
    $orders_quant_send = 0;
    $orders_quant_received = 0;

    foreach($events as $counter => $event)
    {
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
        $orders_sum = $orders_sum + $sub_orders_sum;
        $orders_quant_send = $orders_quant_send + $sub_orders_quant_send;
        $orders_quant_received = $orders_quant_received + $sub_orders_quant_received;
    }
    echo "\t\t<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $orders_sum)))."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_send."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_received."</td>\n";
    $tmp_orders_sum = $tmp_orders_sum + $orders_sum;
    $tmp_orders_quant_send = $tmp_orders_quant_send + $orders_quant_send;
    $tmp_orders_quant_received = $tmp_orders_quant_received + $orders_quant_received;
    echo "\t</tr>\n";
}
    echo "\t<tr>\n";
        echo "\t\t<td><strong>Yhteens&auml;:</strong></td>\n";
    echo "\t\t<td><strong>".$tmp_count."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $tmp_orders_sum)))."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_send."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_received."</strong></td>\n";
    echo "\t</tr>\n";
?>
</table>
<h3>Vastaanoton mukaan</h3>
<table cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td width="150"><strong>Toimipaikka</strong></td>
        <td width="100" align="right"><strong>L&auml;hetyksi&auml;</strong></td>
        <td width="100" align="right"><strong>Summa</strong></td>
        <td width="100" align="right"><strong>L&auml;hetetty kpl</strong></td>
        <td width="130" align="right"><strong>Vastaanotettu kpl</strong></td>
    </tr>
<?
$tmp_orders_sum = 0;
$tmp_orders_quant_send = 0;
$tmp_orders_quant_received = 0;
$tmp_count = 0;
foreach($data['by_receive_deleted'] as $sender => $events)
{
        $person = mgd_get_person($sender);
    echo "\t<tr>\n";
        echo "\t\t<td><a href=\"receive_3/".$person->id.".html\">".$person->name."</a></td>\n";
    echo "\t\t<td>".count($events)."</td>\n";

    $tmp_count = $tmp_count + count($events);

    $orders_sum = 0;
    $orders_quant_send = 0;
    $orders_quant_received = 0;

    foreach($events as $counter => $event)
    {
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
        $orders_sum = $orders_sum + $sub_orders_sum;
        $orders_quant_send = $orders_quant_send + $sub_orders_quant_send;
        $orders_quant_received = $orders_quant_received + $sub_orders_quant_received;
    }
    echo "\t\t<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $orders_sum)))."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_send."</td>\n";
    echo "\t\t<td align=\"right\">".$orders_quant_received."</td>\n";
    $tmp_orders_sum = $tmp_orders_sum + $orders_sum;
    $tmp_orders_quant_send = $tmp_orders_quant_send + $orders_quant_send;
    $tmp_orders_quant_received = $tmp_orders_quant_received + $orders_quant_received;
    echo "\t</tr>\n";
}
    echo "\t<tr>\n";
        echo "\t\t<td><strong>Yhteens&auml;:</strong></td>\n";
    echo "\t\t<td><strong>".$tmp_count."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $tmp_orders_sum)))."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_send."</strong></td>\n";
    echo "\t\t<td align=\"right\"><strong>".$tmp_orders_quant_received."</strong></td>\n";
    echo "\t</tr>\n";
?>
</table>
</div>

<script>
document.getElementById('reports_flick_1').style.display = 'none';
document.getElementById('reports_flick_2').style.display = 'none';
document.getElementById('reports_flick_3').style.display = 'none';

</script>

<br /><br />
Saatavilla olevat raportit
<br /><br />
<a href="../by_places/">Toimipaikoittain</a><br />
<a href="../by_products/">Tuotteittain</a><br />
<a href="../unclear/">Ep&auml;selv&auml;t</a><br />


<!-- / Show-report -->
