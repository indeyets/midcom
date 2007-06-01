<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['view_downloadpage'];
?>
<li><a href="&(prefix);&(view['name']);.html">&(view['release']:h);</a></li>