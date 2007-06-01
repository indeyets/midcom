<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$task_status = $data['object'];
$task = new org_openpsa_projects_task($task_status->task);
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
        if ($data['projects_node'])
        {
            $task_label = "<a href=\"{$data['projects_node'][MIDCOM_NAV_FULLURL]}task/{$task->guid}\">{$task_label}</a>";
        }

        $status_changer_label = $_MIDCOM->i18n->get_string('system', 'org.openpsa.projects');
        $target_person_label = $_MIDCOM->i18n->get_string('system', 'org.openpsa.projects');
        if ($task_status->creator)
        {
            $status_changer = new org_openpsa_contactwidget(new midcom_db_person($task_status->creator));
            $status_changer_label = $status_changer->show_inline();
        }

        if ($task_status->targetPerson)
        {
            $target_person = new org_openpsa_contactwidget(new midcom_db_person($task_status->targetPerson));
            $target_person_label = $target_person->show_inline();
        }

        $message = sprintf($_MIDCOM->i18n->get_string($task_status->get_status_message(), 'org.openpsa.projects'), $target_person_label, $status_changer_label);

        echo "{$task_label}: {$message}";
        ?>
    </td>
</tr>