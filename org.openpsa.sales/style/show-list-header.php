<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <h1><?php echo sprintf($data['l10n']->get('salesprojects: %s'), $data['l10n']->get($data['list_title'])); ?></h1>

    <script type="text/javascript" src="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.reports/table2csv.js"></script>
    <table id="org_openpsa_sales_activeprojects" class="salesprojects">
        <thead>
            <tr>
                <?php echo org_openpsa_sales_sort_link('title', $data['l10n']->get('title')); ?>
                <?php echo org_openpsa_sales_sort_link('customer', $data['l10n']->get('customer')); ?>
                <?php echo org_openpsa_sales_sort_link('owner', $data['l10n']->get('owner')); ?>
                <?php echo org_openpsa_sales_sort_link('close_est', $data['l10n']->get('estimated closing date')); ?>
                <?php echo org_openpsa_sales_sort_link('probability', $data['l10n']->get('probability')); ?>
                <?php echo org_openpsa_sales_sort_link('value', $data['l10n']->get('value')); ?>
                <?php echo org_openpsa_sales_sort_link('weighted_value', $data['l10n']->get('weighted value')); ?>
                <?php echo org_openpsa_sales_sort_link('profit', $data['l10n']->get('profit')); ?>
                <?php echo org_openpsa_sales_sort_link('prev_action', $data['l10n']->get('previous action')); ?>
                <?php echo org_openpsa_sales_sort_link('next_action', $data['l10n']->get('next action')); ?>
            </tr>
        </thead>
        <tbody>