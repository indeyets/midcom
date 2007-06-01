<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dog =& $data['dog'];
$dog_view =& $data['view_dog'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$sex_symbol = net_fernmark_pedigree_dog_sex_symbol($dog);
?>

<h2>&(sex_symbol);&(dog.name_with_kennel);</h2>
<?php
$sire_withlink = $dog_view['sire'];
if ($dog->sire)
{
    $sire_withlink = "<a href='{$prefix}dog/{$dog->sire}.html' target='_BLANK'>{$dog_view['sire']}</a>";
}
$dam_withlink = $dog_view['dam'];
if ($dog->dam)
{
    $dam_withlink = "<a href='{$prefix}dog/{$dog->dam}.html' target='_BLANK'>{$dog_view['dam']}</a>";
}
?>
<p class="parents">♂ &(sire_withlink:h); ♀ &(dam_withlink:h);</p>

<?php
if ($dog->has_offspring())
{
    midcom_show_style('view-dog-offspring');
}
if ($dog->has_results())
{
    midcom_show_style('view-dog-results');
}
?>