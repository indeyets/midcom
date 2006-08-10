<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_data =& $_MIDCOM->get_custom_context_data('midcom_helper_datamanager2_widget_composite');
$view = $view_data['item_html'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
        <tr>
            <td>&(view['title']:h);</td>
            <td>&(view['pricePerUnit']:h); / &(view['unit']:h);</td>
            <td>&(view['units']:h);</td>
            <td>&(view['price']:h);</td>
            <!-- TODO: Show supplier, etc -->
        </tr>