<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<h1><?php echo sprintf($view_data['l10n']->get('sales report %s - %s'), strftime('%x', $view_data['start']), strftime('%x', $view_data['end'])); ?></h1>

<table class="sales_report">
    <thead>
        <tr>
            <th><?php echo $view_data['l10n']->get('invoices'); ?></th>
            <?php
            if ($view_data['handler_id'] != 'deliverable_report')
            {
                echo "            <th>" . $view_data['l10n']->get('owner') . "</th>\n";
            }
            ?>
            <th><?php echo $view_data['l10n']->get('customer'); ?></th>
            <th><?php echo $view_data['l10n']->get('salesproject'); ?></th>
            <th><?php echo $view_data['l10n']->get('product'); ?></th>
            <th><?php echo $view_data['l10n']->get('price'); ?></th>
            <th><?php echo $view_data['l10n']->get('cost'); ?></th>
            <th><?php echo $view_data['l10n']->get('profit'); ?></th>
            <th><?php echo $view_data['l10n']->get('calculation basis'); ?></th>
        </tr>
    </thead>
    <tbody>