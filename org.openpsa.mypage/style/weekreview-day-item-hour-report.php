<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$hour_report = $data['object'];
$task = new org_openpsa_projects_task_dba($hour_report->task);
?>
<tr class="hour_report">
    <td class="time">
        <?php
        echo date('H:i', $data['time']);
        ?>
    </td>
    <td>
        <?php
        $task_label = $task->title;
        if ($data['projects_url'])
        {
            $task_label = "<a href=\"{$data['projects_url']}task/{$task->guid}\">{$task_label}</a>";
        }
        echo "{$task_label}, {$hour_report->description}: {$hour_report->hours}h";
        ?>
    </td>
</tr>