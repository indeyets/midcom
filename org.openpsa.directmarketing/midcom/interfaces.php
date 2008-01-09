<?php
/**
 * @package org.openpsa.directmarketing
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA direct marketing and mass mailing component
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_interface extends midcom_baseclasses_components_interface
{

    function org_openpsa_directmarketing_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.directmarketing';
        $this->_autoload_class_definitions = array('midcom_dba_classes.inc');
        $this->_autoload_files = array(
            'query.php',
            'campaign.php',
            'campaign_ruleresolver.php',
            'campaign_member.php',
            'campaign_message.php',
            'link_log.php',
            'campaign_message_receipt.php',
            'viewer.php',
            'navigation.php'
        );
        $this->_autoload_libraries = array
        (
            'org.openpsa.core',
            'org.openpsa.mail',
            'org.openpsa.helpers',
            'midcom.helper.datamanager',
            'midcom.helper.datamanager2',
            'org.openpsa.queries',
            'org.openpsa.contactwidget',
            'org.openpsa.smslib',
            'org.openpsa.qbpager',
            'midcom.services.at',
        );
    }

    function _on_initialize()
    {
        //We need the contacts person class available.
        $_MIDCOM->componentloader->load_graceful('org.openpsa.contacts');
        return true;
    }

    /**
     * Test case for the AT service
     * @param array $args handler arguments
     * @param object $handler reference to the cron_handler object calling this method.
     * @return boolean Always true
     */
    function at_test($args, &$handler)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $message = "got args:\n===\n" . sprint_r($args) . "===\n";
        $handler->print_error($message);
        debug_add($message);
        debug_pop();
        return true;
    }

    /**
     * Background message sending AT batch handler
     * @param array $args handler arguments
     * @param object $handler reference to the cron_handler object calling this method.
     * @return boolean indicating success/failure
     */
    function background_send_message($args, &$handler)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !isset($args['url_base'])
            || !isset($args['batch']))
        {
            $msg = 'url_base or batch number not set, aborting';
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }
        $_MIDCOM->auth->request_sudo();

        $batch_url = "{$args['url_base']}/{$args['batch']}/{$args['midcom_services_at_entry_object']->guid}";
        debug_add("batch_url: {$batch_url}");

        ob_start();
        $_MIDCOM->dynamic_load($batch_url);
        $output = ob_get_contents();
        ob_end_clean();

        /*
        $fp = @fopen($batch_url, 'r');
        if (!$fp)
        {
            $msg = "Error opening {$batch_url}, response: {$http_response_header[0]}";
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }
        $output = '';
        while (!feof($fp))
        {
            //Sometimes this gives warnings on "SSL: fatal protocol error"
            $output .= @fread($fp, 4096);
        }
        fclose($fp);

        if (stristr($output, 'error'))
        {
            $msg = "ERROR in batch send, output:\n==={$output}\n===\n";
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }
        */
        /*
        $debug = "batch send output:\n===\n{$output}\n===\n";
        $handler->print_error($debug);
        */

        $_MIDCOM->auth->drop_sudo();
        debug_pop();
        return true;
    }

    /**
     * For updating smart campaigns members in background
     * @param array $args handler arguments
     * @param object $handler reference to the cron_handler object calling this method.
     * @return boolean indicating success/failure
     */
    function background_update_campaign_members($args, &$handler)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!array_key_exists('campaign_guid', $args))
        {
            $msg = 'Campaign GUID not found in arguments list';
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }

        $_MIDCOM->auth->request_sudo();
        $campaign = new org_openpsa_directmarketing_campaign($args['campaign_guid']);
        if (   !is_object($campaign)
            || !$campaign->id)
        {
            $msg = "{$args['campaign_guid']} is not a valid campaign GUID";
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }

        $stat = $campaign->update_smart_campaign_members();
        if (!$stat)
        {
            $msg = 'Error while calling campaign->update_smart_campaign_members(), see error log for details';
            debug_add($msg, MIDCOM_LOG_ERROR);
            $handler->print_error($msg);
            debug_pop();
            return false;
        }

        $_MIDCOM->auth->drop_sudo();
        debug_pop();
        return true;
    }

    /**
     * The permalink servie resolver
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $campaign = false;
        $message = false;

        $campaign = new org_openpsa_directmarketing_campaign($guid);
        debug_add("campaign: ===\n" . sprint_r($campaign) . "===\n");
        if (!$campaign)
        {
            $message = new org_openpsa_directmarketing_campaign_message($guid);
            debug_add("message: ===\n" . sprint_r($message) . "===\n");
        }

        switch (true)
        {
            case is_object($campaign):
                debug_pop();
                return "campaign/{$campaign->guid}/";
                break;
            case is_object($message):
                debug_pop();
                return "message/{$message->guid}/";
                break;
        }
        debug_pop();
        return null;
    }

    function _on_watched_dba_create($post)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // TODO: Move this logic to a separate class

        // Re-fetch the post
        $post = new net_nemein_discussion_post_dba($post->id);

        // Figure out which topic the post is in
        $thread = new net_nemein_discussion_thread_dba($post->thread);
        $node = new midcom_db_topic($thread->node);
        if (!$node)
        {
            return false;
        }

        // Find out if some campaign watches the topic
        $campaigns = Array();
        $qb = new MidgardQueryBuilder('midgard_parameter');
        $qb->add_constraint('tablename', '=', 'org_openpsa_campaign');
        $qb->add_constraint('domain', '=', 'org.openpsa.directmarketing');
        $qb->add_constraint('name', '=', 'watch_discussion');
        $qb->add_constraint('value', '=', $node->guid);
        $campaign_params = @$qb->execute();
        if (   is_array($campaign_params)
            && count($campaign_params) > 0)
        {
            foreach ($campaign_params as $parameter)
            {
                $campaigns[] = new org_openpsa_directmarketing_campaign($parameter->oid);
            }
        }
        if (count($campaigns) < 1)
        {
            return false;
        }

        // Find an o.o.directmarketing node for message composition
        $directmarketing_node = midcom_helper_find_node_by_component('org.openpsa.directmarketing');
        if (!$directmarketing_node)
        {
            return false;
        }

        foreach ($campaigns as $campaign)
        {
            // Create message
            $message = new org_openpsa_directmarketing_campaign_message();
            $message->campaign = $campaign->id;
            // FIXME: Support HTML mails and other notifications too
            $message->orgOpenpsaObtype = ORG_OPENPSA_MESSAGETYPE_EMAIL_TEXT;
            $message->title = $post->subject;
            $stat = $message->create();
            if ($stat)
            {
                // Populate the Post data here
                $message = new org_openpsa_directmarketing_campaign_message($message->id);
                // FIXME: We're making awfully lot of assumptions here
                $message->parameter('midcom.helper.datamanager', 'data_post', $post->guid);
                $message->parameter('midcom.helper.datamanager', 'data_subject', "[{$campaign->title}] {$post->subject}");
                $message->parameter('midcom.helper.datamanager', 'data_content', $post->content);
                $message->parameter('midcom.helper.datamanager', 'data_from', "{$post->sendername} <{$post->senderemail}>");
                debug_add("Message created from forum post #{$post->id} \"{$post->subject}\"");

                // TODO: Now we should actually send the message
                $sending_url = $directmarketing_node[MIDCOM_NAV_RELATIVEURL]."message/{$message->guid}/send.html";

                debug_add("START SEND TO URL {$sending_url}");
                $_MIDCOM->auth->request_sudo();
                ob_start();
                $_MIDCOM->dynamic_load($sending_url);
                $output = ob_get_contents();
                ob_end_clean();
                $_MIDCOM->auth->drop_sudo();
                debug_add("END SEND");
            }
            else
            {
                debug_add("Failed to create campaign message from post, reason ".mgd_errstr(), MIDCOM_LOG_ERROR);
            }
        }
        debug_pop();
    }

    /**
     * Support for contacts person merge
     */
    function org_openpsa_contacts_duplicates_merge_person(&$person1, &$person2, $mode)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        switch($mode)
        {
            case 'all':
                break;
            case 'future':
                // DirMar does not have future references so we have nothing to transfer...
                return true;
                break;
            default:
                // Mode not implemented
                debug_add("mode {$mode} not implemented", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
                break;
        }

        // Transfer links from classes we drive
        // ** Members **
        $qb_member = org_openpsa_directmarketing_campaign_member::new_query_builder();
        $qb_member->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb_member->add_constraint('person', '=', $person2->id);
        $members = $qb_member->execute();
        if ($members === false)
        {
            // Some error with QB
            debug_add('QB Error', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Transfer memberships
        $membership_map = array();
        foreach ($members as $member)
        {
            $member->person = $person1->id;
            if (!$member->_check_duplicate_membership())
            {
                // This is a duplicate membership, delete it
                debug_add("Person #{$person1->id} is already member in campaign #{$member->campaign}, removing membership #{$member->id}", MIDCOM_LOG_INFO);
                if (!$member->delete())
                {
                    debug_add("Could not delete campaign member #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                continue;
            }
            debug_add("Transferred campaign membership #{$member->id} to person #{$person1->id} (from #{$member->person})", MIDCOM_LOG_INFO);
            if (!$member->update())
            {
                debug_add("Failed to update campaign member #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // ** Receipts **
        $qb_receipt = org_openpsa_directmarketing_campaign_message_receipt::new_query_builder();
        $qb_receipt->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb_receipt->add_constraint('person', '=', $person2->id);
        $receipts = $qb_receipt->execute();
        if ($receipts === false)
        {
            // Some error with QB
            debug_add('QB Error / receipts', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach($receipts as $receipt)
        {
            debug_add("Transferred message_receipt #{$receipt->id} to person #{$person1->id} (from #{$receipt->person})", MIDCOM_LOG_INFO);
            $receipt->person = $person1->id;
            if (!$receipt->update())
            {
                // Error updating
                debug_add("Failed to update receipt #{$receipt->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // ** Logs **
        $qb_log = org_openpsa_directmarketing_link_log::new_query_builder();
        $qb_log->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb_log->add_constraint('person', '=', $person2->id);
        $logs = $qb_log->execute();
        if ($logs === false)
        {
            // Some error with QB
            debug_add('QB Error / links', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach($logs as $log)
        {
            debug_add("Transferred link_log #{$log->id} to person #{$person1->id} (from #{$log->person})", MIDCOM_LOG_INFO);
            $log->person = $person1->id;
            if (!$log->update())
            {
                // Error updating
                debug_add("Failed to update link #{$log->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // Transfer metadata dependencies from classes that we drive
        $classes = array(
            'org_openpsa_directmarketing_campaign',
            'org_openpsa_directmarketing_campaign_member',
            'org_openpsa_directmarketing_campaign_message',
            'org_openpsa_directmarketing_campaign_message_receipt',
            'org_openpsa_directmarketing_link_log',
        );
        foreach($classes as $class)
        {
            if ($version_not_18 = true)
            {
                switch($class)
                {
                    default:
                        $metadata_fields = array(
                            'creator' => 'id',
                            'revisor' => 'id' // Though this will probably get touched on update we need to check it anyways to avoid invalid links
                        );
                        break;
                }
            }
            else
            {
                // TODO: 1.8 metadata format support
            }
            $ret = org_openpsa_contacts_duplicates_merge::person_metadata_dependencies_helper($class, $person1, $person2, $metadata_fields);
            if (!$ret)
            {
                // Failure updating metadata
                debug_add("Failed to update metadata dependencies in class {$class}, errsrtr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // All done
        return true;
    }

}
?>