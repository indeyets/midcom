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

?>

<br /><br />
Saatavilla olevat raportit
<br /><br />
<a href="by_places/">Toimipaikoittain</a><br />
<a href="by_products/">Tuotteittain</a><br />
<a href="unclear/">Ep&auml;selv&auml;t</a><br />


<!-- / Show-report -->