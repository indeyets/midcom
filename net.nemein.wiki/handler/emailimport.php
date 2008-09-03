<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * E-Mail import handler
 *
 * @package net.nemein.wiki
 */
class net_nemein_wiki_handler_emailimport extends midcom_baseclasses_components_handler
{

    function net_nemein_wiki_handler_emailimport()
    {
        parent::__construct();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_emailimport($handler_id, $args, &$data)
    {
        //Content-Type
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type('text/plain');

        //Load o.o.mail && relatedto
        $_MIDCOM->load_library('org.openpsa.mail');

        //Make sure we have the components we use and the Mail_mimeDecode package
        if (!class_exists('org_openpsa_mail'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'library org.openpsa.mail could not be loaded.');
            // This will exit.
        }

        $_MIDCOM->load_library('org.openpsa.relatedto');
        if (!class_exists('org_openpsa_relatedto_handler'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'library org.openpsa.relatedto could not be loaded.');
            // This will exit.
        }

        $decoder = new org_openpsa_mail();

        if (!class_exists('Mail_mimeDecode'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Cannot decode attachments, aborting.');
            // This will exit.
        }

        //Make sure the message_source is POSTed
        if (   !array_key_exists('message_source', $_POST)
            || empty($_POST['message_source']))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, '_POST[\'message_source\'] not present or empty.');
            // This will exit.
        }
        debug_push_class(__CLASS__, __FUNCTION__);

        $decoder = new org_openpsa_mail();
        $decoder->body = $_POST['message_source'];
        $decoder->mime_decode();

        /*
        echo "DEBUG: decoder->body: \n===\n" . sprint_r($decoder->body) . "===\n";
        echo "DEBUG: decoder->html_body: \n===\n" . sprint_r($decoder->html_body) . "===\n";
        echo "DEBUG: decoder->headers: \n===\n" . sprint_r($decoder->headers) . "===\n";
        */

        //Parse email addresses
        $regex = '/<?([a-zA-Z0-9_.-]+?@[a-zA-Z0-9_.-]+)>?[ ,]?/';
        $emails = array();
        if (preg_match_all($regex, $decoder->headers['To'], $matches_to))
        {
            foreach ($matches_to[1] as $email)
            {
                //Each address only once
                $emails[$email] = $email;
            }
        }
        if (preg_match_all($regex, $decoder->headers['Cc'], $matches_cc))
        {
            foreach ($matches_cc[1] as $email)
            {
                //Each address only once
                $emails[$email] = $email;
            }
        }
        $from = false;
        if (preg_match_all($regex, $decoder->headers['From'], $matches_from))
        {
            foreach ($matches_from[1] as $email)
            {
                //Each address only once
                $emails[$email] = $email;
                //It's unlikely that we'd get multiple matches in From, but we use the latest
                $from = $email;
            }
        }

        //echo "DEBUG: emails: \n===\n" . sprint_r($emails) . "===\n";

        $_MIDCOM->auth->request_sudo();
        //TODO: Create wikinote
        $wikipage = new net_nemein_wiki_wikipage();
        $wikipage->topic = $this->_topic->id;
        //PONDER: add from, to & subject into the body ??
        $wikipage->content = $decoder->body;
        $title_format = $this->_config->get('emailimport_title_format');
        $wikipage->title = sprintf($title_format, $decoder->subject, $from, strftime('%x', time()));
        //Check for duplicate title(s)
        $qb = net_nemein_wiki_wikipage::new_query_builder();
        $qb->add_constraint('topic', '=', $wikipage->topic);
        $qb->add_constraint('title', 'LIKE', $wikipage->title . '%');
        $results = $qb->execute_unchecked();
        if (($found = count($results)) > 0)
        {
            foreach ($results as $foundpage)
            {
                if ($foundpage->content == $wikipage->content)
                {
                    //Content exact duplicate, abort import
                    debug_add("duplicate content with page '{$wikipage->title}' content: \n===\n{$wikipage->content}\n===\n");
                    debug_pop();
                    $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Duplicate content with an existing page with similar title, aborting import.');
                    // This will exit.
                }
            }
            //In theory this should be recursive but we'll leave it at this for now
            $wikipage->title .= ' ' . ($found+1);
        }

        //Figure out author
        $author = $this->emailimport_find_person($from);
        $wikipage->author = $author->id;
        if (!$wikipage->author)
        {
            //Default to first user in the sitegroup
            $qb = midcom_db_person::new_query_builder();
            $qb->add_constraint('username', '<>', '');
            $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
            $results = $qb->execute_unchecked();
            if (empty($results))
            {
                //No users found
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Cannot set any author for the wikipage');
                // This will exit.
            }
            $wikipage->author = $results[0]->id;
        }

        $stat = $wikipage->create();
        if (!$stat)
        {
            //Could not create article
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'wikipage->create returned failure, errstr: ' . mgd_errstr());
            // This will exit.
        }
        //Mark as email
        $wikipage->parameter('net.nemein.wiki:emailimport', 'is_email', time());

        $embeds_added = false;
        $attachments_added = false;
        foreach ($decoder->attachments as $att)
        {
            debug_add("processing attachment {$att['name']}");

            $attobj = $wikipage->create_attachment($att['name'], $att['name'], $att['mimetype']);
            if (!$attobj)
            {
                //Could not create attachment
                debug_add("Could not create attachment '{$att['name']}', errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                continue;
            }
            $fp = @mgd_open_attachment($attobj->id, 'w');
            if (!$fp)
            {
                //Could not open for writing, clean up and continue
                debug_add("Could not open attachment {$attobj->guid} for writing, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                $attobj->delete();
                continue;
            }
            if (!fwrite($fp, $att['content'], strlen($att['content'])))
            {
                //Could not write, clean up and continue
                debug_add("Error when writing attachment {$attobj->guid}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                fclose($fp);
                $attobj->delete();
                continue;
            }
            fclose($fp);

            if (   isset($att['part'])
                && isset($att['part']->headers)
                && isset($att['part']->headers['content-id']))
            {
                //Attachment is embed, add tag to end of note
                if (!$embeds_added)
                {
                    $wikipage->content .= "\n\n";
                    $embeds_added = true;
                }
                $wikipage->content .= "![{$attobj->title}]({$_MIDGARD['self']}midcom-serveattachmentguid-{$attobj->guid}/{$attobj->name})\n\n";
            }
            else
            {
                //Add normal attachments as links to end of note
                if (!$attachments_added)
                {
                    //We hope the client handles these so that embeds come first and attachments then so we can avoid double pass over this array
                    $wikipage->content .= "\n\n";
                    $attachments_added = true;
                }
                $wikipage->content .= "[{$attobj->title}]({$_MIDGARD['self']}midcom-serveattachmentguid-{$attobj->guid}/{$attobj->name}), ";
            }
        }
        if (   $embeds_added
            || $attachments_added)
        {
            $wikipage->update();
        }

        //Create related_to links to persons based on the email addresses
        reset ($emails);
        foreach ($emails as $email)
        {
            $wikipage->parameter('net.nemein.wiki:emailimport_recipients', $email, time());
            debug_add("Processing email address {$email} for related-to links");
            $person = $this->emailimport_find_person($email);
            if (!$person)
            {
                $group = $this->emailimport_find_group($email);
                if (!$group)
                {
                    debug_add("Could not find person or group for email {$email}, cannot link, storing email for future reference", MIDCOM_LOG_WARN);
                    $wikipage->parameter('net.nemein.wiki:emailimport_notlinked', $email, time());
                    continue;
                }
                /* DEPRECATED in favour of the org.openpsa.relatedto
                $stat = $wikipage->parameter('net.nemein.wiki:related_to', $group->guid, "midcom_db_group:{$group->guid}");
                */
                $stat = org_openpsa_relatedto_handler::create_relatedto($wikipage, 'net.nemein.wiki', $group, 'org.openpsa.contacts', ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED);
                if (!$stat)
                {
                    debug_add("Could not link to group {$group->guid}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                }
                else
                {
                    $stat->fromExtra = "email:{$email};";
                    $stat->update();
                    debug_add("Linked to group {$group->guid}");
                }
                continue;
            }
            /* DEPRECATED in favour of the org.openpsa.relatedto
            $stat = $wikipage->parameter('net.nemein.wiki:related_to', $person->guid, "midcom_db_person:{$person->guid}");
            */
            $stat = org_openpsa_relatedto_handler::create_relatedto($wikipage, 'net.nemein.wiki', $person, 'org.openpsa.contacts', ORG_OPENPSA_RELATEDTO_STATUS_CONFIRMED);
            if (!$stat)
            {
                debug_add("Could not link to person {$person->guid}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            }
            else
            {
                $stat->fromExtra = "email:{$email};";
                $stat->update();
                debug_add("Linked to person {$person->guid}");
            }
            //Find persons suspected relations and create links
            $link_def = new org_openpsa_relatedto_relatedto();
            $link_def->fromComponent = 'net.nemein.wiki';
            $link_def->fromGuid = $wikipage->guid;
            $link_def->fromClass = get_class($wikipage);
            $link_def->status = ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED;
            $possible_links = org_openpsa_relatedto_suspect::find_links_object($person, $link_def);
            foreach ($possible_links as $linkdata)
            {
                switch(true)
                {
                    //Any rules for skipping save ??
                    /* obviously dummy rule
                    case (!is_object($linkdata['link'])):
                        //switch is considered a loop-statement in PHP, we need to continue two levels
                        continue 2;
                    */
                    default:
                        $linkdata['link']->create();
                        break;
                }
            }
        }

        //echo "DEBUG: wikipage (title format '{$title_format}'): \n===\n" . sprint_r($wikipage) . "===\n";

        //Give us output from MDA
        //echo "ERROR: just debugging\n";

        $_MIDCOM->auth->drop_sudo();
        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_emailimport($handler_id, &$data)
    {
        //All done
        echo "OK\n";
    }

    function emailimport_find_person($email, $prefer_user = true)
    {
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('email', '=', $email);
        $results = $qb->execute();
        if (empty($results))
        {
            return false;
        }
        if (!$prefer_user)
        {
            return $results[0];
        }
        foreach ($results as $person)
        {
            if (!empty($person->username))
            {
                return $person;
            }
        }
        return $person;
    }

    function emailimport_find_group($email)
    {
        //TODO: find possible groups based on the persons email
        $qb = midcom_db_group::new_query_builder();
        $qb->add_constraint('email', '=', $email);
        $results = $qb->execute();
        if (!empty($results))
        {
            //Exact matche(s) found, return first
            return $results[0];
        }
        list ($user, $domain) = explode('@', $email, 2);
        $qb = midcom_db_group::new_query_builder();
        $qb->add_constraint('email', 'LIKE', "%@{$domain}");
        $results = $qb->execute();
        if (empty($results))
        {
            return false;
        }
        //PONDER: What to return in case of multiple matches ?, now we always return first
        return $results[0];
        /*
        foreach ($results as $group)
        {
            debug_add("Found group #{$group->id} ({$group->official})");
        }
        */

        return false;
    }

}