<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view =& $data['datamanager']->get_content_html();
?>
<h1>&(data['page_title']:h);</h1>
<p>
    <?php echo $data['l10n']->get('are you sure you want to remove the object described below'); ?>
</p>
<?php
$data['datamanager']->display_view();
?>
<br />
<form method="get">
    <input type="submit" name="f_confirm" value="<?php echo $data['l10n']->get('confirm'); ?>" />
    <input type="submit" name="f_cancel" value="<?php echo $data['l10n']->get('cancel'); ?>" />
</form>
