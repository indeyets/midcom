<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$report =& $data['report'];

?>
        <div class="org_openpsa_reports_report org_openpsa_reports_deliverable">
            <div class="header">
                <?php midcom_show_style('projects_report-basic-header-logo'); ?>
                <h1>&(report['title']);</h1>
            </div>

            <table class="report sales_report" id="org_openpsa_reports_deliverable_reporttable">
                <thead>
                    <tr>
                        <th><?php echo $data['l10n']->get('invoices'); ?></th>
                        <?php
                        if ($data['handler_id'] != 'deliverable_report')
                        {
                            echo "            <th>" . $data['l10n']->get('owner') . "</th>\n";
                        }
                        ?>
                        <th><?php echo $data['l10n']->get('customer'); ?></th>
                        <th><?php echo $data['l10n']->get('salesproject'); ?></th>
                        <th><?php echo $data['l10n']->get('product'); ?></th>
                        <th><?php echo $data['l10n']->get('price'); ?></th>
                        <th><?php echo $data['l10n']->get('cost'); ?></th>
                        <th><?php echo $data['l10n']->get('profit'); ?></th>
                        <th><?php echo $data['l10n']->get('calculation basis'); ?></th>
                    </tr>
                </thead>
                <tbody>