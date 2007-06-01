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
<table cellpadding="2" cellspacing="1" border="1">
<?php
echo '<h2>'.$data['fields'][$data['fields_for_search'][$data['sort_key_1']]].', '.$data['fields'][$data['fields_for_search'][$data['sort_key_2']]]."</h2>\n";
?>
<?php
    echo "\t<tr>\n";
    echo "\t\t<td>".$data['l10n']->get('Time submitted')."</td>\n";
    echo "\t\t<td>".$data['fields'][$data['fields_for_search'][$data['sort_key_1']]]."</td>\n";
    echo "\t\t<td>".$data['fields'][$data['fields_for_search'][$data['sort_key_2']]]."</td>\n";
    foreach($data['fields'] as $field_key => $field_title)
    {
        if(!($data['fields_for_search'][$data['sort_key_1']] == $field_key ||  $data['fields_for_search'][$data['sort_key_2']] == $field_key))
        {
            echo "\t\t<td>".$field_title."</td>\n";
        }
    }
    echo "\t<tr>\n";
    foreach($data['articles'] as $article_key => $article)
    {
        echo "\t<tr>\n";
        echo "\t\t<td>".strftime('%x %X',$article->name)."</td>\n";
        echo "\t\t<td>".$article->$data['sort_key_1']."</td>\n";
        echo "\t\t<td>".$article->$data['sort_key_2']."</td>\n";
        foreach($data['fields'] as $field_key => $field_title)
        {
            if(!($data['fields_for_search'][$data['sort_key_1']] == $field_key ||  $data['fields_for_search'][$data['sort_key_2']] == $field_key))
            {
                if($data['schema_content']['fields'][$field_key]['location'] == 'parameter')
                {
                    echo "\t\t<td>".$article->parameter('midcom.helper.datamanager2', $field_key)."</td>\n";
                }
                else
                {
                    echo "\t\t<td>".$article->$data['schema_content']['fields'][$field_key]['location']."</td>\n";
                }
            }
        }
    echo "\t</tr>\n";
    }
?>
</table>
</p>
