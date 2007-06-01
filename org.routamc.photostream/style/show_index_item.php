<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$stream = $data['photostream'];
?>
<li><a href="&(prefix);&(stream['url']);">&(stream['title']);</a></li>