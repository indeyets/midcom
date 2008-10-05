<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$event = $data['object'];
?>
<tr class="event">
    <td class="time">
        <?php
        echo date('H:i', $event->start) . '-' . date('H:i', $event->end);
        ?>
    </td>
    <td>
        <?php
        $event_label = $event->title;
        if ($data['calendar_url'])
        {
            $event_url = "{$data['calendar_node'][MIDCOM_NAV_FULLURL]}event/{$event->guid}";
            $event_js = org_openpsa_calendar_interface::calendar_editevent_js($event->guid, $data['calendar_node']);
            $event_label = "<a href=\"{$event_url}\" onclick=\"{$event_js}\">{$event_label}</a>";
        }
        echo "{$event_label}, {$event->location}";
        ?>
    </td>
</tr>