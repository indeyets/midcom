<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_downloadpage'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1>&(view['release']:h);</h1>

<?php $data['controller']->display_form (); ?>