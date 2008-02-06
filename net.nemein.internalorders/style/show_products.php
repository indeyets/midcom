<!-- Show-own -->
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
    else
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
#reports_flick_1, #reports_flick_2
{
    display: none;
}

</style>

<h1><?php echo $data['l10n']->get('Show-products'); ?></h1>
<a href="#" onclick="change_view(1); return false;">Tuotteet</a>
<div id="reports_flick_1">
<table cellpadding="0" cellspacing="2" border="0">
<tr>
    <td width="100"><strong>Koodi</strong></td>
        <td width="200"><strong>Tuote</strong></td>
        <td width="70"><strong>TR<strong></td>
    <td width="70"><strong>AR</strong></td>
    <td><strong>Ostohinta</strong></td>
</tr>
<?php
$i = 0;
foreach($data['products'] as $product)
{
$i++;
//    echo "<pre>";
//    print_r($product);
//    echo "</pre>";
    echo "<tr>\n";
    echo "<td><a href=\"../productsedit/".$product->id.".html\">".$product->name."</td>\n";
    echo "<td>".$product->title."</td>\n";
        echo "<td>".$product->extra2."</td>";
        echo "<td>".$product->extra3."</td>";
    echo "<td>".$product->extra1."</td>\n";
    echo "</tr>\n";
}
?>
</table>
<br />Yhteens&auml;: <?php echo $i;?><br /><br />
</div>
<a href="#" onclick="change_view(1); return false;">Tuoteryhm&auml;t</a>
<div id="reports_flick_2">
<table cellpadding="0" cellspacing="2" border="0">
<tr>
    <td width="100"><strong>Koodi</strong></td>
        <td width="200"><strong>Tuote</strong></td>
        <td width="70"><strong>TR<strong></td>
    <td width="70"><strong>AR</strong></td>
    <td><strong>Ostohinta</strong></td>
</tr>
<?php
$i = 0;
foreach($data['tr'] as $product)
{
$i++;
//    echo "<pre>";
//    print_r($product);
//    echo "</pre>";
    echo "<tr>\n";
    echo "<td><a href=\"../productsedit/".$product->id.".html\">".$product->name."</td>\n";
    echo "<td>".$product->title."</td>\n";
        echo "<td>".$product->extra2."</td>";
        echo "<td>".$product->extra3."</td>";
    echo "<td>".$product->extra1."</td>\n";
    echo "</tr>\n";
}
?>
<tr><td><br /><br /></td></tr>
</table>
<br />Yhteens&auml;: <?php echo $i;?><br /><br />
</div>
<a href="#" onclick="change_view(3); return false;">Alaryhm&auml;t</a>
<div id="reports_flick_3">
<table cellpadding="0" cellspacing="2" border="0">
<tr>
    <td width="100"><strong>Koodi</strong></td>
        <td width="200"><strong>Tuote</strong></td>
        <td width="70"><strong>TR<strong></td>
    <td width="70"><strong>AR</strong></td>
    <td><strong>Ostohinta</strong></td>
</tr>
<?php
$i = 0;
foreach($data['ar'] as $product)
{
$i++;
//    echo "<pre>";
//    print_r($product);
//    echo "</pre>";
    echo "<tr>\n";
    echo "<td><a href=\"../productsedit/".$product->id.".html\">".$product->name."</td>\n";
    echo "<td>".$product->title."</td>\n";
        echo "<td>".$product->extra2."</td>";
        echo "<td>".$product->extra3."</td>";
    echo "<td>".$product->extra1."</td>\n";
    echo "</tr>\n";
}
?>
</table>
<br />Yhteens&auml;: <?php echo $i;?><br /><br />
</div>

<script>
document.getElementById('reports_flick_1').style.display = 'none';
document.getElementById('reports_flick_2').style.display = 'none';
document.getElementById('reports_flick_3').style.display = 'none';

</script>

<br /><br />
<a href="../productsnew/">Lis&auml;&auml; tuote</a>
<br /><br />

<!-- / Show-own -->
