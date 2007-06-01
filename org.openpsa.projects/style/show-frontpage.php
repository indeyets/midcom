<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <h1><?php echo $data['l10n']->get('projects'); ?></h1>
    <?php
    foreach ($data['customers'] as $customer => $projects)
    {
        if ($customer == 0)
        {
            echo "<h2>" . $data['l10n']->get('no customer') . "</h2>\n";
        }
        else
        {
            $customer = new org_openpsa_contacts_group($customer);
            echo "<h2>{$customer->official}</h2>\n";
        }
        
        echo "<table>\n";
        echo "    <tr>\n";
        echo "        <th>" . $data['l10n']->get('project') . "</th>\n";
        echo "        <th>" . $data['l10n']->get('status') . "</th>\n";
        echo "        <th>" . $data['l10n']->get('start') . "</th>\n";
        echo "        <th>" . $data['l10n']->get('end') . "</th>\n";
        echo "        <th>" . $data['l10n']->get('tasks') . "</th>\n";
        echo "    </tr>\n";
        foreach ($projects as $project)
        {
            $task_qb = org_openpsa_projects_project::new_query_builder();
            $task_qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
            $task_qb->add_constraint('up', '=', $project->id);
            $task_count = $task_qb->count();

            echo "    <tr>\n";
            echo "        <td><a href=\"{$prefix}project/{$project->guid}/\">{$project->title}</a></td>\n";
            echo "        <td>" . $data['l10n']->get($project->status_type) . "</td>\n";
            echo "        <td> " . strftime('%x', $project->start) . "</td>\n";
            echo "        <td> " . strftime('%x', $project->end) . "</td>\n";
            echo "        <td>{$task_count}</td>\n";
            echo "    </tr>\n";
        }
        echo "</table>\n";
    }
    ?>
    
    <p>
        <?php
        echo sprintf($data['l10n']->get('%d closed projects'), $data['closed_count']);
        ?>
    </p>
</div>