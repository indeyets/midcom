<?php
$component =& $data['component_data'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><img src="<?php echo MIDCOM_STATIC_URL; ?>/&(component['icon']);" alt="" /> &(component['name']);</h2>

<p class="version">&(component['version']);</p>

<p class="description">&(component['title']);</p>

<?php
$help = new midcom_admin_help_help();
$files = $help->list_files($data['component']);
if (count($files) > 0)
{
    echo "<h3>" . $_MIDCOM->i18n->get_string('component help', 'midcom.admin.help') . "</h3>\n";
    echo "<ul>\n";
    foreach ($files as $identifier => $filedata)
    {
        echo "<li><a href=\"{$prefix}__ais/help/{$data['component']}/{$identifier}/\" target=\"_blank\">{$filedata['subject']}</a></li>\n";
    }
    echo "</ul>\n";
}
?>

