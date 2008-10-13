<h2>Rambos playgound</h2>
<?php

/*
$made = mktime(1,2,3,4,5,2006);
echo date("Y-m-d H:i:s", $made) . "<br>\n";;
$_MIDCOM->componentloader->load_graceful('net.nemein.tag');
*/

$task = new org_openpsa_projects_task_dba('b093253a0c96d4cb7868787f8a2357fc');
$broker = new org_openpsa_projects_projectbroker();
$prospects = $broker->find_task_prospects($task);
echo "prospects:\n<pre>\n" . sprint_r($prospects) . "</pre>\n";

/*
$_MIDCOM->componentloader->load_graceful('org.openpsa.contacts');
$classes = array
(
    'midgard_person',
    'midcom_db_person',
    'midcom_org_openpsa_person',
    'org_openpsa_contacts_person',
);
$tags = array('php', 'midgard');
$persons = net_nemein_tag_handler::get_objects_with_tags($tags, $classes, 'OR');
echo "persons:\n<pre>\n" . sprint_r($persons) . "</pre>\n";
*/

?>