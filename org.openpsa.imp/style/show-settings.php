<?php
$view_data = $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <?php $view_data['datamanager']->display_form(); ?>
</div>