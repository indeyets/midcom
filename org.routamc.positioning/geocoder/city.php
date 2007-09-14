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
class org_routamc_positioning_geocoder_city extends org_routamc_positioning_geocoder
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_positioning_geocoder_city()
    {
         $this->_component = 'org.routamc.positioning';
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
            
        if (!isset($location['city']))
        {
            $this->error = 'POSITIONING_MISSING_ATTRIBUTES';        
            return null;
        }
        
        $city_entry = null;
        $qb = org_routamc_positioning_city_dba::new_query_builder();
        $qb->add_constraint('city', '=', $location['city']);
        
        if (isset($location['country']))
        {
            $qb->add_constraint('country', '=', $location['country']);
        }
        $matches = $qb->execute();
        if (count($matches) > 0)
        {
            $city_entry = $matches[0];
        }

        if (is_null($city_entry))
        {
            // Seek the city entry by alternate names via a LIKE query
            $qb = org_routamc_positioning_city_dba::new_query_builder();
            $qb->add_constraint('alternatenames', 'LIKE', "%|{$location['city']}|%");

            if (isset($location['country']))
            {
                $qb->add_constraint('country', '=', $location['country']);
            }
            
            $matches = $qb->execute();
            if (count($matches) > 0)
            {
                $city_entry = $matches[0];
            }
        }
        
        if (is_null($city_entry))
        {
            $this->error = 'POSITIONING_CITY_NOT_FOUND';
            return null;
        }
        
        $position['latitude' ] = $city_entry->latitude;
        $position['longitude' ] = $city_entry->longitude;
        $position['city' ] = $city_entry->city;
        $position['region' ] = $city_entry->region;
        $position['country' ] = $city_entry->country;
        $position['accuracy'] = ORG_ROUTAMC_POSITIONING_ACCURACY_CITY;
        
        return $position;
    }
    
    /**
     * Empty default implementation, this won't do anything yet
     *
     * @param Array $coordinates Contains latitude and longitude values
     * @return Array containing geocoded information
     */
    function reverse_geocode($coordinates,$options=array())
    {
        $this->error = 'METHOD_NOT_IMPLEMENTED';        
        return null;
    }
}