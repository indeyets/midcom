<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
$data = $view['datamanager']->get_array();
?>

<h2><?php echo $GLOBALS["view_l10n_midcom"]->get("delete"); ?> &(view.title);</h2>

<?php $view['datamanager']->display_view (); ?>

<p>
<form action="" method="POST">
    <input type="submit" name="net_nemein_calendar_deleteok" value="<?php echo $view['l10n_midcom']->get("delete"); ?>" />
    <input type="submit" name="net_nemein_calendar_deletecancel" value="<?php echo $view['l10n_midcom']->get("cancel"); ?>" />
</form>
</p>