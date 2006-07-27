<?php
$_MIDCOM->auth->require_admin_user();

// If we import some other database than Geonames this is the place to tune
$fields_map = Array(
    'geonameid'        => 0,
    'name'             => 1,
    'ansiname'         => 2,
    'alternatenames'   => 3,
    'latitude'         => 4,
    'longitude'        => 5,
    'featureclass'     => 6, //see http://www.geonames.org/export/codes.html
    'featurecode'      => 7, // see http://www.geonames.org/export/codes.html
    'country'          => 8, // ISO-3166 2-letter
    'area'             => 9,
    'population'       => 10,
    'elevation'        => 11,
    'averageelevation' => 12,
);

if (   array_key_exists('cities_file_path', $_POST)
    && file_exists($_POST['cities_file_path']))
{
    $features = explode(',', $_POST['featurecodes_to_import']);

    //Disable limits
    @ini_set('memory_limit', -1);
    @ini_set('max_execution_time', 0);
    while (@ob_end_flush());
    
    $imported_cities = Array();
    
    // Read CSV file
    $cities_created = 0;
    $row = 0;
    $handle = fopen($_POST['cities_file_path'], 'r');
    while ($data = fgetcsv($handle, 1000, "\t"))
    {
        $row++;
        //if ($row > 1000) { break; }

        if (!in_array($data[$fields_map['featurecode']], $features))
        {
            continue;
        }
        
        if ($data[$fields_map['population']] < $_POST['population_to_import'])
        {
            continue;
        }
        
        if (strlen($data[$fields_map['country']]) > 2)
        {
            continue;
        }

        $new_city = new org_routamc_positioning_city_dba();
        $new_city->city      = $data[$fields_map['name']];
        $new_city->country   = $data[$fields_map['country']];
        $new_city->latitude  = $data[$fields_map['latitude']];
        $new_city->longitude = $data[$fields_map['longitude']];
        $new_city->population = $data[$fields_map['population']];
        $new_city->altitude = $data[$fields_map['averageelevation']];
        
        // Handle possible alternate names
        $alternate_names = explode(',', $data[$fields_map['alternatenames']]);
        if (count($alternate_names) > 0)
        {
            foreach ($alternate_names as $name)
            {
                $new_city->alternatenames .= "|{$name}";
            }
            $new_city->alternatenames .= '|';
        }
        
        if (   array_key_exists("{$new_city->country}:{$data[3]}:{$new_city->city}", $imported_cities)
            || $row == 1)
        {
            // We have city by this name for the country already
            continue;
        }
        
        echo "Adding {$new_city->city}, {$new_city->country}... ";
        
        if ($new_city->create())
        {
            echo "<span style=\"color: #00cc00;\">Success,</span> ";
            $imported_cities["{$new_city->country}:{$data[3]}:{$new_city->city}"] = true;
            $cities_created++;
        }
        else
        {
            echo "<span style=\"color: #cc0000;\">FAILED</span>, ";
        }
        echo mgd_errstr() . "<br />\n";
        flush();
    }
    
    echo "<p>{$cities_created} cities imported.</p>\n";
}
else
{
    ?>
    <h1>World Cities Database installation</h1>
    
    <p>
    You can use this script to install a <a href="http://www.geonames.org/export/#dump">Geonames city database</a>. 
    <a href="http://download.geonames.org/export/dump/">Download the database ZIP file</a>
    to your server, unzip it and provide its local path in the box below. Ensure that Apache can read it.
    </p>
    
    <p><strong>Please note that this process will take a long time.</strong> This can be anything between half hour and several hours
    to process the 3 million cities.</p>
    
    <form method="post">
        <label><a href="http://www.geonames.org/export/codes.html">Features</a> to import<br /><input type="text" name="featurecodes_to_import" value="PPL,PPLA,PPLC,PPLL,PPLS" /></label><br />
        <label>Minimum population<br /><input type="text" name="population_to_import" value="0" /></label><br />
        <label>File path<br /><input type="text" name="cities_file_path" value="/tmp/FI.txt" /></label>
        <input type="submit" value="Install" />
    </form>
    
    <p>
    If you want to install a custom cities list, it must be in a tab-delimited CSV file in the following format:
    </p>
    
    <pre>
660561  Borgå   Borga   Borgo,Porvo,Porvoo      60.4    25.6666667      P       PPL     FI      13      47192   0       37
    </pre>    
    <?php
}
?>