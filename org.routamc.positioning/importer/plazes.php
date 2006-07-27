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

    function _fetch_plazes_position($plazes_username, $plazes_password)
    {
        $plazes_password_md5 = md5("PLAZES{$plazes_password}");
        
        // These are the required XML-RPC parameters
		$params = array
		(
			new XML_RPC_Value($plazes_username, 'string'),
			new XML_RPC_Value($plazes_password_md5, 'string')
        );
        
		// Name of the XML-RPC method to be called
		$msg = new XML_RPC_Message('plazes.whereami', $params);
		
		// URI of the XML-RPC stub
		$cli = new XML_RPC_Client('/xmlrpc/whereami.php', 'http://www.plazes.com');
		$resp = $cli->send($msg);

        if (!$resp) 
        {
        	$this->error = 'POSITIONING_PLAZES_CONNECTION_FAILED';
        	return null;
        }

		if (!$resp->faultCode()) 
		{
			$results = $resp->value();
			
			if (isset($results))
            {
				$plaze_lat = $results->structmem('plazelat');
				$plaze_lat = $plaze_lat->scalarval();
				$plaze_lon = $results->structmem('plazelon');
                $plaze_lon = $plaze_lon->scalarval();
				/*
				$plaze_url = $results->structmem('plazeurl')->scalarval();
				$plaze_name = $results->structmem('plazename')->scalarval();
				$plaze_username = $results->structmem('username')->scalarval();
				$plaze_state = $results->structmem('state')->scalarval();
				*/
				$plaze_country = $results->structmem('plazecountry');
                $plaze_country = $plaze_country->scalarval();
				$plaze_city = $results->structmem('plazecity');
                $plaze_city = $plaze_city->scalarval();
                
				$position = array
				(
					'latitude'	=> $plaze_lat,
					'longitude'	=> $plaze_lon,
					'country'	=> $plaze_country,
					'city'		=> $plaze_city,
				);
				return $position;
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
            return null;
		}
    }
    
    /**
     * Get plazes location for an user
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
            $position = $this->_fetch_plazes_position($plazes_username, $plazes_password);
            
            if (is_null($position))
            {
                return null;
            }
            
            if ($cache)
            {
                $this->import($position, $user->id);    
            }
            
            return $position;
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
        
        $this->log->date = time();
        $this->log->latitude = (float) $position['latitude'];
        $this->log->longitude = (float) $position['longitude'];
        $this->log->altitude = 0;
        $this->log->accuracy = ORG_ROUTAMC_POSITIONING_ACCURACY_PLAZES;   

        // Try to create the entry
        $stat = $this->log->create();
        $this->error = mgd_errstr();
        return $stat;
    }
}
