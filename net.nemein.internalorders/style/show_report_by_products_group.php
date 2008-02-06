<!-- Show-report -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<div style="float:right; margin:20px;"><a href="../">Omat</a></div>
<h1><?php echo $data['l10n']->get('Show-internalorders'); ?></h1>

<?php
echo "Muokattavissa: ".$data['open']."<br />\n";
echo "Avoimet: ".$data['sent']."<br />\n";
echo "Suljetut: ".$data['closed']."<br />\n";
echo "Poistetut: ".$data['removed']."<br />\n";

?><br />
<h3>Tuotenumero tiedossa:</h3>
<table cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td>ID</td>
        <td>Nimi</td>
        <td align="right">Yhteens&auml;</td>
        <td align="right">L&auml;hetyksi&auml;</td>
    </tr>
<?
foreach($data['product_known'] as $guid => $events)
{
    $sent_count = 0;
    foreach ($events as $event)
    {
        $sent_count = $sent_count + $event->parameter('net.nemein.internalorders', 'quantity');
    }
    $QB = org_openpsa_products_product_dba::new_query_builder();
    $QB->add_constraint('code', '=', $guid);
    $QB->add_order('code', 'ASC');
    $product = $QB->execute();
    
    if ($product)
    {
        echo "<tr>\n";
        echo "<td>".$guid."</td>\n";
        echo "<td><a href=\"../by_products/detail/".$product[0]->code.".html\">".$product[0]->title."</a></td>\n";
        echo "<td align=\"right\">".$sent_count."</td>\n";
        echo "<td align=\"right\">".count($events)."</td>\n";
        echo "</tr>\n";
//        echo "<h3><a href=\"sent/".$guid.".html\">".$article->title."</a>, ".$sent_count." kpl, ".count($events)." l&auml;hetyst&auml;</h3>";
    }

}
?>
</table>


<h3>Ei tuotenumeroa:</h3>
<table cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td>ID</td>
        <td>Nimi</td>
        <td align="right">Yhteens&auml;</td>
        <td align="right">L&auml;hetyksi&auml;</td>
    </tr>
<?
foreach($data['product_unknown'] as $guid => $events)
{
    $sent_count = 0;
    foreach ($events as $event)
    {
        $sent_count = $sent_count + $event->parameter('net.nemein.internalorders', 'quantity');
    }
        echo "<tr>\n";
        echo "<td>".$guid."</td>\n";
        echo "<td><a href=\"detail/".$event->extra.".html\">".$event->title."</a></td>\n";
        echo "<td align=\"right\">".$sent_count."</td>\n";
        echo "<td align=\"right\">".count($events)."</td>\n";
        echo "</tr>\n";

}
?>
</table>



<br /><br />
Saatavilla olevat raportit
<br /><br />
<a href="../by_places/">Toimipaikoittain</a><br />
<a href="../by_products/">Tuotteittain</a><br />
<a href="../unclear/">Ep&auml;selv&auml;t</a><br />


<!-- / Show-report -->
