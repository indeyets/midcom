<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
// Available request data: type_list, mode, result_url, search_data, type

// Goes over all types, shows checkboxed selectors for readable types,
// disabled ones for all others.
?>

<h3><?php $data['l10n']->show('search for:'); ?></h3>
<table border="0" width="100%">
<tr>
    <td width="33%">
<?php
    if ($data['search_data']['search_all_types'])
    {
        $checked = "checked='checked'";
    }
    else
    {
        $checked = '';
    }
?>
        <input type='checkbox' name='types_all' value='1' &(checked:h);/> <?php $data['l10n']->show('show all'); ?>
    </td>
<?php
$current_col = 2;
$max_col = 3;
foreach ($data['type_list'] as $name => $config)
{
    if (! $config['show_in_search_all'])
    {
        continue;
    }
    echo '<td width="33%">';
    if ($config["{$data['mode']}_schema"])
    {
        if (   $_MIDCOM->auth->user === null
            && ! $config["{$data['mode']}_anonymous_read"])
        {
            echo "<img src='" . MIDCOM_STATIC_URL . "/stock-icons/16x16/cancel.png'/> {$config['title']}\n";
        }
        else
        {
            if (   ! $data['search_data']['search_all_types']
                && in_array($name, $data['search_data']['types']))
            {
                $checked = "checked='checked'";
            }
            else
            {
                $checked = '';
            }
            echo "<input type='checkbox' name='types[]' value='{$name}' {$checked}/> {$config['title']}\n";
        }
    }
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