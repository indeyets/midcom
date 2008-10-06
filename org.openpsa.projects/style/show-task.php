<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_task =& $data['view_task'];
$task =& $data['task'];

$structure = new org_openpsa_core_structure();
$sales_url = $structure->get_node_full_url('org.openpsa.sales');

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="org_openpsa_projects_task">
    <div class="main">
        <div class="tags">(&(view_task['tags']:h);)</div>
        <h1><?php echo $data['l10n']->get('task'); ?>: &(view_task['title']:h);</h1>
        <div class="status &(task.status_type);"><?php echo $data['l10n']->get('task status') . ': ' . $data['l10n']->get($task->status_type); ?></div>

        <div class="time">&(view_task['start']:h); - &(view_task['end']:h);</div>

        <div class="description">
            &(view_task['description']:h);
        </div>

        <div class="bookings">
            <?php
            echo "<h2>" . $data['l10n']->get('booked times') . "</h2>\n";
            if (count($data['task_bookings']['confirmed']) > 0)
            {
                echo "<ul>\n";
                foreach ($data['task_bookings']['confirmed'] as $booking)
                {
                    echo "<li>";
                    echo strftime('%x', $booking->start) . ' ' . date('H', $booking->start) . '-' . date('H', $booking->end);

                    if ($data['calendar_node'])
                    {
                        echo ": <a href=\"#\" onclick=\"" . org_openpsa_calendar_interface::calendar_editevent_js($booking->guid, $data['calendar_node']) . "\">{$booking->title}</a>";
                    }
                    else
                    {
                        echo ": {$booking->title}";
                    }

                    echo " (";
                    foreach ($booking->participants as $participant_id => $display)
                    {
                        $participant = new org_openpsa_contacts_person($participant_id);
                        $participant = new org_openpsa_contactwidget($participant);
                        echo $participant->show_inline();
                    }
                    echo ")</li>\n";
                }
                echo "</ul>\n";
            }

            if ($data['task_booked_percentage'] >= 105)
            {
                $status = 'acceptable';
            }
            elseif ($data['task_booked_percentage'] >= 95)
            {
                $status = 'ok';
            }
            elseif ($data['task_booked_percentage'] >= 75)
            {
                $status = 'acceptable';
            }
            else
            {
                $status = 'bad';
            }
            echo "<p class=\"{$status}\">" . sprintf($data['l10n']->get('%s of %s planned hours booked'), $data['task_booked_time'], $task->plannedHours) . ".\n";
            echo "<a href=\"{$prefix}task/resourcing/{$data['task']->guid}/\">" . $data['l10n']->get('schedule resources') . "</a>.</p>\n";
            ?>
        </div>
    </div>
    <div class="sidebar">
        <?php
        if ($data['task']->agreement)
        {
            echo "<h2>" . $data['l10n']->get('agreement') . "</h2>\n";
            $agreement = new org_openpsa_sales_salesproject_deliverable($data['task']->agreement);

            if ($sales_url)
            {
                $salesproject = new org_openpsa_sales_salesproject($agreement->salesproject);
                $agreement->deliverable_html = "<a href=\"{$sales_url}salesproject/{$salesproject->guid}/#{$agreement->guid}\">{$agreement->deliverable_html}</a>\n";
            }

            echo $agreement->deliverable_html;
        }

        $manager = new org_openpsa_contacts_person($data['task']->manager);
        if ($manager)
        {
            echo "<h2>" . $data['l10n']->get('manager') . "</h2>\n";
            $contact = new org_openpsa_contactwidget($manager);
            echo $contact->show_inline();
        }

        $remote_search = $data['task']->parameter('org.openpsa.projects.projectbroker', 'remote_search');
        if ($remote_search)
        {
            echo "<div class=\"resources search\">\n";
            if ($remote_search == 'REQUEST_SEARCH')
            {
                echo $data['l10n']->get('remote resource search requested');
            }
            elseif ($remote_search == 'SEARCH_IN_PROGRESS')
            {
                echo $data['l10n']->get('remote resource search in progress');
                // TODO: Link to results listing
            }
            echo "</div>\n";
        }
        elseif (count($data['task']->resources) > 0)
        {
            echo "<h2>" . $data['l10n']->get('resources') . "</h2>\n";
            foreach ($data['task']->resources as $contact_id => $display)
            {
                $contact = new org_openpsa_contacts_person($contact_id);
                $contact = new org_openpsa_contactwidget($contact);
                echo $contact->show_inline() . " ";
            }
        }

        if (count($data['task']->contacts) > 0)
        {
            echo "<h2>" . $data['l10n']->get('contacts') . "</h2>\n";
            foreach ($data['task']->contacts as $contact_id => $display)
            {
                $contact = new org_openpsa_contacts_person($contact_id);
                $contact = new org_openpsa_contactwidget($contact);
                echo $contact->show();
            }
        }
        ?>
    </div>
    <div class="hours wide">
        &(view_task['hours']:h);
    </div>
</div>
<?php
/*
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

            $status_changer_label = $view_data['l10n']->get('system');
            $target_person_label = $view_data['l10n']->get('system');
            if ($status_change->creator)
            {
                $status_changer = new org_openpsa_contactwidget(new midcom_db_person($status_change->creator));
                $status_changer_label = $status_changer->show_inline();
            }

            if ($status_change->targetPerson)
            {
                $target_person = new org_openpsa_contactwidget(new midcom_db_person($status_change->targetPerson));
                $target_person_label = $target_person->show_inline();
            }

            $message = sprintf($view_data['l10n']->get($status_change->get_status_message(), 'org.openpsa.projects'), $target_person_label, $status_changer_label);
                $status_changed = strftime('%x %XZ', $status_change->created);
            echo "<span class=\"date\">{$status_changed}</span>: {$message}";

            echo "</li>\n";
        }
        echo "</ul>\n";
    }
    ?>
</div>
<div class="main">
    <?php $view_data['controller']->datamanager->display_view(); ?>
</div>
<?php
*/
?>