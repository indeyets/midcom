<?php
if (!isset($_GET['service']))
{
    $service = 'city';
}
else
{
    $service = $_GET['service'];
}

if (!isset($_GET['dir']))
{
    $direction = '';
}
else
{
    $direction = 'reverse_';
}

$geocoder = org_routamc_positioning_geocoder::create($service);

$location = array();
foreach ($_GET as $key => $value)
{
    switch ($key)
    {
        case 'longitude':
        case 'latitude':
            if (! empty($direction))
            {
                $location[$key] = $value;
            }
        // Accept only XEP-0080 values
        case 'area':
        case 'building':
        case 'country':
        case 'description':
        case 'floor':
        case 'city':
        case 'postalcode':
        case 'region':
        case 'room':
        case 'street':
        case 'text':
        case 'uri':
            $location[$key] = $value;
            break;
    }
}

$method_name = "{$direction}geocode";

$position = $geocoder->$method_name($location);
if (is_null($position))
{
    $error_str = 'unknown';
    if (isset($geocoder->error))
    {
        $error_str = $geocoder->error;        
    }
    //$_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Geocoding failed: {$error_str}");

    $_MIDCOM->header('HTTP/1.0 500 Server Error');
    echo "Geocoding failed: {$error_str}";
    $_MIDCOM->finish();
    exit();
    // This will exit
}

$_MIDCOM->cache->content->content_type("text/xml");
$_MIDCOM->header("Content-type: text/xml; charset=UTF-8");

echo "<position>\n";
foreach ($position as $key => $value)
{
    echo "    <{$key}>{$value}</{$key}>\n";
}
echo "</position>\n";
?>