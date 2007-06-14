<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['datamanager']->get_content_html();
?>
        <tr>
            <td class="title"><a href="&(prefix);<?php echo date('Y', $data['event']->start) . '/' . $data['event']->extra; ?>/">&(view['title']:h);</td>
            <td class="exhibition_name">&(view['exhibition']:h);</td>
            <td class="date"><?php echo strftime('%x', $data['event']->start) . ' - ' . strftime('%x', $data['event']->end); ?></td>
        </tr>
