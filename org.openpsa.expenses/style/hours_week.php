<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

// Sort the reports by task and day
$tasks = array();
foreach ($data['hours'] as $hour_report)
{
    if (!isset($tasks[$hour_report->task]))
    {
        $tasks[$hour_report->task] = array();
    }

    $date_identifier = date('Y-m-d', $hour_report->date);
    if (!isset($tasks[$hour_report->task][$date_identifier]))
    {
        $tasks[$hour_report->task][$date_identifier] = array();
    }
    $tasks[$hour_report->task][$date_identifier][] = $hour_report;
}

$date_totals = array();

// Header line
echo "<table class='expenses'>\n";

$time = $data['week_start'];
echo "  <thead>\n";
echo "    <tr>\n";
echo "        <th></th>\n";
while ($time < $data['week_end'])
{
    $next_time = $time + 3600 * 24;
    echo "        <th><a href=\"{$prefix}hours/between/" . date('Y-m-d', $time) . "/" .  date('Y-m-d', $next_time) . "/\">". strftime('%a', $time) . "</a></th>\n"; 
    
    // Hop to next day
    $time = $next_time;
}
echo "    </tr>\n";
echo "  </thead>\n";
$class = "even";
foreach ($tasks as $task => $days)
{
    $task = new org_openpsa_projects_task($task);
    $time = $data['week_start'];

    if ($class == "even")
    {
        $class = "";
    }
    else
    {
        $class = "even";
    }
    echo "    <tr class='{$class}'>\n";
    
    if (   !$task
        || !$task->guid)
    {
        echo "        <td>" . $data['l10n']->get('no task') . "</td>";
    }
    else
    {   
        echo "        <td><a href=\"{$prefix}hours/task/{$task->guid}/\">" . $task->get_label() . "</a></td>";
    }
    while ($time < $data['week_end'])
    {
        $date_identifier = date('Y-m-d', $time);
        if (!isset($days[$date_identifier]))
        {
            echo "<td></td>\n";
        }
        else
        {
            $hours_total = 0;
            foreach ($days[$date_identifier] as $hour_report)
            {
                $hours_total += $hour_report->hours;
            }
            
            if (!isset($date_totals[$date_identifier]))
            {
                $date_totals[$date_identifier] = 0;
            }
            $date_totals[$date_identifier] += $hours_total;
            
            echo "        <td>" . round($hours_total, 1) . "</td>\n"; 
        }
        // Hop to next day
        $time = $time + 3600 * 24;
    }
    echo "    </tr>\n";
}

$time = $data['week_start'];
echo "    <tr class=\"totals\">\n";
echo "        <td></td>\n";
while ($time < $data['week_end'])
{
    $date_identifier = date('Y-m-d', $time);
    
    if (!isset($date_totals[$date_identifier]))
    {
        echo "<td>0</td>\n";
    }    
    else
    {
        echo "        <td>" . round($date_totals[$date_identifier], 1) ."</td>\n"; 
    }
    // Hop to next day
    $time = $time + 3600 * 24;
}
echo "    </tr>\n";
echo "</table>\n";
?>