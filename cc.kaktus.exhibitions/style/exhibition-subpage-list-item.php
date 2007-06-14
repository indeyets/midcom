<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['datamanager']->get_content_html();
?>
    <li><a href="<?php echo "{$prefix}{$data['event_url']}{$view['extra']}/"; ?>">&(view['title']:h);</a></li>