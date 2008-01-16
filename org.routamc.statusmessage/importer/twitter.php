<?php
/**
 * @package org.routamc.statusmessage
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: plazes.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 */

/**
 * Importer for fetching status messages from twitter
 *
 * @package org.routamc.statusmessage
 */
class org_routamc_statusmessage_importer_twitter extends org_routamc_statusmessage_importer
{
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_statusmessage_importer_twitter()
    {
         parent::org_routamc_statusmessage_importer();
    }

    /**
     * Seek users with Plazes account settings set
     *
     * @return Array
     */
    function seek_twitter_users()
    {
        // TODO: With 1.8 we can query parameters more efficiently
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->add_constraint('domain', '=','org.routamc.statusmessage:twitter');
        $qb->add_constraint('name', '=','username');
        $qb->add_constraint('tablename', '=', 'person');
        $accounts = $qb->execute();
        if (count($accounts) > 0)
        {
            foreach ($accounts as $account_param)
            {
                $user = new midcom_db_person($account_param->oid);
                $this->get_twitter_status($user, true);
            }
        }
    }

    function _fetch_twitter_status($username, $password)
    {
        $client = new org_openpsa_httplib();
        
        // Prepare authentication information
        //$headers = array();
        //$headers[] = "Authorization: Basic " . base64_encode("{$username}:{$password}") . "\r\n";
        $twitter = $client->get('http://twitter.com/statuses/friends_timeline.json', null, $username, $password);
        if ($twitter)
        {
            $twitter_messages = json_decode($twitter);
            $messages = array();
            if ($twitter_messages)
            {
                foreach ($twitter_messages as $twitter_message)
                {
                    $person_qb = midcom_db_person::new_query_builder();
                    $person_qb->begin_group('OR');
                        // Try matching to the twitter username parameter or the local username
                        $person_qb->begin_group('AND');
                            $person_qb->add_constraint('parameter.domain', '=', 'org.routamc.statusmessage:twitter');
                            $person_qb->add_constraint('parameter.name', '=', 'username');
                            $person_qb->add_constraint('parameter.value', '=', $twitter_message->user->screen_name);
                        $person_qb->end_group();
                        $person_qb->add_constraint('username', '=', $twitter_message->user->screen_name);
                    $person_qb->end_group();

                    $persons = $person_qb->execute();
                    if (count($persons) == 0)
                    {
                        // Couldn't match this user to local user, skip
                        continue;
                    }
                    $author = $persons[0]->guid;
                    $author_id = $persons[0]->id;
                    
                    $time = explode(' ', $twitter_message->created_at);
                    $time_string = "{$time[2]} {$time[1]} {$time[5]} {$time[3]} {$time[4]}";
                
                    $messages[] = array
                    (
                        'id' => $twitter_message->id,
                        'text' =>  $twitter_message->text,
                        'published' => strtotime($time_string),
                        'author' => $author,
                        'person' => $author_id,
                        'authorname' => $twitter_message->user->name,
                    );
                }                
            }
            return $messages;
        }
        $this->error = 'STATUSMESSAGE_TWITTER_CONNECTION_NORESULTS';
        return null;
    }

    /**
     * Get plazes status for a user
     *
     * @param midcom_db_person $user Person to fetch Plazes data for
     * @param boolean $cache Whether to cache the status to a status object
     * @return Array
     */
    function get_twitter_status($user, $cache = true)
    {
        $username = $user->parameter('org.routamc.statusmessage:twitter', 'username');
        $password = $user->parameter('org.routamc.statusmessage:twitter', 'password');

        if (   $username
            && $password)
        {
            $statuses = $this->_fetch_twitter_status($username, $password);

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
            $this->error = 'STATUSMESSAGE_TWITTER_NO_USERNAME';
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
        
        $this->message->source = 'twitter';
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