<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$l10n =& $data['l10n'];
$report =& $data['report'];
$link_data =& $data['use_link_data'];
if (!isset($data['form_suffix']))
{
    $data['form_suffix'] = '';
}
$form_suffix =& $data['form_suffix']; 
?>
    </table>
    <input type="submit" class="button create_campaign" value="<?php echo $l10n->get('create campaign from link'); ?>"/>
</form>
<?php
        $reports_node = midcom_helper_find_node_by_component('org.openpsa.reports');
        if (!empty($reports_node))
        {
            $reports_prefix = $reports_node[MIDCOM_NAV_FULLURL];
            $filename = 'org_openpsa_directmarketing_' . date('Ymd_Hi');
    ?>
<script type="text/javascript" src="<?php echo MIDCOM_STATIC_URL . '/org.openpsa.reports/table2csv.js'; ?>"></script>
<form method="post" action="&(reports_prefix);csv/&(filename);.csv" onSubmit="return table2csv('org_openpsa_directmarketing_messagelinks&(form_suffix);');">
    <input type="hidden" id="csvdata" name="org_openpsa_reports_csv" value="" />
    <input class="button" type="submit" value="<?php echo $_MIDCOM->i18n->get_string('download as CSV', 'org.openpsa.reports'); ?>" />
</form>
    <?php
        }
?>