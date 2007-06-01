<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls
$view = $data['view_reservation'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $data['l10n_midcom']->get('delete'); ?>: &(view['title']);</h2>
<?php
if ($data['dependencies'])
{
    echo "<h3>". $data['l10n']->get('this reservation is a master event for repeated events') ."</h3\n";
    echo "<p>". $data['l10n']->get('you can remove the dependant events first') ."</p>\n";
    echo "<ul>\n";
    foreach ($data['dependant_events'] as $event)
    {
        echo "    <li><a href=\"{$prefix}reservation/{$event->guid}/\"></a>{$event->title}</li>\n";
    }
    echo "</ul>\n";
    echo "<p><a href=\"{$prefix}reservation/repeat/{$data['event']->guid}\">". $data['l10n']->get('or you can redefine the repeat rule') ."</p>\n";
}
else
{
?>

<form action="" method="post">
  <input type="submit" name="net_nemein_reservations_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="net_nemein_reservations_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>

<?php 
}
midcom_show_style('view-reservation'); 
?>
