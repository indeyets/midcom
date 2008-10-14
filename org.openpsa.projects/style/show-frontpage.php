<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <h1><?php echo $data['l10n']->get('projects'); ?></h1>
    <?php
    $class = "even";
    echo "<table class='tasks'>\n";
    echo "  <thead>\n";
    echo "    <tr>\n";
    echo "        <th>" . $data['l10n']->get('customer') . "</th>\n";
    echo "        <th>" . $data['l10n']->get('project') . "</th>\n";
    echo "        <th>" . $data['l10n']->get('status') . "</th>\n";
    echo "        <th>" . $data['l10n']->get('start') . "</th>\n";
    echo "        <th>" . $data['l10n']->get('end') . "</th>\n";
    echo "        <th>" . $data['l10n']->get('tasks') . "</th>\n";
    echo "    </tr>\n";
    echo "  </thead>\n";
    
    foreach ($data['customers'] as $customer => $projects)
    {
        $customer_title = $data['l10n']->get('no customer');
        if ($customer != 0)
        {
            $customer = new org_openpsa_contacts_group_dba($customer);
            $customer_title = $customer->official;
        }

        foreach ($projects as $project)
        {
            if ($class == "even")
            {
                $class = '';
            }
            else
            {
                $class = "even";
            }

            $task_qb = org_openpsa_projects_project::new_query_builder();
            $task_qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
            $task_qb->add_constraint('up', '=', $project->id);
            $task_count = $task_qb->count();

            echo "    <tr class='{$class}'>\n";
            echo "        <td>{$customer_title}</td>\n";
            echo "        <td><a href=\"{$prefix}project/{$project->guid}/\">{$project->title}</a></td>\n";
            echo "        <td>" . $data['l10n']->get($project->status_type) . "</td>\n";
            echo "        <td> " . strftime('%x', $project->start) . "</td>\n";
            echo "        <td> " . strftime('%x', $project->end) . "</td>\n";
            echo "        <td>{$task_count}</td>\n";
            echo "    </tr>\n";
        }
    }
    ?>
    
      <tfoot>
        <td colspan="6">
        <?php
        echo sprintf($data['l10n']->get('%d closed projects'), $data['closed_count']);
        ?>
        </td>
      </tfoot>
    </table>
</div>