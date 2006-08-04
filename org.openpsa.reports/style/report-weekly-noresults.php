<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
//If we need to do any specific mangling etc, we do it here.
$query =& $view_data['query_data'];
$report =& $view_data['report'];
if (   !array_key_exists('title', $report)
    || empty($report['title']))
{
    $report['title'] = sprintf($view_data['l10n']->get('weekly report for %s - %s'), strftime('%x', $query['start']['timestamp']), strftime('%x', $query['end']['timestamp']));
}

if (   !isset($query['skip_html_headings'])
    || empty($query['skip_html_headings']))
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN""http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="" lang="">
    <head>
        <title>OpenPsa - &(report['title']);</title>
        <link rel="stylesheet" type="text/css" href="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.reports/projects-common.css" />
        <script type="text/javascript" src="<?php echo MIDCOM_STATIC_URL; ?>/org.openpsa.reports/table2csv.js"></script>
    </head>
    <body>
<?php
}
if (   !isset($query['resource_expanded'])
    || empty($query['resource_expanded']))
{
?>
        <div class="error">
            <h1><?php echo $view_data['l10n']->get('no results'); ?></h1>
            <p><?php echo $view_data['l10n']->get('no results found matching the report criteria'); ?></p>
        </div>
        <!--
        <div class="debug">
          <h1>Query data</h1>
          <pre><?php print_r($query); ?></pre>
        </div>
        -->
<?php
}
else
{
    /* TODO: Iterate trough $query['resource_expanded'] and draw empty tables for each person */
}
if (   !isset($query['skip_html_headings'])
    || empty($query['skip_html_headings']))
{
?>
    </body>
</html>
<?php
}
?>
