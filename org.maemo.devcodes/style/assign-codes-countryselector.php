<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h2><?php echo $data['l10n']->get('select area'); ?></h2>
<ul>
<?php
foreach ($data['areas'] as $area => $title)
{
    $codes = (int)$data['codes_available'][$area];
    echo "    <li>";
    if ($codes > 0)
    {
        echo "<a href='{$prefix}code/assign/{$data['device']->guid}/{$area}.html'>";
    }
    echo sprintf($data['l10n']->get('%s (%d codes available)'), $title, $codes);
    if ($codes > 0)
    {
        echo '</a>';
    }
    echo "</li>\n";
}
?>
</ul>
