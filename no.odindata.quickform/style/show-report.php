<?php
// Bind the view data, remember the reference assignment:

//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<p>
<form method="POST" action="list_by_key/">
<h1><?php echo $data['l10n']->get('Reports'); ?></h1>
<p>
<?php echo $data['l10n']->get('Article count'); ?>: &(data['articles_count']);
<br /><br />
<a href="list_all/"><?php echo $data['l10n']->get('List all'); ?></a>
</p>
<h2><?php echo $data['l10n']->get('Select columns to sort with'); ?></h2>
<div class="no_odindata_quickform_reports_select_sort_keys">
<div class="no_odindata_quickform_reports_select_sort_key_1" style="float:left;">
<select name="no_odindata_quickform_reports_select_sort_key_1">
<?php 
foreach($data['fields_for_search'] as $field => $description_key)
{
    echo "\t<option value=\"" . $field . "\">".$data['fields'][$description_key]."</option>\n";
}
?>
</select>
</div>
<div class="no_odindata_quickform_reports_select_sort_key_2" style="float:left;">
<select name="no_odindata_quickform_reports_select_sort_key_2">
<?php 
foreach($data['fields_for_search'] as $field => $description_key)
{
    echo "\t<option value=\"" . $field . "\">".$data['fields'][$description_key]."</option>\n";
}
?>
</select>
</div>

</div>
<input type="submit" value="<?php echo $data['l10n']->get('Submit'); ?>" />
</form>
</p>
