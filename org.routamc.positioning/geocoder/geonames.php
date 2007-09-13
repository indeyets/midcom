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
    function org_routamc_positioning_geocoder_geonames()
    {
         $this->_component = 'org.routamc.positioning';
         $_MIDCOM->load_library('org.openpsa.httplib');
         parent::org_routamc_positioning_geocoder();
    }

    /**
     * Empty default implementation, this calls won't do much.
     *
     * @param Array $location Parameters to geocode with, conforms to XEP-0080
     * @return Array containing geocoded information
     */
    function geocode($location)
    {
        $position = array
        (
            'latitude' => null,
            'longitude' => null,
            'accuracy' => null,
        );
            
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
        
        $http_request = new org_openpsa_httplib();
        $response = $http_request->get('http://ws.geonames.org/postalCodeSearch?' . implode('&', $params));
        $simplexml = simplexml_load_string($response);

        if (   !isset($simplexml->code)
            || count($simplexml->code) == 0)
        {
            $this->error = 'POSITIONING_CITY_NOT_FOUND';
            return null;
        }
        
        $city_entry = $simplexml->code[0];
        $position['latitude' ] = (float) $city_entry->lat;
        $position['longitude' ] = (float) $city_entry->lng;
        $position['city' ] = (string) $city_entry->name;
        $position['region' ] = (string) $city_entry->adminName2;
        $position['country' ] = (string) $city_entry->countryCode;
        $position['postalcode' ] = (string) $city_entry->postalcode;
        $position['accuracy'] = ORG_ROUTAMC_POSITIONING_ACCURACY_CITY;

        return $position;
    }
}