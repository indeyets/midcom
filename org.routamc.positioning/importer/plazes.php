<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 *
 * Based on PlazesWhereAmIPhp by
 * @author Peter Rukavina <peter@rukavina.net>
 * @author Olle Jonsson <olle@olleolleolle.dk>
 * @copyright Reinvented Inc., 2005
 * @license http://creativecommons.org/licenses/by-sa/2.0/ca
 */

// PEAR XML_RPC package
require_once 'XML/RPC.php';

/**
 * Importer for fetching position data for Plazes users
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_importer_plazes extends org_routamc_positioning_importer
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_positioning_importer_plazes()
    {
         parent::org_routamc_positioning_importer();
    }

    /**
     * Seek users with Plazes account settings set
     *
     * @return Array
     */
    function seek_plazes_users()
    {
        // TODO: With 1.8 we can query parameters more efficiently
        $qb = new MidgardQueryBuilder('midgard_parameter');
        $qb->add_constraint('domain', '=','org.routamc.positioning:plazes');
        $qb->add_constraint('name', '=','username');
        $qb->add_constraint('tablename', '=', 'person');
        $accounts = $qb->execute();
        if (count($accounts) > 0)
        {
            foreach ($accounts as $account_param)
            {
                $user = new midcom_db_person($account_param->oid);
                $this->get_plazes_location($user, true);
            }
        }
    }
    
    function _prepare_plazes_params($plazes_username, $plazes_password)
    {
        $plazes_password_md5 = md5("PLAZES{$plazes_password}");
        
        // These are the required XML-RPC parameters
        $params = array
        (
            new XML_RPC_Value($this->_config->get('plazes_developer_key'), 'string'),
            new XML_RPC_Value($plazes_username, 'string'),
            new XML_RPC_Value($plazes_password_md5, 'string')
        );
        
        return $params;
    }

    function _parse_w3cdtf($date_str) 
    {
        
        # regex to match wc3dtf
        $pat = "/(\d{4})(\d{2})(\d{2})T(\d{2}):(\d{2}):((\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))/";
        
        if ( preg_match( $pat, $date_str, $match ) ) 
        {
            list( $year, $month, $day, $hours, $minutes, $seconds) = 
                array( $match[1], $match[2], $match[3], $match[4], $match[5], $match[6]);
            
            # calc epoch for current date assuming GMT
            $epoch = gmmktime( $hours, $minutes, $seconds, $month, $day, $year);
            
            $offset = 0;
            if ( $match[10] == 'Z' ) 
            {
                # zulu time, aka GMT
            }
            else 
            {
                $tz_mod = $match[8];
                $tz_hour = $match[9];
                $tz_min = $match[10];
                
                # zero out the variables
                if (!$tz_hour)
                {
                    $tz_hour = 0;
                }
                if (!$tz_min)
                {
                    $tz_min = 0;
                }
            
                $offset_secs = (($tz_hour * 60) + $tz_min) * 60;
                
                # is timezone ahead of GMT?  then subtract offset
                #
                if ( $tz_mod == '+' ) 
                {
                    $offset_secs = $offset_secs * -1;
                }
                
                $offset = $offset_secs;
            }
            $epoch = $epoch + $offset;
            return $epoch;
        }
        else 
        {
            return -1;
        }
    }

    function _fetch_plazes_positions($plazes_username, $plazes_password, $days = 0)
    {
        $positions = array();
    
        $params = $this->_prepare_plazes_params($plazes_username, $plazes_password);
        $params[] = new XML_RPC_Value($days, 'int');

        // Name of the XML-RPC method to be called
        $msg = new XML_RPC_Message('user.trazes', $params);

        // URI of the XML-RPC stub
        $cli = new XML_RPC_Client('/api/plazes/xmlrpc', 'http://beta.plazes.com');
        $resp = @$cli->send($msg);

        if (   !$resp
            || !is_object($resp)
            || !method_exists($resp, 'faultCode'))
        {
            $this->error = 'POSITIONING_PLAZES_CONNECTION_FAILED';
            return null;
        }

        if (!$resp->faultCode())
        {
            $results = $resp->value();

            $trazes = @XML_RPC_decode($results);
            
            // Quick-and-dirty timezone handling since Plazes doesn't return timezone information like they should
            // http://wwp.greenwichmeantime.com/time-zone/rules/eu.htm
            $month = (int) date('m');
            if (   $month < 4
                || $month > 10)
            {
                // Plazes is in CET
                $timezone = '+0100';
            }
            else
            {
                // Plazes is in CEST
                $timezone = '+0200';
            }
            
            if (count($trazes) > 0)
            {
                foreach ($trazes as $traze)
                {
                    @$positions[] = array
                    (
                        'plaze'       => $traze['plaze']['key'],
                        'latitude'    => $traze['plaze']['latitude'],
                        'longitude'   => $traze['plaze']['longitude'],
                        'country'     => $traze['plaze']['country'],
                        'city'        => $traze['plaze']['city'],
                        'date'        => $this->_parse_w3cdtf("{$traze['start']}{$timezone}"),
                    );
                }
                return $positions;
            }
            else
            {
                $this->error = 'POSITIONING_PLAZES_CONNECTION_NORESULTS';
                return null;
            }
        }
        else
        {
            $this->error = 'POSITIONING_PLAZES_FAULT_' . $resp->faultCode();
            $this->error_string = $resp->faultString();
            return null;
        }
    }

    /**
     * Get plazes location for a user
     *
     * @param midcom_db_person $user Person to fetch Plazes data for
     * @param boolean $cache Whether to cache the position to a log object
     * @return Array
     */
    function get_plazes_location($user, $cache = true)
    {
        $plazes_username = $user->parameter('org.routamc.positioning:plazes', 'username');
        $plazes_password = $user->parameter('org.routamc.positioning:plazes', 'password');

        if (   $plazes_username
            && $plazes_password)
        {
            $positions = $this->_fetch_plazes_positions($plazes_username, $plazes_password);

            if (   is_null($positions)
                && !is_array($positions))
            {
                return null;
            }

            if ($cache)
            {
                foreach ($positions as $position)
                {
                    $this->import($position, $user->id);
                }
            }

            return $positions[0];
        }
        else
        {
            $this->error = 'POSITIONING_PLAZES_NO_ACCOUNT';
        }

        return null;
    }

    /**
     * Import plazes log entry. The entries are associative arrays containing
     * all of the following keys:
     *
     * - latitude
     * - longitude
     *
     * @param Array $log Log entry in Array format specific to importer
     * @param integer $person_id ID of the person to import logs for
     * @return bool Indicating success.
     */
    function import($position, $person_id)
    {
        $this->log = new org_routamc_positioning_log_dba();
        $this->log->importer = 'plazes';
        $this->log->person = $person_id;

        $this->log->date = (int) $position['date'];
        $this->log->latitude = (float) $position['latitude'];
        $this->log->longitude = (float) $position['longitude'];
        $this->log->altitude = 0;
        $this->log->accuracy = ORG_ROUTAMC_POSITIONING_ACCURACY_PLAZES;

        // Try to create the entry
        $stat = $this->log->create();
        
        $this->log->parameter('org.routamc.positioning:plazes', 'plaze_key', $position['plaze']);
        
        $this->error = mgd_errstr();
        return $stat;
    }
}