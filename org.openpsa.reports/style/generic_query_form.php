<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <?php  $data['datamanager']->display_form();  ?>
    <!-- To open the report in new window we need to set the target via JS -->
    <script type="text/javascript">
        document.<?php echo $data['datamanager']->form_prefix; ?>_form.target = '_BLANK';
    </script>
</div>