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



$group = new org_openpsa_contacts_group(17);
$group2 = new org_openpsa_contacts_group(18);
/*
echo "group: <pre>\n" . org_openpsa_helpers::sprint_r($group) . "</pre>\n";
echo "group2: <pre>\n" . org_openpsa_helpers::sprint_r($group2) . "</pre>\n";
*/

/*
$dfinder = new org_openpsa_contacts_duplicates();
$dfinder->config =& $data['config'];
$p_arr = $dfinder->p_duplicate_group($group, $group2);
echo "p_arr: <pre>\n" . org_openpsa_helpers::sprint_r($p_arr) . "</pre>\n";

$dupes = $dfinder->find_duplicates_group($group);
echo "dupes: <pre>\n" . org_openpsa_helpers::sprint_r($dupes) . "</pre>\n";
*/
/*
$dfinder = new org_openpsa_contacts_duplicates();
$dfinder->config =& $data['config'];

$ret = $dfinder->check_all_persons();
echo "check_all_persons returned: <pre>\n" . org_openpsa_helpers::sprint_r($ret) . "</pre>\n";

$ret2 = $dfinder->check_all_groups();
echo "check_all_groups returned: <pre>\n" . org_openpsa_helpers::sprint_r($ret2) . "</pre>\n";
*/

$qb = new midgard_query_builder('midgard_parameter');
$qb->add_constraint('domain', '=', 'org.openpsa.contacts.duplicates:possible_duplicate');
$qb->add_constraint('tablename', '=', 'person');
$qb->add_order('oid', 'ASC');
$qb->add_order('name', 'ASC');
$ret = $qb->execute();
foreach($ret as $param)
{
    $person1 = new org_openpsa_contacts_person($param->oid);
    $person2 = new org_openpsa_contacts_person($param->name);
    echo "Found marked as duplicate ids #{$person1->id} and #{$person2->id}<br/>\n";
}


/*
$person = new org_openpsa_contacts_person(20380);
$person2 = new org_openpsa_contacts_person(20395);
echo "person: <pre>\n" . org_openpsa_helpers::sprint_r($person) . "</pre>\n";
echo "person2: <pre>\n" . org_openpsa_helpers::sprint_r($person2) . "</pre>\n";
$dfinder = new org_openpsa_contacts_duplicates();
$dfinder->config =& $data['config'];
$p_arr = $dfinder->p_duplicate_person($person, $person2);
echo "p_arr: <pre>\n" . org_openpsa_helpers::sprint_r($p_arr) . "</pre>\n";

$dupes = $dfinder->find_duplicates_person($person);
echo "dupes: <pre>\n" . org_openpsa_helpers::sprint_r($dupes) . "</pre>\n";
*/

?>