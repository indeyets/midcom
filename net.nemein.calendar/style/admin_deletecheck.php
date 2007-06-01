<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dn_data= $data['datamanager']->get_content_html();
?>

<h2><?php echo $data['l10n_midcom']->get('delete'); ?> &(view.title);</h2>

<?php $data['datamanager']->display_view (); ?>

<p>
<form action="" method="POST">
    <input type="submit" name="net_nemein_calendar_deleteok" value="<?php echo $data['l10n_midcom']->get("delete"); ?>" />
    <input type="submit" name="net_nemein_calendar_deletecancel" value="<?php echo $data['l10n_midcom']->get("cancel"); ?>" />
</form>
</p>