<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$report =& $data['report'];
$query_data =& $data['query_data'];
?>
        <div class="org_openpsa_reports_report org_openpsa_reports_basic">
            <div class="header">
<?php midcom_show_style('projects_report-basic-header-logo'); ?>
                <h1>&(report['title']);</h1>
            </div>
            <table class="report" id="org_openpsa_reports_basic_reporttable">
                <thead>
                    <tr>
<?php   switch($data['grouping'])
        {
            case 'date': ?>
                        <th><?php echo $data['l10n']->get('person'); ?></th>
<?php           break;
            case 'person': ?>
                        <th><?php echo $data['l10n']->get('date'); ?></th>
<?php           break;
        } ?>
                        <th><?php echo $data['l10n']->get('task'); ?></th>
<?php   if (   array_key_exists('hour_type_filter', $query_data)
            /* Cannot be checked from this array
            && !(   array_key_exists('hidden', $query_data['hour_type_filter'])
                 && !empty($query_data['hour_type_filter']['hidden']))
            */
            )
        {   ?>
                        <th><?php echo $data['l10n']->get('type'); ?></th>
<?php   }   ?>
<?php   if (   array_key_exists('invoiceable_filter', $query_data)
            /* Cannot be checked from this array
            && !(   array_key_exists('hidden', $query_data['invoiceable_filter'])
                 && !empty($query_data['invoiceable_filter']['hidden']))
            */
            )
        {   ?>
                        <th><?php echo $data['l10n']->get('invoiceable'); ?></th>
<?php   }   ?>
                        <th><?php echo $data['l10n']->get('description'); ?></th>
                        <th><?php echo $data['l10n']->get('hours'); ?></th>
                    </tr>
                </thead>