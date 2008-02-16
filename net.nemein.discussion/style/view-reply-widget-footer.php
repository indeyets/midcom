<?php
// Available request keys: controller, schema, schemadb
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2><?php echo $data['l10n']->get('reply to'); ?>: <?php echo $data['view_parent_post']['subject']; ?></h2>
<?php 
$data['controller']->display_form(); 
?>