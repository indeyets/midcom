<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Importer for manually entered positions
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_importer_manual extends org_routamc_positioning_importer
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function __construct()
    {
         parent::__construct();
    }

    /**
     * Import manually entered log entry. The entries are associative arrays containing
     * some or all of the following keys:
     *
     * - latitude
     * - longitude
     * - altitude
     * - city
     * - country
     * - aerodrome
     * - timestamp
     *
     * @param Array $log Log entry in Array format specific to importer
     * @return boolean Indicating success.
     */
    function import($log)
    {
        $this->log = new org_routamc_positioning_log_dba();
        $this->log->importer = 'manual';

        // Set different person if required
        if (array_key_exists('person', $log))
        {
            $this->log->person = $log['person'];
        }
        else
        {
            $this->log->person = $_MIDGARD['user'];
        }
        
        if (array_key_exists('timestamp', $log))
        {
            $this->log->date = (int) $log['timestamp'];
        }
        else
        {
            $this->log->date = time();
        }

        // Figure out which option we will use, starting from best option

        // Best option: we know coordinates
        if (   array_key_exists('latitude', $log)
            && array_key_exists('longitude', $log))
        {
            // Manually entered positions are assumed to be only semi-accurate
            $this->log->accuracy = ORG_ROUTAMC_POSITIONING_ACCURACY_MANUAL;

            // Normalize coordinates to decimal
            $coordinates = $this->normalize_coordinates($log['latitude'], $log['longitude']);

            $this->log->latitude = $coordinates['latitude'];
            $this->log->longitude = $coordinates['longitude'];
        }

        // Airport entered
        if (array_key_exists('aerodrome', $log))
        {
            // Aerodrome position is not usually very accurate, except if we're at the airport of course
            $this->log->accuracy = ORG_ROUTAMC_POSITIONING_ACCURACY_CITY;

            // Normalize aerodrome name
            $aerodrome = strtoupper($log['aerodrome']);

            // Seek the aerodrome entry, first by accurate match
            $aerodrome_entry = null;
            $qb = org_routamc_positioning_aerodrome_dba::new_query_builder();
            $qb->begin_group('OR');
                // We will seek by both ICAO and IATA codes
                $qb->add_constraint('icao', '=', $aerodrome);
                $qb->add_constraint('iata', '=', $aerodrome);
            $qb->end_group();
            $matches = $qb->execute();
            if (count($matches) > 0)
            {
                $aerodrome_entry = $matches[0];
            }

            if (is_null($aerodrome_entry))
            {
                // Couldn't match the entered city to a location
                $this->error = 'POSITIONING_AERODROME_NOT_FOUND';
                return false;
            }

            // Normalize coordinates
            $this->log->latitude = $aerodrome_entry->latitude;
            $this->log->longitude = $aerodrome_entry->longitude;
            $this->log->altitude = $aerodrome_entry->altitude;
        }

        // City and country entered
        if (array_key_exists('city', $log))
        {
            if (!isset($log['geocoder']))
            {
                $log['geocoder'] = 'city';
            }
            $geocoder = org_routamc_positioning_geocoder::create($log['geocoder']);
            $position = $geocoder->geocode($log);
            
            if (   !$position['latitude']
                || !$position['longitude'])
            {
                // Couldn't match the entered city to a location
                $this->error = 'POSITIONING_CITY_NOT_FOUND';
                return false;
            }

            foreach ($position as $key => $value)
            {
                $this->log->$key = $value;
            }
        }

        // Save altitude if provided
        if (array_key_exists('altitude', $log))
        {
            $this->log->altitude = $log['altitude'];
        }

        // Try to create the entry
        //print_r($this->log);
        //die();
        $stat = $this->log->create();
        $this->error = mgd_errstr();
        return $stat;
    }

    /**
     * Modify country to conform to ISO standards
     */
    function normalize_country($country)
    {
        if (strlen($country) == 2)
        {
            // Probably an ISO code
            return $country;
        }
        
        $qb = org_routamc_positioning_country_dba::new_query_builder();
        $qb->add_constraint('name', '=', $country);
        $countries = $qb->execute();
        if (count($countries) > 0)
        {
            return $countries[0]->code;
        }
        
        return '';
    }
}