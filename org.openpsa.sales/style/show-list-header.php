<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <h1><?php echo sprintf($data['l10n']->get('salesprojects: %s'), $data['l10n']->get($data['list_title'])); ?></h1>

    <script type="text/javascript" src="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.reports/table2csv.js"></script>
    <table id="org_openpsa_sales_activeprojects" class="salesprojects">
        <thead>
            <tr>
                <?php echo org_openpsa_sales_sort::link('title', $data['l10n']->get('title')); ?>
                <?php echo org_openpsa_sales_sort::link('customer', $data['l10n']->get('customer')); ?>
                <?php echo org_openpsa_sales_sort::link('owner', $data['l10n']->get('owner')); ?>
                <?php echo org_openpsa_sales_sort::link('close_est', $data['l10n']->get('estimated closing date')); ?>
                <?php echo org_openpsa_sales_sort::link('probability', $data['l10n']->get('probability')); ?>
                <?php echo org_openpsa_sales_sort::link('value', $data['l10n']->get('value')); ?>
                <?php echo org_openpsa_sales_sort::link('weighted_value', $data['l10n']->get('weighted value')); ?>
                <?php echo org_openpsa_sales_sort::link('profit', $data['l10n']->get('profit')); ?>
                <?php echo org_openpsa_sales_sort::link('prev_action', $data['l10n']->get('previous action')); ?>
                <?php echo org_openpsa_sales_sort::link('next_action', $data['l10n']->get('next action')); ?>
            </tr>
        </thead>
        <tbody>