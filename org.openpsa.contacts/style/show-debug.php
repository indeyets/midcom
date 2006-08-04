<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
echo "<p>\n";
echo 'time:' . time() . "<br>\n";
/*
phpinfo();

echo "<pre>\n";
print_r($_MIDGARD);
echo "</pre>\n";
*/



$group = new org_openpsa_contacts_group(17);
$group2 = new org_openpsa_contacts_group(18);
/*
echo "group: <pre>\n" . sprint_r($group) . "</pre>\n";
echo "group2: <pre>\n" . sprint_r($group2) . "</pre>\n";
*/

/*
$dfinder = new org_openpsa_contacts_duplicates();
$dfinder->config =& $view_data['config'];
$p_arr = $dfinder->p_duplicate_group($group, $group2);
echo "p_arr: <pre>\n" . sprint_r($p_arr) . "</pre>\n";

$dupes = $dfinder->find_duplicates_group($group);
echo "dupes: <pre>\n" . sprint_r($dupes) . "</pre>\n";
*/


$person = new org_openpsa_contacts_person(199);
$person2 = new org_openpsa_contacts_person(200);
echo "person: <pre>\n" . sprint_r($person) . "</pre>\n";
echo "person2: <pre>\n" . sprint_r($person2) . "</pre>\n";
$dfinder = new org_openpsa_contacts_duplicates();
$dfinder->config =& $view_data['config'];
$p_arr = $dfinder->p_duplicate_person($person, $person2);
echo "p_arr: <pre>\n" . sprint_r($p_arr) . "</pre>\n";

$dupes = $dfinder->find_duplicates_person($person);
echo "dupes: <pre>\n" . sprint_r($dupes) . "</pre>\n";


?>