<?php
/**
 * @package org.routamc.statusmessage
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: plazes.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 */

/**
 * Importer for fetching status messages from jaiku
 *
 * @package org.routamc.statusmessage
 */
class org_routamc_statusmessage_importer_jaiku extends org_routamc_statusmessage_importer
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_statusmessage_importer_jaiku()
    {
         parent::org_routamc_statusmessage_importer();
    }

    /**
     * Seek users with Plazes account settings set
     *
     * @return Array
     */
    function seek_jaiku_users()
    {
        // TODO: With 1.8 we can query parameters more efficiently
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->add_constraint('domain', '=','org.routamc.statusmessage:jaiku');
        $qb->add_constraint('name', '=','username');
        $qb->add_constraint('tablename', '=', 'person');
        $accounts = $qb->execute();
        if (count($accounts) > 0)
        {
            foreach ($accounts as $account_param)
            {
                $user = new midcom_db_person($account_param->oid);
                $this->get_jaiku_status($user, true);
            }
        }
    }
    
    function _parse_w3cdtf($date_str) 
    {   
        # regex to match wc3dtf
        $pat = "/(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):((\d{2}))?(?:([-+])(\d{2}):?(\d{2})|(Z))/";
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

    function _fetch_jaiku_status($person, $username, $personal_key)
    {
        $client = new org_openpsa_httplib();
        
        $jaiku = $client->get("http://{$username}.jaiku.com/feed/json?user={$username}&personal_key={$personal_key}");
        if ($jaiku)
        {
            $jaiku_messages = json_decode($jaiku);
            $messages = array();
            if (   $jaiku_messages
                && !empty($jaiku_messages->stream))
            {

                foreach ($jaiku_messages->stream as $jaiku_message)
                {
                    if (!strstr($jaiku_message->url, 'jaiku.com'))
                    {
                        // Skip messages imported from elsewhere to Jaiku, they're better aggregated to their respective components
                        continue;
                    }
                                        
                    $messages[] = array
                    (
                        'id' => sprintf('%s', $jaiku_message->id),
                        'text' => sprintf('%s', $jaiku_message->title),
                        'published' => $this->_parse_w3cdtf(str_replace(' GMT', 'Z', $jaiku_message->created_at)),
                        'author' => $person->guid,
                        'person' => $person->id,
                        'authorname' => $person->name,
                    );
                    // TODO: Add icon support
                }
            }

            return $messages;
        }
        $this->error = 'STATUSMESSAGE_JAIKU_CONNECTION_NORESULTS';
        return null;
    }

    /**
     * Get plazes status for a user
     *
     * @param midcom_db_person $user Person to fetch Plazes data for
     * @param boolean $cache Whether to cache the status to a status object
     * @return Array
     */
    function get_jaiku_status($user, $cache = true)
    {
        $username = $user->parameter('org.routamc.statusmessage:jaiku', 'username');
        $personal_key = $user->parameter('org.routamc.statusmessage:jaiku', 'personal_key');

        if (   $username
            && $personal_key)
        {
            $statuses = $this->_fetch_jaiku_status($user, $username, $personal_key);

            if (is_null($statuses))
            {
                return null;
            }

            if ($cache)
            {
                $cached_statuses = array();
                
                foreach ($statuses as $status)
                {
                    if ($this->import($status))
                    {
                        $cached_statuses[] = $status;
                    }
                }
                
                $statuses = $cached_statuses;
            }

            return $statuses;
        }
        else
        {
            $this->error = 'STATUSMESSAGE_JAIKU_NO_USERNAME';
        }

        return null;
    }

    /**
     * Import status entry. The entries are associative arrays containing
     * all of the following keys:
     *
     * - id
     * - text
     * - author
     *
     * @param Array $status Log entry in Array format specific to importer
     * @param integer $person_id ID of the person to import statuss for
     * @return boolean Indicating success.
     */
    function import($status)
    {
        // Check for duplicates first
        if ($status['id'])
        {
            $qb = org_routamc_statusmessage_message_dba::new_query_builder();
            $qb->add_constraint('externalid', '=', (string) $status['id']);
            $existing = $qb->execute();
            if (count($existing) > 0)
            {
                return false;
            }
        }
        
        $this->message = new org_routamc_statusmessage_message_dba();
        $this->message->status = $status['text'];
        $this->message->person = $status['person'];
        
        $this->message->source = 'jaiku';
        $this->message->externalid = $status['id'];

        // Try to create the entry
        $stat = $this->message->create();
        $this->error = mgd_errstr();
        
        if($stat)
        {
            $metadata = midcom_helper_metadata::retrieve($this->message);
            $metadata->set('authors', $status['author']);
            $metadata->set('published', $status['published']);
        }
        
        return $stat;
    }
}