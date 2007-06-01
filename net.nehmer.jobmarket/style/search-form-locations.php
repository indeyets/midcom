<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
// Available request data: type_list, mode, result_url, search_data, type

// Goes over all types, shows checkboxed selectors for readable types,
// disabled ones for all others.
?>

<h3><?php $data['l10n']->show('locations'); ?>:</h3>
<table border="0" width="100%">
<tr>
    <td width="33%">
<?php
    if (count($data['search_data']['locations']) == 0)
    {
        $checked = "checked='checked'";
    }
    else
    {
        $checked = '';
    }
?>
        <input type='checkbox' name='locations_all' value='1' &(checked:h);/> <?php $data['l10n']->show('show all'); ?>
    </td>
<?php
$current_col = 2;

$max_col = 3;
foreach ($data['config']->get('location_list') as $key => $title)
{
    echo '<td width="33%">';
    if (in_array($key, $data['search_data']['locations']))
    {
        $checked = "checked='checked'";
    }
    else
    {
        $checked = '';
    }
    echo "<input type='checkbox' name='locations[]' value='{$key}' {$checked}/> {$title}\n";
    echo '</td>';

    $current_col++;
    if ($current_col > $max_col)
    {
        $current_col = 1;
        echo "\n</tr><tr>\n";
    }
}
if ($current_col > 1)
{
    while ($current_col <= $max_col)
    {
        echo "<td></td>";
        $current_col++;
    }
}
?>
</tr>
</table>