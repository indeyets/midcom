<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['datamanager']->get_content_html();
?>
    <li><a href="&(prefix);route/<?php echo $data['route']->guid ?>/">&(view['name']:h);</a></li>
