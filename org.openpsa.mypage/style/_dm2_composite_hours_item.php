<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_data =& $_MIDCOM->get_custom_context_data('midcom_helper_datamanager2_widget_composite');
$view = $view_data['item_html'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
            <td>&(view['task']:h);</td>
            <td class="hours">&(view['hours']:h);</td>
            <td>&(view['invoiceable']:h);</td>
            <td class="description">&(view['description']:h);</td>