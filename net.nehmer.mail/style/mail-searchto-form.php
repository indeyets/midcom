<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2><?php $data['l10n']->show('lookup username'); ?></h2>

<form action='' method='post' enctype='multipart/form-data'>
<p>
    <?php $data['l10n_midcom']->show('username'); ?>:
    <input type="text" name="search_string" value="&(data['search_string']);" />
    <input type="submit" name="net_nehmer_mail_searchto_submit" />
</p>
</form>

<?php if ($data['processing_msg']) { ?>
<p style="color: red; font-weight: bold;">&(data['processing_msg']:h);</p>
<?php } ?>