<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <h1><?php echo $view_data['l10n']->get('active salesprojects'); ?></h1>

    <script type="text/javascript" src="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.reports/table2csv.js"></script>
    <table id="org_openpsa_sales_activeprojects">
        <thead>
            <tr>
                <?php echo org_openpsa_sales_sort_link('title', $view_data['l10n']->get('title')); ?>
                <?php echo org_openpsa_sales_sort_link('customer', $view_data['l10n']->get('customer')); ?>
                <?php echo org_openpsa_sales_sort_link('owner', $view_data['l10n']->get('owner')); ?>
                <?php echo org_openpsa_sales_sort_link('close_est', $view_data['l10n']->get('estimated closing date')); ?>
                <?php echo org_openpsa_sales_sort_link('probability', $view_data['l10n']->get('probability')); ?>
                <?php echo org_openpsa_sales_sort_link('value', $view_data['l10n']->get('value')); ?>
                <?php echo org_openpsa_sales_sort_link('weighted_value', $view_data['l10n']->get('weighted value')); ?>
                <?php echo org_openpsa_sales_sort_link('prev_action', $view_data['l10n']->get('previous action')); ?>
                <?php echo org_openpsa_sales_sort_link('next_action', $view_data['l10n']->get('next action')); ?>
            </tr>
        </thead>
        <tbody>