<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['invoice_dm'];

$structure = new org_openpsa_core_structure();
$projects_url = $structure->get_node_full_url('org.openpsa.projects');
?>
<div class="main org_openpsa_invoices_invoice">
    <?php
    // Display invoice basic info
    $view->display_view();
    ?>
</div>
<?php
// Display invoiced hours and tasks
if (   array_key_exists('sorted_reports', $data)
    && array_key_exists('tasks', $data['sorted_reports'])
    && count($data['sorted_reports']['tasks']) > 0)
{
    echo "<div style=\"clear: both;\" class=\"hours\">\n";
    echo "<h2>" . $data['l10n']->get('invoiced hour reports') . "</h2>\n";
    $total = 0;
    foreach ($data['sorted_reports']['tasks'] as $task_id => $task_data)
    {
        $hours =& $task_data['reports'];
        $task = new org_openpsa_projects_task_dba($task_id);
        // TODO: Link etc
        echo "<h3><a href=\"{$projects_url}task/{$task->guid}/\">{$task->title}</a></h3>\n";
        echo "<table>\n";
        echo "    <tbody>\n";
        foreach ($hours as $hour_report)
        {
            echo "        <tr>\n";
            echo "            <td>" . strftime('%x', $hour_report->date) . "</td>\n";
            $reporter = new midcom_db_person($hour_report->person);
            // TODO: Contactwidget
            echo "            <td>{$reporter->rname}</td>\n";
            echo "            <td class=\"number\">{$hour_report->hours}h</td>\n";
            $total += $hour_report->hours;
            echo "            <td>{$hour_report->description}</td>\n";
            $approved_img_src = MIDCOM_STATIC_URL . '/stock-icons/16x16/';
            if ($hour_report->is_approved)
            {
                $approved_text = $data['l10n']->get('approved');
                $approved_img_src .= 'approved.png';
            }
            else
            {
                $approved_text = $data['l10n']->get('not approved');
                $approved_img_src .= 'cancel.png';
            }
            $approved_img =  "<img src='{$approved_img_src}' alt='{$approved_text}' title='{$approved_text}' />";
            echo "            <td>{$approved_img}</td>\n";
            echo "        </tr>\n";
        }
        echo "    </tbody>\n";
        echo "    <tfoot>\n";
        echo "        <tr>\n";
        echo "            <td colspan=2>" . $data['l10n']->get('total') . "</td>\n";
        echo "            <td class=\"number\">{$total}h</td>\n";
        echo "            <td colspan=2>&nbsp;</td>\n";
        echo "        </tr>\n";
        echo "    </tfoot>\n";
        echo "</table>\n";
    }
    echo "</div>\n";
}
?>