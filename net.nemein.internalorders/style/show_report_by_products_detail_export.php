<!-- Show-report -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
setlocale(LC_MONETARY, 'fi_FI.UTF');
?>
	<table cellpadding="2" cellspacing="0" border="0">
		<tr>
			<td colspan="5">
				<h1><?php echo $data['l10n']->get('Show-internalorders'); ?></h1>
			</td>
		</tr>


<?
	$QB = org_openpsa_products_product_dba::new_query_builder();
	$QB->add_constraint('code', '=', $data['name']);
	$QB->add_order('code', 'ASC');
	$product = $QB->execute();
//	print_r($product);

//$article = mgd_get_article_by_name($data['products_topic']->id, $data['name']);
if($product)
{
?>
		<tr>
			<td colspan="5">
<?php
	echo "<h2>".$product[0]->code.", ".$product[0]->title."</h2>";
?>
			</td>
		</tr>
		<tr>
			<td>Numero</td>
			<td>L&auml;hett&auml;j&auml;</td>
			<td>Vastaanottaja</td>
			<td>L&auml;hetysaika</td>
			<td>L&auml;hetyksen kok. summa</td>
			<td>Kokonaism&auml;&auml;r&auml;</td>
		</tr>
	<?php
	foreach($data['product'] as $sender => $products)
	{
	foreach($products as $product)
	{
	$event = mgd_get_event($product->up);
	
	$person_tmp = mgd_get_person($event->extra);
	$person_tmp2 = mgd_get_person($event->creator);
	
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
		echo "		<td><a href=\"../../../view/".$event->guid().".html\">".$event->title."</a></td>";
		echo "		<td>".$person_tmp2->name."</td>\n";
		echo "		<td>".$person_tmp->name."</td>\n";
		echo "		<td>&nbsp;".date("d.m.Y G:i", $event->start)."</td>\n";
		echo "		<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $sub_orders_sum)))."&nbsp;&euro;</td>\n";
		if ($sub_orders_quant_received == $sub_orders_quant_send)
		{
		echo "		<td align=\"right\">".$sub_orders_quant_send."</td>\n";
		}
		else
		{
		echo "		<td class=\"note\" align=\"right\">".$sub_orders_quant_send."</td>\n";
		}
		echo "	</tr>\n";
		
	}
	}
	echo "</table>";


}
else
{
		
	foreach($data['product'][$data['name']] as $name => $products)
	{
	?>
			<tr>
			<td colspan="5">
	<?
		echo "<h2>".$data['name'].", ".$name."</h2>";
?>
	
			</td>
		</tr>
		<tr>
				<td>Nimi</td>
				<td>L&auml;hett&auml;j&auml;</td>
				<td>Vastaanottaja</td>
				<td>L&auml;hetysaika</td>
				<td>L&auml;hetyksen kokonaissumma</td>
				<td>Kokonaism&auml;&auml;r&auml;</td>
			</tr>
		<?php
		foreach($products as $product)
		{
		$event = mgd_get_event($product->up);
		
		$person_tmp = mgd_get_person($event->extra);
		$person_tmp2 = mgd_get_person($event->creator);
		
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
			echo "		<td><a href=\"../../../view/".$event->guid().".html\">".$event->title."</a></td>";
			echo "		<td>".$person_tmp2->name."</td>\n";
			echo "		<td>".$person_tmp->name."</td>\n";
			echo "		<td>&nbsp;".date("d.m.Y G:i", $event->start)."</td>\n";
			echo "		<td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $sub_orders_sum)))."&nbsp;&euro;</td>\n";
			if ($sub_orders_quant_received == $sub_orders_quant_send)
			{
			echo "		<td align=\"right\">".$sub_orders_quant_send."</td>\n";
			}
			else
			{
			echo "		<td class=\"note\" align=\"right\">".$sub_orders_quant_send."</td>\n";
			}
			echo "	</tr>\n";
			
		}
		echo "</table>";
	}
}
?>



<!-- / Show-report -->
