<?php
$view_data = $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <?php  $view_data['datamanager']->display_form();  ?>
    <!-- To open the report in new window we need to set the target via JS -->
    <script language="javascript">
        document.<?php echo $view_data['datamanager']->form_prefix; ?>_form.target = '_BLANK';
    </script>
</div>
