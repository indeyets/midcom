<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
echo "<p>\n";
echo 'time:' . time() . "<br>\n";


/*
phpinfo();

echo "<pre>\n";
print_r($_MIDGARD);
echo "</pre>\n";
*/

/*
$_MIDCOM->componentloader->load('org.openpsa.calendar');
$event = new org_openpsa_calendar_event_dba(269);
$link_def = new org_openpsa_relatedto_relatedto_dba();
$link_def->fromComponent = 'org.openpsa.calendar';
$link_def->fromGuid = $event->guid;
$link_def->fromClass = get_class($event);
$link_def->status = ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED;

$event_suspects_projects = org_openpsa_relatedto_suspect::find_links_object_component($event, 'org.openpsa.projects', $link_def);
echo "event_suspects_projects<pre>" . org_openpsa_helpers::sprint_r($event_suspects_projects) . "</pre>\n";
*/

/*
$sp = new org_openpsa_sales_salesproject_dba('5f8093bb1db4afc10b984952ea4268b5');
$sp->get_actions();
echo "sp<pre>" . org_openpsa_helpers::sprint_r($sp) . "</pre>\n";
*/

/*
$rel = new org_openpsa_relatedto_relatedto_dba();
$rel->fromComponent = 'org.openpsa.wiki';
$rel->toComponent = 'org.openpsa.sales';
$rel->fromGuid = 'dummy1_' . time();
$rel->toGuid = 'dummy2_' . time();
$stat = $rel->create();
echo "got status '{$stat}', errstr: " . mgd_errstr() . " <br>\n<pre>" . org_openpsa_helpers::sprint_r($rel) . "</pre>\n";
*/



?>