<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$report =& $data['report'];

?>
        <div class="org_openpsa_reports_report org_openpsa_invoices">
            <div class="header">
                <?php midcom_show_style('projects_report-basic-header-logo'); ?>
                <h1>&(report['title']);</h1>
            </div>

            <table class="report sales_report invoices" id="org_openpsa_reports_deliverable_reporttable">
                <thead>
                    <tr>
                        <th><?php echo $_MIDCOM->i18n->get_string('invoice number', 'org.openpsa.invoices'); ?></th>
                        <th><?php echo $_MIDCOM->i18n->get_string('due', 'org.openpsa.invoices'); ?></th>
                        <th><?php echo $_MIDCOM->i18n->get_string('customer', 'org.openpsa.invoices'); ?> </th>
                        <th><?php echo $_MIDCOM->i18n->get_string('customer contact', 'org.openpsa.invoices'); ?></th>
                        <th><?php echo $_MIDCOM->i18n->get_string('sum', 'org.openpsa.invoices'); ?></th>
                        <th><?php echo $_MIDCOM->i18n->get_string('vat', 'org.openpsa.invoices'); ?></th>
                    </tr>
                </thead>
                <tbody>