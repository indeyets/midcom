<!-- Show-report -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>
<style>
.note
{
	color:#DD0000;
	font-weight:bold;
}
table.internalorders td
{
	padding-left:3px;
	padding-right:3px;
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

<?

$person = mgd_get_person($data['person']);

echo "<h2>".$person->name."</h2>";
?>

<br /><br />

<a href="<?php echo $data['link']; ?>" target="_blank"><img src="/midcom-static/stock-icons/16x16/view.png" alt="excel" border="0" />&nbsp;<?php echo $data['l10n']->get('export to excel'); ?></a>

<br /><br />

<table cellpadding="0" cellspacing="0" border="0" class="internalorders">
	<tr>
		<td><strong>Nro.</strong></td>
		<td><strong>L&auml;hett&auml;j&auml;</strong></td>
		<td><strong>Vastaanottaja</strong></td>
		<td align="right"><strong>L&auml;hetysaika</strong></td>
		<td align="right"><strong>L&auml;hetyksen kokonaissumma</strong></td>
		<td align="right"><strong>L&auml;hetetty</strong></td>
		<td align="right"><strong>Vastaanotettu</strong></td>
	</tr>
<?php

$tmp_sum = 0;
$tmp_quant = 0;
$tmp_quant2 = 0;
$tmp_orders_count = 0;
setlocale(LC_MONETARY, 'fi_FI.UTF');
foreach($data['unclear'] as $sender => $event)
{
$person_tmp_receiver = mgd_get_person($event->extra);
$person_tmp_sender = mgd_get_person($event->creator);

/*	echo "<pre>";
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

	echo "	<tr>\n";
	echo "		<td><a href=\"".$_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)."/view/".$event->guid().".html\">".$event->title."</a></td>";
	echo "		<td>".$person_tmp_sender->name."</td>\n";
	echo "		<td>".$person_tmp_receiver->name."</td>\n";
	echo "		<td align=\"right\">&nbsp;".date("d.m.Y G:i", $event->start)."</td>\n";
	echo "		<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $sub_orders_sum)))."</td>\n";
	echo "		<td align=\"right\">".$sub_orders_quant_send."</td>\n";
	echo "		<td align=\"right\">".$sub_orders_quant_received."</td>\n";
	echo "	</tr>\n";
	$tmp_sum = $tmp_sum + $sub_orders_sum;
	$tmp_quant = $tmp_quant + $sub_orders_quant_send;
	$tmp_quant2 = $tmp_quant2 + $sub_orders_quant_received;
	$tmp_orders_count++;
	
}
echo "\t<tr>\n";
echo "\t\t<td>&nbsp;</td>\n";
echo "\t</tr>\n";
echo "\t<tr>\n";
echo "\t\t<td align=\"right\" colspan=\"3\"><strong>Yhteens&auml;</strong></td>\n";
echo "\t\t<td align=\"right\">".$tmp_orders_count."&nbsp;kpl</td>\n";
echo "\t\t<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $tmp_sum)))."&nbsp;&euro;</td>\n";
echo "\t\t<td align=\"right\">".$tmp_quant."</td>\n";
echo "\t\t<td align=\"right\">".$tmp_quant2."</td>\n";
echo "\t</tr>\n";
echo "</table>";
?>

<br /><br />
Saatavilla olevat raportit
<br /><br />
<a href="../../by_places/">Toimipaikoittain</a><br />
<a href="../../by_products/">Tuotteittain</a><br />
<a href="../../unclear/">Ep&auml;selv&auml;t</a><br />

<!-- / Show-report -->
