<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_data =& $_MIDCOM->get_custom_context_data('midcom_helper_datamanager2_widget_composite');
?>
<table>
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('product'); ?></th>
            <th><?php echo $data['l10n']->get('price'); ?></th>
            <th><?php echo $data['l10n']->get('cost'); ?></th>
            <th><?php echo $data['l10n']->get('units'); ?></th>
            <th><?php echo $data['l10n']->get('total'); ?></th>
            <th><?php echo $data['l10n']->get('total cost'); ?></th>
        </tr>
    </thead>
    <tbody>
