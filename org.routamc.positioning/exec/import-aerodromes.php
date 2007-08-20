<?php
$_MIDCOM->auth->require_admin_user();

$_MIDCOM->load_library('org.openpsa.httplib');
$http_request = new org_openpsa_httplib();

$csv = $http_request->get('http://weather.gladstonefamily.net/cgi-bin/location.pl/pjsg_all_location.csv?csv=1');
$lines = explode("\n", $csv);
foreach ($lines as $line)
{
    $line = str_replace('"', '', $line);
    $aerodromeinfo = explode(',', $line);

    // Skip the non-ICAO ones
    if (empty($aerodromeinfo[0]))
    {
        continue;
    }
    
    echo "<br />Importing {$aerodromeinfo[0]}...\n";
    $aerodrome = new org_routamc_positioning_aerodrome_dba();
    $aerodrome->icao = $aerodromeinfo[0];
    $aerodrome->wmo = $aerodromeinfo[1];
    $aerodrome->name = $aerodromeinfo[2];
    //$aerodrome->state = $aerodromeinfo[3];
    $aerodrome->country = $aerodromeinfo[4];
    $aerodrome->latitude = (float) $aerodromeinfo[5];
    $aerodrome->longitude = (float) $aerodromeinfo[6];
    $aerodrome->altitude = (int) $aerodromeinfo[7];
    
    $aerodrome->create();
    echo mgd_errstr();
}
?>