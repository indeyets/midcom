<?php
if ($data['archive_mode']) 
{
    $url = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'archive/';
    ?>
    <p><a href="&(url);"><?php $data['l10n']->show('back to archive.'); ?></a></p>
    <?php 
} 
?>