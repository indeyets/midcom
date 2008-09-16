<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: geocoder.php 11571 2007-08-13 11:07:02Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Position geocodeing class that uses the local city database
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_geocoder_geonames extends org_routamc_positioning_geocoder
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function __construct()
    {
         $this->_component = 'org.routamc.positioning';
         $_MIDCOM->load_library('org.openpsa.httplib');
         parent::__construct();
    }

    /**
     *
     * @param Array $location Parameters to geocode with, conforms to XEP-0080
     * @return Array containing geocoded information
     */
    function geocode($location, $options=array())
    {
        $results = array();
        
        $parameters = array
        (
            'radius' => null,
            'maxRows' => 1,
            'style' => 'FULL',
        );
        
        if (! empty($options))
        {
            foreach ($options as $key => $value)
            {
                if (isset($parameters[$key]))
                {
                    $parameters[$key] = $value;
                }
            }
        }
            
        if (   !isset($location['postalcode'])
            && !isset($location['city']))
        {
            $this->error = 'POSITIONING_MISSING_ATTRIBUTES';
            return null;
        }
        $params = array();
        
        if (isset($location['postalcode']))
        {
            $params[] = 'postalcode=' . urlencode($location['postalcode']);
        }
        if (isset($location['city']))
        {
            $params[] = 'placename=' . urlencode($location['city']);
        }
        if (isset($location['country']))
        {
            $params[] = 'country=' . urlencode($location['country']);
        }

        foreach ($parameters as $key => $value)
        {
            if (! is_null($value))
            {
                $params[] = "{$key}=" . urlencode($value);
            }
        }
        
        $http_request = new org_openpsa_httplib();
        $response = $http_request->get('http://ws.geonames.org/postalCodeSearch?' . implode('&', $params));
        if (empty($response))
        {
            $this->error = 'POSITIONING_SERVICE_NOT_AVAILABLE';
            return null;
        }
        
        $simplexml = simplexml_load_string($response);

        if (   !isset($simplexml->code)
            || count($simplexml->code) == 0)
        {
            $this->error = 'POSITIONING_CITY_NOT_FOUND';
            return null;
        }
                
        for ($i=0; $i<$parameters['maxRows']; $i++)
        {
            if (! isset($simplexml->code[$i]))
            {
                break;
            }
            $entry = $simplexml->code[$i];
            
            $position = array();
            $position['latitude' ] = (float) $entry->lat;
            $position['longitude' ] = (float) $entry->lng;
            $position['distance'] = array
            (
                'meters' => round( (float) $entry->distance * 1000 ),
                'bearing' => null,
            );
            $position['city' ] = (string) $entry->name;
            $position['region' ] = (string) $entry->adminName2;
            $position['country' ] = (string) $entry->countryCode;
            $position['postalcode' ] = (string) $entry->postalcode;
            $position['alternate_names'] = (string) $entry->alternateNames;
            $position['accuracy'] = ORG_ROUTAMC_POSITIONING_ACCURACY_CITY;
            
            $results[] = $position;
        }

        return $results;
    }
    
    /**
     *
     * @param Array $coordinates Contains latitude and longitude values
     * @return Array containing geocoded information
     */
    function reverse_geocode($coordinates, $options=array())
    {
        $results = array();
        
        $parameters = array
        (
            'radius' => 10,
            'maxRows' => 20,
            'style' => 'FULL',
        );
        
        if (! empty($options))
        {
            foreach ($options as $key => $value)
            {
                if (isset($parameters[$key]))
                {
                    $parameters[$key] = $value;
                }
            }
        }
            
        if (   !isset($coordinates['latitude'])
            && !isset($coordinates['longitude']))
        {
            $this->error = 'POSITIONING_MISSING_ATTRIBUTES';
            return null;
        }
        $params = array();
        
        $params[] = 'lat=' . urlencode($coordinates['latitude']);
        $params[] = 'lng=' . urlencode($coordinates['longitude']);
        
        foreach ($parameters as $key => $value)
        {
            if (! is_null($value))
            {
                $params[] = "{$key}=" . urlencode($value);
            }
        }
        
        $http_request = new org_openpsa_httplib();
        $url = 'http://ws.geonames.org/findNearbyPlaceName?' . implode('&', $params);
        $response = $http_request->get($url);
        $simplexml = simplexml_load_string($response);
        
        if (   !isset($simplexml->geoname)
            || count($simplexml->geoname) == 0)
        {
            $this->error = 'POSITIONING_DETAILS_NOT_FOUND';
            
            if (isset($simplexml->status))
            {
                $constant_name = strtoupper(str_replace(" ", "_",$simplexml->status));
                $this->error = $constant_name;
            }
            return null;
        }
        
        for ($i=0; $i<$parameters['maxRows']; $i++)
        {
            if (! isset($simplexml->geoname[$i]))
            {
                break;
            }
            
            $entry = $simplexml->geoname[$i];

            $entry_coordinates = array
            (
                'latitude'  => (float) $entry->lat,
                'longitude' => (float) $entry->lng,
            );

            $meters = round( org_routamc_positioning_utils::get_distance($coordinates, $entry_coordinates) * 1000 );
            $entry_meters = round( (float) $entry->distance * 1000 );
            
            if ($entry_meters < $meters)
            {
                $meters = $entry_meters;
            }
            
            $position = array();
            $position['latitude' ] = (float) $entry->lat;
            $position['longitude' ] = (float) $entry->lng;
            $position['distance'] = array
            (
                'meters' => $meters,
                'bearing' => org_routamc_positioning_utils::get_bearing($coordinates, $entry_coordinates),
            );
            $position['city'] = (string) $entry->name;
            $position['region'] = (string) $entry->adminName2;
            $position['country'] = (string) $entry->countryCode;
            $position['postalcode' ] = (string) $entry->postalcode;
            $position['alternate_names'] = (string) $entry->alternateNames;
            $position['accuracy'] = ORG_ROUTAMC_POSITIONING_ACCURACY_GPS;

            $results[] = $position;            
        }
        
        return $results;
    }
}