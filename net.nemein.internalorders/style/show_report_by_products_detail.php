<!-- Show-report -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
setlocale(LC_MONETARY, 'fi_FI.UTF');
?>
<style>
.note
{
    color:#DD0000;
    font-weight:bold;
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

<br /><br />

<a href="<?php echo $data['link']; ?>" target="_blank"><img src="/midcom-static/stock-icons/16x16/view.png" alt="excel" border="0" />&nbsp;<?php echo $data['l10n']->get('export to excel'); ?></a>

<br /><br />

<?
    $QB = org_openpsa_products_product_dba::new_query_builder();
    $QB->add_constraint('code', '=', $data['name']);
    $QB->add_order('code', 'ASC');
    $product = $QB->execute();
//    print_r($product);

//$article = mgd_get_article_by_name($data['products_topic']->id, $data['name']);
if($product)
{
    echo "<h2>".$product[0]->code.", ".$product[0]->title."</h2>";
?>

    
    <table cellpadding="2" cellspacing="0" border="0">
        <tr>
            <td>Numero</td>
            <td>L&auml;hett&auml;j&auml;</td>
            <td>Vastaanottaja</td>
            <td align="right">L&auml;hetysaika</td>
            <td align="right">L&auml;hetyksen kok. summa</td>
            <td align="right">Kokonaism&auml;&auml;r&auml;</td>
        </tr>
    <?php
    foreach($data['product'] as $sender => $products)
    {
    foreach($products as $product)
    {
    $event = mgd_get_event($product->up);
    
    $person_tmp = mgd_get_person($event->extra);
    $person_tmp2 = mgd_get_person($event->creator);
    
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
        echo "        <td><a href=\"../../../view/".$event->guid().".html\">".$event->title."</a></td>";
        echo "        <td>".$person_tmp2->name."</td>\n";
        echo "        <td>".$person_tmp->name."</td>\n";
        echo "        <td align=\"right\">&nbsp;".date("d.m.Y G:i", $event->start)."</td>\n";
        echo "        <td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $sub_orders_sum)))."&nbsp;&euro;</td>\n";
        if ($sub_orders_quant_received == $sub_orders_quant_send)
        {
        echo "        <td align=\"right\">".$sub_orders_quant_send."</td>\n";
        }
        else
        {
        echo "        <td class=\"note\" align=\"right\">".$sub_orders_quant_send."</td>\n";
        }
        echo "    </tr>\n";
        
    }
    }
    echo "</table>";


}
else
{
        
    foreach($data['product'][$data['name']] as $name => $products)
    {
        echo "<h2>".$data['name'].", ".$name."</h2>";
?>
    
        
        <table cellpadding="0" cellspacing="0" border="0">
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
            echo "        <td><a href=\"../../../view/".$event->guid().".html\">".$event->title."</a></td>";
            echo "        <td>".$person_tmp2->name."</td>\n";
            echo "        <td>".$person_tmp->name."</td>\n";
            echo "        <td>&nbsp;".date("d.m.Y G:i", $event->start)."</td>\n";
            echo "        <td align=\"right\">".str_replace('.', ',', str_replace('EUR', '', money_format('%i', $sub_orders_sum)))."&nbsp;&euro;</td>\n";
            if ($sub_orders_quant_received == $sub_orders_quant_send)
            {
            echo "        <td align=\"right\">".$sub_orders_quant_send."</td>\n";
            }
            else
            {
            echo "        <td class=\"note\" align=\"right\">".$sub_orders_quant_send."</td>\n";
            }
            echo "    </tr>\n";
            
        }
        echo "</table>";
    }
}
?>

<br /><br />
Saatavilla olevat raportit
<br /><br />
<a href="../../by_places/">Toimipaikoittain</a><br />
<a href="../../by_products/">Tuotteittain</a><br />
<a href="../../unclear/">Ep&auml;selv&auml;t</a><br />

<!-- / Show-report -->
