<?php
$_MIDCOM->auth->require_admin_user();

$topic_qb = midcom_db_topic::new_query_builder();
$topic_qb->add_constraint('component', '=', 'net.nemein.calendar');
$topics = $topic_qb->execute();

foreach ($topics as $topic)
{
    $root_event_guid = $topic->parameter('net.nemein.calendar', 'root_event');
    if (!$root_event_guid)
    {
        continue;
    }
    
    $root_event = new midcom_db_event($root_event_guid);
    if (   !$root_event
        || !$root_event->guid)
    {
        continue;
    }
    
    $qb = midcom_db_event::new_query_builder();
    $qb->add_constraint('up', '=', $root_event->id);
    $events = $qb->execute();
    foreach ($events as $event)
    {
        $newevent = new net_nemein_calendar_event();
        $newevent->name = $event->extra;
        $newevent->title = $event->title;
        $newevent->description = $event->description;
        //$newevent->location = $event->location;
                
        $newevent->start = gmdate('Y-m-d H:i:s', $event->start);
        $newevent->end = gmdate('Y-m-d H:i:s', $event->end);
        //$newevent->openregistration = gmdate('Y-m-d H:i:s', $event->openregistration);
        //$newevent->closeregistration = gmdate('Y-m-d H:i:s', $event->closeregistration);
        
        $newevent->node = $topic->id;
        
        if (!$newevent->create())
        {
            echo "Failed copying event {$event->title}: " . mgd_errstr() . "\n";
        }
        
        $params = $event->list_parameters();
        foreach ($params as $domain => $params)
        {
            foreach ($params as $name => $value)
            {
                $newevent->parameter($domain, $name, $value);
            }
        }
        
        // Link between the two in case something needs to be done manually afterwards
        $event->parameter('net.nemein.calendar', 'new_event', $newevent->guid);
        $newevent->parameter('net.nemein.calendar', 'old_event', $event->guid);
        
        // TODO: Copy atts?
        // TODO: Delete old event?
    }
}
?>
