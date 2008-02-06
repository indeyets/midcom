<?php
$_MIDCOM->auth->require_admin_user();
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);
while(@ob_end_flush());

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
        $qb2 = net_nemein_calendar_event_dba::new_query_builder();
        $qb2->add_constraint('parameter.domain', '=', 'net.nemein.calendar');
        $qb2->add_constraint('parameter.name', '=', 'old_event');
        $qb2->add_constraint('parameter.value', '=', $event->guid);
        $already_converted = $qb2->execute();
        if (!empty($already_converted))
        {
            echo "Event '{$event->title}' ALREADY copied, skipping<br>\n"; flush();
            continue;
        }
        
        $newevent = new net_nemein_calendar_event_dba();
        $newevent->name = $event->extra;
        $newevent->title = $event->title;
        $newevent->description = $event->description;
        //$newevent->location = $event->location;
                
        $newevent->start = gmdate('Y-m-d H:i:s', $event->start);
        $newevent->end = gmdate('Y-m-d H:i:s', $event->end);
        //$newevent->openregistration = gmdate('Y-m-d H:i:s', $event->openregistration);
        //$newevent->closeregistration = gmdate('Y-m-d H:i:s', $event->closeregistration);
        
        $newevent->node = $topic->id;
        //mgd_debug_start();
        if (!$newevent->create())
        {
            echo "Failed copying event {$event->title}: " . mgd_errstr() . "<br>\n"; flush();
            continue; 
        }
        //mgd_debug_stop();
        
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
        // Delete old event?, NOPE
        
        echo "Event '{$event->title}' copied<br>\n"; flush();
    }
}
ob_start();
?>