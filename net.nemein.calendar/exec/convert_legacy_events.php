<?php
$_MIDCOM->auth->require_admin_user();
$_MIDCOM->load_library('net.nehmer.markdown');

$qb = net_nemein_calendar_event::new_query_builder();
$qb->add_constraint('up', '<>', 0);
$qb->add_constraint('closeregistration', '=', 0);
$events = $qb->execute();
foreach ($events as $event)
{
    $update_event = false;
    
    $old_close_registration = $event->get_parameter('midcom.helper.datamanager2', 'close_registration');
    $old_open_registration = $event->get_parameter('midcom.helper.datamanager2', 'open_registration');
    
    if ($old_close_registration != '')
    {
        $close_registration = @strtotime($old_close_registration);
        if ($close_registration != -1)
        {
            $event->closeregistration = $close_registration;
            $update_event = true;
        }
    }
    
    if ($old_open_registration != '')
    {
        $open_registration = @strtotime($old_open_registration);
        if ($open_registration != -1)
        {
            $event->openregistration = $open_registration;
            $update_event = true;
        }
    }
    
    // TODO: Demarkdownize
    if (array_key_exists('demarkdownize', $_GET)
        && $_GET['demarkdownize'] == 1)
    {
        $markdown = new net_nehmer_markdown_markdown();
        $old_description = $event->description;
        $event->description = $markdown->render($old_description);
        
        if (   !empty($event->description)
            && $event->description != $old_description)
        {
            $update_event = true;
        }
    }
    
    if ($update_event)
    {
        $event->update();
    }
}
?>
