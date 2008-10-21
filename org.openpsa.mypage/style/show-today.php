<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view_today =& $data['view_today'];
?>
<div class="org_openpsa_mypage main">
    <?php
    
    if ($data['calendar_url'])
    {
        ?>
        <div class="agenda">
            <?php
            $_MIDCOM->dynamic_load($data['calendar_url'] . 'agenda/day/' . date('Y-m-d', $data['requested_time']));
            ?>
        </div>
        <?php
    }
    
    if ($data['wiki_url'])
    {
        ?>
        <div class="wiki">
            <?php
            $_MIDCOM->dynamic_load($data['wiki_url'] . 'latest/');
            ?>
        </div>
        <?php
    }
    ?>
</div>

<div class="sidebar">
    <?php
    if ($data['projects_url'])
    {
        $tasks = org_openpsa_projects_task_resource_dba::get_resource_tasks('guid');
        if (count($tasks) > 0)
        {
            $workingon = new org_openpsa_mypage_workingon();
            ?>
            <div class="org_openpsa_mypage_workingon">
                <h2><?php echo $data['l10n']->get('now working on'); ?></h2>
                <form method="post" action="workingon/set/">
                    <input type="hidden" name="url" value="&(prefix);" />
                    <select name="task" onchange="this.form.submit();">
                        <option value="none"><?php echo $data['l10n']->get('not working on a task'); ?></option>
                        <?php
                        foreach ($tasks as $guid => $label)
                        {
                            $selected = '';
                            if (   !is_null($workingon->task)
                                && $workingon->task->guid == $guid)
                            {
                                $selected = ' selected="selected"';
                            }
                            echo "<option value=\"{$guid}\"{$selected}>{$label}</option>\n";
                        }
                        ?>
                    </select>
                    <?php
                    if ($workingon->task)
                    {
                        ?>
                        <label class="calculator">
                            <input type="text" id="org_openpsa_mypage_workingon_time" name="&(workingon.start);" value="<?php echo $workingon->format_time(); ?>" />
                            <span>h</span>
                        </label>
                        <script type="text/javascript">
                            timeCounter = new workingOnCalculator('org_openpsa_mypage_workingon_time', <?php echo time(); ?>);
                        </script>
                        <?php
                    }
                    ?>
                </form>
            </div>
            <?php
        }
    }
    
    // List expenses
    if ($data['expenses_url'])
    {
        echo "<div class=\"expenses\">\n";
        echo "<h2>" . $data['l10n']->get('this week') . "</h2>\n";
        
        if (count($data['hours']) > 0)
        {
            $invoiceable_hours = 0;
            $uninvoiceable_hours = 0;
            $total_hours = 0;
            foreach ($data['hours'] as $hour_report)
            {
                if ($hour_report->invoiceable)
                {
                    $invoiceable_hours += $hour_report->hours;
                    // TODO: Load customer to list
                }
                else
                {
                    $uninvoiceable_hours += $hour_report->hours;
                }
                $total_hours += $hour_report->hours;
            }
            echo "<table class=\"hours\">\n";
            echo "    <tr>\n";
            echo "        <td>" . $data['l10n']->get('invoiceable') . "</td>\n";
            echo "        <td>". round($invoiceable_hours, 1) ."</td>\n";
            echo "    </tr>\n";
            echo "    <tr>\n";
            echo "        <td>" . $data['l10n']->get('uninvoiceable') . "</td>\n";
            echo "        <td>". round($uninvoiceable_hours, 1) . "</td>\n";
            echo "    </tr>\n";
            echo "</table>\n";
            echo "<p><a href=\"{$data['expenses_url']}\">" . sprintf($data['l10n']->get('see all %s hours'), round($total_hours, 1)). "</a></p>\n";
        }
        else
        {
      echo "<p><a href=\"{$data['expenses_url']}\">" . $_MIDCOM->i18n->get_string('report hours', 'org.openpsa.expenses') . "</a></p>\n";
        }
        
        // TODO: Show expenses and mileages
        
        echo "</div>\n";
    }
    ?>
</div>