<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
        <tr>
            <th class="week-number">
                <a href="&(prefix);week/<?php echo date('Y-m-d'); ?>/"><?php echo strftime('%V', $data['week_start']); ?></a>
            </th>
