<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $view_data['invoice_dm'];
?>
<div class="main">
    <?php $view->display_view(); ?>
</div>
