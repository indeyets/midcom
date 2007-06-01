<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$businessarea = $data['businessarea'];
$view_businessarea = $data['view_businessarea'];
?>
<li><a href="<?php echo $data['view_businessarea_url']; ?>">&(view_businessarea['code']:h);: &(view_businessarea['title']:h);</a></li>