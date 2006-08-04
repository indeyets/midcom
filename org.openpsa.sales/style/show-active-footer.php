
        </tbody>
    </table>
<?php
    $reports_node = midcom_helper_find_node_by_component('org.openpsa.reports');
    if (!empty($reports_node))
    {
        $reports_prefix = $reports_node[MIDCOM_NAV_FULLURL];
        $filename = 'org_openpsa_sales_' . date('Ymd_Hi');
?>
    <form method="post" action="&(reports_prefix);csv/&(filename);.csv" onSubmit="return table2csv('org_openpsa_sales_activeprojects');">
        <input type="hidden" id="csvdata" name="org_openpsa_reports_csv" value="" />
        <input class="button" type="submit" value="<?php echo $_MIDCOM->i18n->get_string('download as CSV', 'org.openpsa.reports'); ?>" />
    </form>
<?php
    }
?>

</div>