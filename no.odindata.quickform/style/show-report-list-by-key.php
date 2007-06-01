<?php
// Bind the view data, remember the reference assignment:

//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<p>
<form method="post" action="../list_by_key/">
<h1><?php echo $data['l10n']->get('Reports'); ?></h1>
<p>
<?php echo $data['l10n']->get('Article count'); ?>: &(data['articles_count']);
<br /><br />
<a href="../list_all/"><?php echo $data['l10n']->get('List all'); ?></a>
</p>
<h2><?php echo $data['l10n']->get('Select column to sort with'); ?></h2>
<div class="no_odindata_quickform_reports_select_sort_keys">
<div class="no_odindata_quickform_reports_select_sort_key_1" style="float:left;">
<select name="no_odindata_quickform_reports_select_sort_key_1">
<?php 
foreach($data['fields_for_search'] as $field => $description_key)
{
    if($data['sort_key_1'] == $field)
    {
        echo "\t<option selected value=\"" . $field . "\">".$data['fields'][$description_key]."</option>\n";
    }
    else
    {
        echo "\t<option value=\"" . $field . "\">".$data['fields'][$description_key]."</option>\n";
    }
}
?>
</select>
</div>
<div class="no_odindata_quickform_reports_select_sort_key_2" style="float:left;">
<select name="no_odindata_quickform_reports_select_sort_key_2">
<?php 
foreach($data['fields_for_search'] as $field => $description_key)
{
    if($data['sort_key_2'] == $field)
    {
        echo "\t<option selected value=\"" . $field . "\">".$data['fields'][$description_key]."</option>\n";
    }
    else
    {
        echo "\t<option value=\"" . $field . "\">".$data['fields'][$description_key]."</option>\n";
    }
}
?>
</select>
</div>

</div>

<input type="submit" value="<?php echo $data['l10n']->get('Submit'); ?>" />
</form>
<?php echo $data['l10n']->get('List all'); ?>
<table cellpadding="2" cellspacing="1" border="1">
<?php
echo "\t<tr>\n";
echo "\t\t<td>".$data['fields'][$data['fields_for_search'][$data['sort_key_1']]]."</td>\n";
echo "\t\t<td>".$data['fields'][$data['fields_for_search'][$data['sort_key_2']]]."</td>\n";
echo "\t\t<td>".$data['l10n']->get('Count')."</td>\n";
echo "\t\t<td>".$data['l10n']->get('Show on screen')."</td>\n";
echo "\t\t<td>".$data['l10n']->get('Show in excel')."</td>\n";
/*foreach($data['fields'] as $field_key => $field_title)
{
     echo "\t\t<td>".$field_title."</td>\n";
}*/
echo "\t<tr>\n";

foreach($data['articles_by_key'] as $article_key => $article)
{
foreach($article as $article_key_2 => $article2)
{
    echo "<form method=\"POST\" action=\"../list_by_key_distinct/\">";
    echo "<input type=\"hidden\" name=\"no_odindata_quickform_reports_sort_key_1\" value=\"".$data['sort_key_1']."\" />";
    echo "<input type=\"hidden\" name=\"no_odindata_quickform_reports_sort_key_2\" value=\"".$data['sort_key_2']."\" />";
    echo "<input type=\"hidden\" name=\"no_odindata_quickform_reports_sort_key_1_value\" value=\"".$article_key."\" />";
    echo "<input type=\"hidden\" name=\"no_odindata_quickform_reports_sort_key_2_value\" value=\"".$article_key_2."\" />";
    echo "\t<tr>\n";
    echo "\t\t<td>".$article_key."</td>\n";
    echo "\t\t<td>".$article_key_2."</td>\n";
    echo "\t\t<td>".count($article2)."</td>\n";
    echo "\t\t<td><input type=\"submit\" name=\"no_odindata_quickform_reports_submit_screen\" value=\"".$data['l10n']->get('Show on screen')."\"</td>\n";
    echo "\t\t<td><input type=\"submit\" name=\"no_odindata_quickform_reports_submit_excel\" value=\"".$data['l10n']->get('Show in excel')."\"</td>\n";
    echo "\t</tr>\n";
    echo "\t</form>\n";
}
}
?>
</table>
</p>
