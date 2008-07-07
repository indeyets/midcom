<?php
$log_max_lines = 100;
if ($data['object']->metadata->imported == 0)
{
    $imported = $_MIDCOM->i18n->get_string('never', 'midcom.helper.replicator');
}
else
{
    $imported = strftime('%x %X', $data['object']->metadata->imported);
}

if ($data['object']->metadata->exported == 0)
{
    $exported = $_MIDCOM->i18n->get_string('never', 'midcom.helper.replicator');
}
else
{
    $exported = strftime('%x %X', $data['object']->metadata->exported);
}
?>
<h1><?php echo $data['view_title']; ?></h1>

<table>
    <tr>
        <th><?php echo $_MIDCOM->i18n->get_string('imported', 'midcom.helper.replicator'); ?></th>
        <td>&(imported);</td>
    </tr>
    <tr>
        <th><?php echo $_MIDCOM->i18n->get_string('exported', 'midcom.helper.replicator'); ?></th>
        <td>&(exported);</td>
    </tr>
    <tr>
        <td colspan=2>
            <form method="post" class="midcom_helper_replicator_requeue_form">
                <input type="submit" class="button" name="midcom_helper_replicator_requeue" value="<?php echo $_MIDCOM->i18n->get_string('re-queue object', 'midcom.helper.replicator'); ?>" />
            </form>
        </td>
    </tr>
</table>

<?php
echo "<h2>" . $_MIDCOM->i18n->get_string('from log file', 'midcom.helper.replicator') . "</h2>\n";

$output = array();
exec("grep '{$data['object']->guid}' '{$GLOBALS['midcom_helper_replicator_logger']->_filename}'", $output);

$path_regex = "%{$data['queue_root_dir']}.*?/[0-9a-f]{32,80}(-quarantine)?/[0-9]+/%";
echo "<ul class=\"midcom_helper_replicator_object\">\n";
$output = array_slice(array_reverse($output), 0, $log_max_lines);
foreach ($output as $line)
{
    $line_items = array();
    if (!preg_match('/^(.*?\s[0-9]{2}\s[0-9]{4}\s[0-9]{2}:[0-9]{2}:[0-9]{2})\s(\s*\(.*?\):\s*)?\[(.*?)\]\s(.*?):(.*?)$/', $line, $line_items))
    {
        // Could not parse line, output as is
        echo "<li class=\"raw\"><pre>{$line}</pre></li>\n";
        continue;
    }
    $item_time = $line_items[1];
    // Possible value in key 2 is detailed time statistics
    $item_class = $line_items[3];
    $item_component = $line_items[4];
    $item_content = $line_items[5];

    $component = str_replace('midcom_helper_replicator_importer', 'importer', str_replace('Queue Manager', 'queuemanager', $item_component));
    $message = str_replace($data['object']->guid, "<abbr title=\"{$data['object']->guid}\">&lt;GUID&gt;</abbr>", $item_content);
    $message = preg_replace($path_regex, '<abbr title="\\0">&lt;PATH&gt;</abbr>/', $message);
    
    echo "<li class=\"{$item_class} {$component}\">";
    echo "<span class=\"date\">" . strftime('%x %X', strtotime($item_time)) . "</span>";
    echo ": {$message}";
    echo "</li>\n";
}
echo "</ul>\n";
?>