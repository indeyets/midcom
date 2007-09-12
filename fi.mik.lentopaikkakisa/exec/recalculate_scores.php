<?php
// Cache scores
$_MIDCOM->auth->require_admin_user();

$pilot_names = array();
$persons_by_name = array();
$persons = array();
$person_aerodromes = array();
$organizations = array();
$organization_aerodromes = array();
$dupes = 0;

$qb = fi_mik_flight_dba::new_query_builder();
$flights = $qb->execute();

foreach ($flights as $flight)
{
    if (!isset($pilot_names[$flight->pilot]))
    {
        $pilot = new midcom_db_person($flight->pilot);
        $pilot_names[$flight->pilot] = $pilot->name;
    }
    
    if (!isset($persons[$flight->pilot]))
    {
        $persons[$flight->pilot] = 0;
    }
    if (!isset($persons_by_name[$pilot_names[$flight->pilot]]))
    {
        $persons_by_name[$pilot_names[$flight->pilot]] = 0;
    }
    if (!isset($person_aerodromes[$flight->pilot]))
    {
        $person_aerodromes[$flight->pilot] = array();
    }

    if (!isset($organizations[$flight->operator]))
    {
        $organizations[$flight->operator] = 0;
    }
    if (!isset($organization_aerodromes["{$flight->pilot}_{$flight->operator}"]))
    {
        $organization_aerodromes["{$flight->pilot}_{$flight->operator}"] = array();
    }
    
    if (!isset($person_aerodromes[$flight->pilot][$flight->origin]))
    {
        // Same combo of aerodrome and pilot gains score only once
        $persons_by_name[$pilot_names[$flight->pilot]] += (float) $flight->scoreorigin;
        $persons[$flight->pilot] += (float) $flight->scoreorigin;
        $person_aerodromes[$flight->pilot][$flight->origin] = $flight->scoreorigin;
    }
    else
    {
        echo "{$pilot_names[$flight->pilot]} already has score {$person_aerodromes[$flight->pilot][$flight->origin]} from {$flight->origin}<br />\n";
        $dupes += $flight->scoreorigin;
    }

    if (!isset($organization_aerodromes["{$flight->pilot}_{$flight->operator}"][$flight->origin]))
    {
        // Same combo of aerodrome, organization and pilot gains score only once
        $organizations[$flight->operator] += (float) $flight->scoreorigin;
        $organization_aerodromes["{$flight->pilot}_{$flight->operator}"][$flight->origin] = $flight->scoreorigin;
    }
    
    if (!isset($person_aerodromes[$flight->pilot][$flight->destination]))
    {
        // Same combo of aerodrome and pilot gains score only once
        $persons_by_name[$pilot_names[$flight->pilot]] += (float) $flight->scoredestination;
        $persons[$flight->pilot] += (float) $flight->scoredestination;
        $person_aerodromes[$flight->pilot][$flight->destination] = $flight->scoredestination;
    }
    else
    {
        echo "{$pilot_names[$flight->pilot]} already has score {$person_aerodromes[$flight->pilot][$flight->destination]} from {$flight->destination}<br />\n";
        $dupes += $flight->scoredestination;
    }

    if (!isset($organization_aerodromes["{$flight->pilot}_{$flight->operator}"][$flight->destination]))
    {
        // Same combo of aerodrome, organization and pilot gains score only once
        $organizations[$flight->operator] += (float) $flight->scoredestination;
        $organization_aerodromes["{$flight->pilot}_{$flight->operator}"][$flight->destination] = $flight->scoredestination;
    }
}

echo "<p>{$dupes} duplicate points in total</p>\n";

arsort($organizations);
arsort($persons_by_name);
echo "<pre>\n";
print_r($organizations);
print_r($persons_by_name);
echo "</pre>\n";

foreach ($persons as $id => $score)
{
    $person = new midcom_db_person($id);
    if ($person)
    {
        $person->set_parameter('fi.mik.lentopaikkakisa', 'person_scores', $score);
    }
}
foreach ($organizations as $id => $score)
{
    $organization = new midcom_db_group($id);
    if ($organization)
    {
        $organization->set_parameter('fi.mik.lentopaikkakisa', 'organization_scores', $score);
    }
}
?>