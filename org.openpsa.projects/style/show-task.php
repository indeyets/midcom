<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view = $view_data['task_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="org_openpsa_helper_box history status">
    <?php
    $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_task_status');
    $qb->add_constraint('task', '=', $view_data['task']->id);
    $qb->add_order('timestamp', 'ASC');
    $qb->add_order('type', 'ASC');
    $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
    if (   is_array($ret)
        && count($ret) > 0)
    {
        echo "<h3>".$view_data['l10n']->get('status history')."</h3>\n";
        
        echo "<div class=\"current-status {$view_data['task']->status_type}\">".$view_data['l10n']->get('task status').': '.$view_data['l10n']->get($view_data['task']->status_type)."</div>\n";
        
        echo "<ul>\n";        
        foreach ($ret as $status_change)
        {
            echo "<li>";

            $status_changer = new org_openpsa_contactwidget(new midcom_baseclasses_database_person($status_change->creator));
            if ($status_change->targetPerson)
            {
                $target_person = new org_openpsa_contactwidget(new midcom_baseclasses_database_person($status_change->targetPerson));
            }
            switch ($status_change->type)
            {
                case ORG_OPENPSA_TASKSTATUS_PROPOSED:
                    $message = sprintf($view_data['l10n']->get('proposed to %s by %s'), $target_person->show_inline(), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_DECLINED:
                    $message = sprintf($view_data['l10n']->get('declined by %s'), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_ACCEPTED:
                    $message = sprintf($view_data['l10n']->get('accepted by %s'), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_ONHOLD:
                    $message = sprintf($view_data['l10n']->get('put on hold by %s'), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_STARTED:
                    $message = sprintf($view_data['l10n']->get('work started by %s'), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_REJECTED:
                    $message = sprintf($view_data['l10n']->get('rejected by %s'), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_REOPENED:
                    $message = sprintf($view_data['l10n']->get('re-opened by %s'), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_COMPLETED:
                    $message = sprintf($view_data['l10n']->get('marked as completed by %s'), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_APPROVED:
                    $message = sprintf($view_data['l10n']->get('approved by %s'), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_CLOSED:
                    $message = sprintf($view_data['l10n']->get('closed by %s'), $status_changer->show_inline());
                    break;
                case ORG_OPENPSA_TASKSTATUS_DBE_SYNC_OK:
                    $message = sprintf($view_data['l10n']->get('synchronized with %s by %s'), $target_person->show_inline(), $status_changer->show_inline());
                    break;
                default:
                    $message = sprintf("{$status_change->type} by %s",$status_changer->show_inline());
                    break;
            }
            $status_changed = strftime('%x %XZ', $status_change->created);
            echo "<span class=\"date\">{$status_changed}</span>: {$message}";

            echo "</li>\n";
        }
        echo "</ul>\n";
    }
    ?>
</div>
<div class="main">
    <?php $view->display_view(); ?>
</div>
<div class="area sidebar">
    <h2><?php echo $view_data['l10n']->get('hour reports'); ?></h2>
    <?php $view_data['hours_widget'][$view_data['task']->guid]->show(); ?>
</div>
