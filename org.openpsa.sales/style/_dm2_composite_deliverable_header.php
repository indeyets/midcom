<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_data =& $_MIDCOM->get_custom_context_data('midcom_helper_datamanager2_widget_composite');
?>
<table>
    <thead>
        <?php
        if ($view_data['item_total'] > 0)
        {
        ?>
        <tr>
            <th><?php echo $data['l10n']->get('product'); ?></th>
            <th><?php echo $data['l10n']->get('price'); ?></th>
            <th><?php echo $data['l10n_midcom']->get('units'); ?></th>
            <th><?php echo $data['l10n_midcom']->get('total'); ?></th>
        </tr>
        <?php
        }
        ?>
    </thead>
    <tbody>
