<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <?php $data['controller']->display_form(); ?>
</div>
<div class="sidebar">
    <?php midcom_show_style("show-search-form-simple"); ?>
    <?php midcom_show_style("show-directory-navigation"); ?>
</div>