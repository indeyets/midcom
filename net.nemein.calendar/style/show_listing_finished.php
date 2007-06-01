<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

if ($data['archive_mode']) 
{
    $url = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'archive.html';
?>
<p><a href="&(url);"><?php $data['l10n']->show('back to archive.'); ?></a></p>
<?php } ?>