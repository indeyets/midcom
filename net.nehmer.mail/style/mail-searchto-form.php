<?php
$view =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2><?php $view['l10n']->show('lookup username'); ?></h2>

<form action='' method='post' enctype='multipart/form-data'>
<p>
    <?php $view['l10n_midcom']->show('username'); ?>:
    <input type="text" name="search_string" value="&(view['search_string']);" />
    <input type="submit" name="net_nehmer_mail_searchto_submit" />
</p>
</form>

<?php if ($view['processing_msg']) { ?>
<p style="color: red; font-weight: bold;">&(view['processing_msg']:h);</p>
<?php } ?>