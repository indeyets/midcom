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
class org_routamc_positioning_geocoder_yahoo extends org_routamc_positioning_geocoder
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_positioning_geocoder_yahoo()
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
        $params[] = 'appid=' . $this->_config->get('yahoo_application_key');
        $params[] = 'output=xml';

        if (isset($location['street']))
        {
            $params[] = 'street=' . urlencode($location['street']);
        }
        if (isset($location['city']))
        {
            $params[] = 'city=' . urlencode($location['city']);
        }
        if (isset($location['region']))
        {
            $params[] = 'state=' . urlencode($location['region']);
        }
        if (isset($location['postalcode']))
        {
            $params[] = 'zip=' . urlencode($location['postalcode']);
        }
               
        $http_request = new org_openpsa_httplib();
        $response = $http_request->get('http://local.yahooapis.com/MapsService/V1/geocode?' . implode('&', $params));
        $simplexml = simplexml_load_string($response);

        if (!isset($simplexml->Result->Latitude))
        {
            $this->error = 'POSITIONING_CITY_NOT_FOUND';
            return null;
        }
        
        $position['latitude' ] = (float) $simplexml->Result->Latitude;
        $position['longitude' ] = (float) $simplexml->Result->Longitude;
        $position['street' ] = (string) $simplexml->Result->Address;
        $position['city' ] = (string) $simplexml->Result->City;
        $position['region' ] = (string) $simplexml->Result->State;
        $position['country' ] = (string) $simplexml->Result->Country;
        $position['postalcode' ] = (string) $simplexml->Result->Zip;
        $position['accuracy'] = ORG_ROUTAMC_POSITIONING_ACCURACY_CITY;

        // Cleaner cases, Yahoo! returns uppercase
        $position['street'] = ucwords(strtolower($position['street']));
        $position['city'] = ucwords(strtolower($position['city']));
        
        foreach ($simplexml->Result->attributes() as $key => $val)
        {
            if ($key == 'warning')
            {
                $this->error = $val;
            }
            if ($key == 'precision')
            {
                switch ($val)
                {
                    case 'address':
                        $position['accuracy'] = ORG_ROUTAMC_POSITIONING_ACCURACY_ADDRESS;
                        break;
                    case 'street':
                        $position['accuracy'] = ORG_ROUTAMC_POSITIONING_ACCURACY_STREET;
                        break;                    
                    default:
                        $position['accuracy'] = ORG_ROUTAMC_POSITIONING_ACCURACY_CITY;
                        break;
                }
            }
        }

        return $position;
    }
}