<!-- Show-report -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<script>
function change_view(trToChange)
{

    elementToChange = document.getElementById(trToChange);
    
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
<?php
foreach($data['root_groups'] as $code => $root_group)
{
    echo "#sub_group_".$root_group->code.", ";
}
?>#foobar
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

?><br />
<table cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td>TR</td>
        <td>Nimi</td>
    </tr>
<?
foreach($data['root_groups'] as $code => $root_group)
{
    echo "\t<tr>\n";
    echo "\t\t<td>".$root_group->code."</td>\n";
    echo "\t\t<td><a href=\"#\" onclick=\"change_view('sub_group_".$root_group->code."'); return false;\">".$root_group->title."</a></td>\n";
    echo "\t</tr>\n";
    if(count($data['sub_groups'][$root_group->code])>0)
    {
        echo "\t<tr>\n";
        echo "\t\t<td colspan=\"2\">\n";
        echo "\t<div id=\"sub_group_".$root_group->code."\">";
        echo "\t\t\t<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
        foreach($data['sub_groups'][$root_group->code] as $code_sub => $sub_group)
        {
            echo "\t\t\t\t<tr>\n";
            echo "\t\t\t\t\t<td>&nbsp;&nbsp;</td>\n";
            echo "\t\t\t\t\t<td>".$sub_group->code."</td>\n";
            echo "\t\t\t\t\t<td><a href=\"../by_products_group/".$root_group->code.$sub_group->code."\">".$sub_group->title."</a></td>\n";
            echo "\t\t\t\t</tr>\n";
        }
            echo "\t\t</table>\n";
            echo "\t</div>\n";
            echo "\t\t</td>\n";
            echo "\t</tr>\n";
    }

}
?>
</table>

<script>
<?php
foreach($data['root_groups'] as $code => $root_group)
{
    echo "\tchange_view('sub_group_".$root_group->code."');\n";
}
?>
</script>


<br /><br />
Saatavilla olevat raportit
<br /><br />
<a href="../by_places/">Toimipaikoittain</a><br />
<a href="../by_products/">Tuotteittain</a><br />
<a href="../unclear/">Ep&auml;selv&auml;t</a><br />


<!-- / Show-report -->
