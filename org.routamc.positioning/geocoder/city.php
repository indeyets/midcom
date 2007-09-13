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
class org_routamc_positioning_geocoder_city extends midcom_baseclasses_components_purecode
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_positioning_geocoder_city()
    {
         $this->_component = 'org.routamc.positioning';
         parent::midcom_baseclasses_components_purecode();
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
}