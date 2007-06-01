<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_project'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main org_openpsa_projects_project">
    <h1><?php echo $data['l10n']->get('project'); ?>: &(view['title']:h);</h1>
        
    <div class="status <?php echo $project->status_type; ?>"><?php echo $data['l10n']->get('project status') . ': ' . $data['l10n']->get($data['project']->status_type); ?></div>

    <div class="time">&(view['start']:h); - &(view['end']:h);</div>
            
    &(view['description']:h);
    
    <?php
    if (count($data['tasks']) > 0)
    {
        echo "<h2>" . $data['l10n']->get('tasks') . "</h2>\n";
        echo "<ul class=\"tasks\">\n";
        foreach ($data['tasks'] as $task)
        {
            echo "<li>\n";
            echo "    <div class=\"title\"><a href=\"{$prefix}task/{$task->guid}/\">{$task->title}</a></div>\n";
            echo "    <div class=\"time\">" . strftime('%x', $task->start) . ' - ' . strftime('%x', $task->end) . "</div>\n";
            echo "    <div class=\"description\">" . substr($task->description, 0, 60) . "...</div>\n";
            echo "</li>\n";
        }
        echo "</ul>\n";
    }
    // TODO: Show help message otherwise?
    ?>
</div>
<div class="sidebar">
    <?php
    $customer = new org_openpsa_contacts_group($data['project']->customer);
    if ($customer)
    {
        echo "<h2>" . $data['l10n']->get('customer') . "</h2>\n";
        echo $customer->official;
    }
    
    $manager = new org_openpsa_contacts_person($data['project']->manager);
    if ($manager)
    {
        echo "<h2>" . $data['l10n']->get('manager') . "</h2>\n";
        $contact = new org_openpsa_contactwidget($manager);
        echo $contact->show_inline();
    }
    elseif (count($data['project']->resources) > 0)
    {
        echo "<h2>" . $data['l10n']->get('resources') . "</h2>\n";
        foreach ($data['project']->resources as $contact_id => $display)
        {
            $contact = new org_openpsa_contacts_person($contact_id);
            $contact = new org_openpsa_contactwidget($contact);
            echo $contact->show_inline() . " ";
        }
    }

    if (count($data['project']->contacts) > 0)
    {
        echo "<h2>" . $data['l10n']->get('contacts') . "</h2>\n";
        foreach ($data['project']->contacts as $contact_id => $display)
        {
            $contact = new org_openpsa_contacts_person($contact_id);
            $contact = new org_openpsa_contactwidget($contact);
            echo $contact->show();
        }
    }
    ?>
</div>