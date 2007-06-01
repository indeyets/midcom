<?php
// Available request keys: event, datamanager

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_reservation'];

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$data['event_widget'] = new org_openpsa_calendarwidget_event($data['event']);
$rendered = $data['event_widget']->render_timelabel(0);
?>
<h1 class="reservation">&(rendered:h);: &(view['title']:h);</h1>

<?php
if (count($data['event']->participants) > 0)
{
    echo "<h2>" . $data['l10n']->get('participants') . "</h2>\n";
    echo "<ul class=\"participants\">\n";
    foreach ($data['event']->participants as $participant => $included)
    {
        $person = new midcom_db_person($participant);
        $contact = new org_openpsa_contactwidget($person);

        echo "<li>" . $contact->show_inline() . "</li>\n";
    }
    echo "</ul>\n";
}

if (count($data['event']->resources) > 0)
{
    echo "<h2>" . $data['l10n']->get('resources') . "</h2>\n";
    echo "<ul class=\"resources\">\n";
    foreach ($data['event']->resources as $resource => $included)
    {
        $resource = new org_openpsa_calendar_resource_dba($resource);
        echo "<li><a href=\"{$prefix}view/{$resource->name}/\">{$resource->title}</a>, {$resource->location}</li>\n";
    }
    echo "</ul>\n";
}
?>

&(view['description']:h);
