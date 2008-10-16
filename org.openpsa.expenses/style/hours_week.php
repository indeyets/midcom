<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

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
foreach ($data['tasks'] as $task => $days)
{
    $task = new org_openpsa_projects_task_dba($task);
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
            $hours_total = $days[$date_identifier];

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

$previous_week = $data['requested_time'] - 3600 * 24 * 7;
$next_week = $data['requested_time'] + 3600 * 24 * 7;
echo "<p>\n";
echo "<a href=\"{$prefix}" . $previous_week . "/\">&laquo; ". $data['l10n']->get('previous week') . "</a> | \n"; 
echo "<a href=\"{$prefix}" . $next_week . "/\">". $data['l10n']->get('next week') . " &raquo;</a>\n"; 
echo "</p>\n";
?>