<?php
$_MIDCOM->auth->require_valid_user();
$_MIDCOM->header('content-type: text/xml');

$apml = new SimpleXMLElement('<APML></APML>');
$apml->addAttribute('xmlns', 'http://www.apml.org/apml-0.6');
$apml->addAttribute('version', '0.6');

// APML headers
$head = $apml->addChild('Head');
$head->addChild('Title', "APML for {$_MIDCOM->auth->user->name}");
$head->addChild('Generator', "Midgard/" . mgd_version() . ' MidCOM/' . $GLOBALS['midcom_version'] . ' PHP/' . phpversion());
$head->addChild('DateCreated', date('c'));

// APML content
$body = $apml->addChild('Body');

$qb = net_nemein_attention_concept_dba::new_query_builder();
$qb->add_constraint('person', '=', $_MIDGARD['user']);
$qb->add_order('profile');
$qb->add_order('explicit', 'ASC');

if (isset($_GET['profile']))
{
    $qb->add_constraint('profile', '=', $_GET['profile']);
}

$concepts = $qb->execute();
$profiles = array();
foreach ($concepts as $concept)
{
    if (empty($concept->profile))
    {
        $concept->profile = 'default';
    }
    
    if (!isset($profiles[$concept->profile]))
    {
        $profiles[$concept->profile] = $body->addChild('Profile');
        $profiles[$concept->profile]->addAttribute('name', $concept->profile);
    }
    
    $data_attribute = 'ImplicitData';
    if ($concept->explicit)
    {
        $data_attribute = 'ExplicitData';
    }
    if (!isset($profiles[$concept->profile]->$data_attribute))
    {
        $profiles[$concept->profile]->addChild($data_attribute);
    }
    $data =& $profiles[$concept->profile]->$data_attribute;
    
    if (!isset($data->Concepts))
    {
        $data->addChild('Concepts');
    }
    
    // And then actually add the concept
    $concept_element = $data->Concepts->addChild('Concept');
    $concept_element->addAttribute('key', $concept->concept);
    $concept_element->addAttribute('value', $concept->value);
    $concept_element->addAttribute('from', $concept->source);
    $concept_element->addAttribute('updated', date('c', $concept->metadata->published));
}

echo $apml->asXml();
?>