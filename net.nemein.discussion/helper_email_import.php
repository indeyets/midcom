<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 4051 2006-09-12 07:32:51Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package net.nemein.discussion
 */
class net_nemein_discussion_email_importer extends midcom_baseclasses_components_purecode
{
    var $parsed = false;
    var $imported = false;
    var $topic = false;
    var $schema_name = 'email';
    var $is_duplicate = false;

    // Used only for indexing (only when called in correct MidCOM topic context)
    var $controller = false;
    var $midcom_topic = false;

    function net_nemein_discussion_email_importer()
    {
        $this->_component = 'net.nemein.discussion';
        parent::midcom_baseclasses_components_purecode();
        $_MIDCOM->load_library('org.openpsa.mail');
    }

    /**
     * Parses given raw email using org.openpsa.mail and applies rewrite rules as per configuration
     *
     * @param text $body raw email source
     * @return boolean indicating success/failure
     */
    function parse($body)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $mail = new org_openpsa_mail();
        // o.o.mail autosets these, unset to keep them from confusing us later
        unset($mail->headers['User-Agent'], $mail->headers['X-Originating-Ip']);
        $mail->body =& $body;

        if (!$mail->mime_decode())
        {
            debug_add("mime_decode returned failure, aborting parse", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // TODO: determine decode status somehow ?
        if (!$this->_rewrite($mail))
        {
            debug_add("_rewrite returned failure, aborting parse", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // PHP5-TODO: Must be copy
        $this->parsed = $mail;
        debug_pop();
        return true;
    }

    /**
     * Import the previously parsed body as post
     */
    function import($strict_parent = true, $force = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_a($this->parsed, 'org_openpsa_mail'))
        {
            debug_add("\$this->parsed is not a valid org_openpsa_mail instance", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (empty($this->topic))
        {
            debug_add("\$this->topic is empty", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $mail =& $this->parsed;
        // Check for duplicate based on message-id
        $duplicate = $this->get_post_by_message_id($mail->headers['Message-Id']);
        if (   !empty($duplicate)
            && !$force)
        {
            $this->is_duplicate = true;
            debug_add("Found duplicate post #{$duplicate->id} for message-id '{$mail->headers['Message-Id']}', aborting (hint: use force)", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        unset($duplicate);
        $parent = false;
        $post = new net_nemein_discussion_post_dba();
        $post->subject = $mail->subject;
        $post->content = $mail->body;
        debug_add('$post->content size ' . strlen($post->content) . ' bytes');
        // FIXME: when DBA stops messing about with metadata timestamps convert to Midgard core format
        $set_metadata_published = strtotime($mail->headers['Date']);
        //$set_metadata_published = gmstrftime('%Y-%m-%d %T', strtotime($mail->headers['Date']));
        $post->metadata->published = $set_metadata_published;
        if (is_numeric($post->metadata->published))
        {
            debug_add("Set \$post->metadata->published to {$post->metadata->published} (" . date('Y-m-d H:i:s', $post->metadata->published) . ")");
        }
        else
        {
            debug_add("Set \$post->metadata->published to {$post->metadata->published} (" . strtotime($post->metadata->published) . ")");
        }

        // Fetch in-reply-to message if set
        if (isset($mail->headers['In-Reply-To']))
        {
            $parent = $this->get_post_by_message_id($mail->headers['In-Reply-To']);
            if (   empty($parent)
                && $strict_parent
                && !$force)
            {
                debug_add("Could not find parent message with message-id '{$mail->headers['In-Reply-To']}', aborting (hint: use force or don't use strict_parent)", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }

        // Set author the best we can
        $author_person = false;
        $author_info = array
        (
            'email' => '',
            'fullname' => '',
            'firstname' => '',
            'lastname' => '',
        );
        switch (true)
        {
            case (preg_match("/(['\"])?(.*?)\\1?\s+<(.*?)>/", $mail->from, $from_matches)):
                // "my name" <my.email@example.com (ie standard) formatted from line (quotes optional)
                $author_info['email'] = trim($from_matches[3]);
                $author_info['fullname'] = trim($from_matches[2]);
                // unset keys we could not fill
                unset($author_info['firstname'], $author_info['firstname']);
                break;
            case (preg_match("/\s*(.*?@.*?)(\s|$)/", $mail->from, $from_matches)):
                // email address with misc junk
                $author_info['email'] = trim($from_matches[1]);
                // unset keys we could not fill
                unset($author_info['firstname'], $author_info['firstname'], $author_info['fullname']);
                break;
            default:
                debug_add("Could not parse any sensible author info from '{$mail->from}'", MIDCOM_LOG_WARN);
                // unset keys we could not fill
                unset($author_info['firstname'], $author_info['firstname'], $author_info['fullname'], $author_info['email']);
                break;
        }
        debug_print_r('Got author_info: ', $author_info);
        // TODO: Try to find person based on author_info
        $author_person = $this->find_person($author_info);
        if (!is_object($author_person))
        {
            // Could not find person, settle for what we have
            if (isset($author_info['fullname']))
            {
                $post->sendername = $author_info['fullname'];                
            }
            else
            {
                $post->sendername = str_replace('@', ' at ', $author_info['email']);
            }
            $post->senderemail = $author_info['email'];
            $post->status = $this->_config->get('new_message_status_anon');
        }
        else
        {
            debug_print_r('Got author_person: ', $author_person);
            $post->sender = $author_person->id;
            $post->sendername = $author_person->name;
            $post->senderemail = $author_person->email;
            if (!empty($author_person->username))
            {
                $post->status = $this->_config->get('new_message_status_user');
            }
            else
            {
                $post->status = $this->_config->get('new_message_status_anon');
            }
        }

        // ** Skip store for now
        /*
        debug_add("TESTING, skip store, returning true");
        debug_print_r('Post: ', $post);
        debug_pop();
        return true;
        */

        // store message
        if ($parent)
        {
            // Reply to existing post
            debug_add("parent message is #{$parent->id}");
            // Reply to old message
            $post->replyto = $parent->id;
            $post->thread = $parent->thread;
            if (!$post->create())
            {
                debug_add("Failed to create post, last error: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r('Object was: ', $post);
                debug_pop();
                return false;
            }
        }
        else
        {
            // new message, create thread as well
            $thread = $this->_create_thread_for_post($post);
            if (!$thread)
            {
                debug_add('Could not create new thread, aborting', MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $post->thread = $thread->id;
            if (!$post->create())
            {
                debug_add("Failed to create post, last error: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_print_r('Object was: ', $post);
                debug_pop();
                $thread->delete();
                return false;
            }
        }
        $post->set_parameter('midcom.helper.datamanager2', 'schema_name', $this->schema_name);
        debug_add("Created new post #{$post->id}",  MIDCOM_LOG_INFO);

        // store headers for future reference
        foreach ($mail->headers as $header => $value)
        {
            if (empty($value))
            {
                continue;
            }
            
            if (!is_string($value))
            {
                $value = serialize($value);
            }

            if (!$post->set_parameter('net.nemein.discussion.mailheaders', $header, $value))
            {
                debug_add("Could not store header '{$header}' data in parameters", MIDCOM_LOG_WARN);
                // PONDER: abort and clean up ?? (this may affect future imports adversely)
                continue;
            }
        }

        // Try to find tags in post content
        // FIXME seems not to work
        $content = $post->content;
        $_MIDCOM->componentloader->load_graceful('net.nemein.tag');
        if (class_exists('net_nemein_tag_handler'))
        {
            $content_tags = net_nemein_tag_handler::separate_machine_tags_in_content($content);
            if (   is_array($content_tags)
                && !empty($content_tags))
            {
                net_nemein_tag_handler::tag_object($post, $content_tags);
            }
        }

        // TODO: store attachments ??

        $call_update = false;
        // Doublecheck published (for some reason sometimes at some point in create process this is reset to current time)
        if ($post->metadata->published != $set_metadata_published)
        {
            $post->metadata->published = $set_metadata_published;
            if (is_numeric($post->metadata->published))
            {
                debug_add("RESET \$post->metadata->published to {$post->metadata->published} (" . date('Y-m-d H:i:s', $post->metadata->published) . ")");
            }
            else
            {
                debug_add("RESET \$post->metadata->published to {$post->metadata->published} (" . strtotime($post->metadata->published) . ")");
            }
            $call_update = true;
        }

        // Update post if necessary after the doublechecks
        if ($call_update)
        {
            $post->update();
        }

        // Index the post ??
        if (   $this->controller
            && $this->midcom_topic
            && is_callable(array('net_nemein_discussion_viewer', 'index')))
        {
            $indexer =& $_MIDCOM->get_service('indexer');
            net_nemein_discussion_viewer::index($this->_controller->datamanager, $indexer, $this->midcom_topic);
        }

        if ($this->_config->get('autoapprove'))
        {
            $meta = $post->get_metadata();
            $meta->approve();

            $meta = $thread->get_metadata();
            $meta->approve();
        }

        $this->_imported = $post;
        debug_pop();
        return true;
    }

    function find_person($person_info)
    {
        // Email is a good match to start with
        if (isset($person_info['email']))
        {
            $user = $_MIDCOM->auth->get_user_by_email(trim($person_info['email']));
            if (is_array($user))
            {
                // Multiple matches, use first
                $person = $user[0]->get_storage();
                return $person;
            }
            elseif ($user)
            {
                $person = $user->get_storage();
                return $person;
            }
        }

        // Normalize the fullname (no start/end whitespace, inline whitespace normalized to single spaces)
        if (isset($person_info['fullname']))
        {
            $person_info['fullname'] = trim(preg_replace('/\s+/', ' ', $person_info['fullname']));
        }

        // Use fullname as username if not specifically set
        if (   !isset($person_info['username'])
            && isset($person_info['fullname']))
        {
            $person_info['username'] = strtolower($person_info['fullname']);
        }

        // Try matching username
        if (isset($person_info['username']))
        {
            $person_qb = midcom_db_person::new_query_builder();
            $person_qb->add_constraint('username', '=', $person_info['username']);
            $persons = $person_qb->execute();
            if (   is_array($persons)
                && count($persons) > 0)
            {
                return $persons[0];
            }
        }

        // Try expanding fullname to separate last and first name
        if (   (   !isset($person_info['firstname'])
                && !isset($person_info['lastname'])
                )
            && isset($person_info['fullname']))
        {
            $person_info['firstname'] = '';
            $person_info['lastname'] = '';
            // Use strict check even though string starting with delimiter is unlikely to give us good results
            if (strpos($person_info['fullname'], ',') !== false)
            {
                // contains comma, most likely format is: "lastname, firstname"
                list($person_info['lastname'], $person_info['firstname']) = explode(',', $person_info['fullname'], 2);
            }
            elseif (strpos($person_info['fullname'], ' ') !== false)
            {
                // does not contain comma but contains space, most like format is: "firstname lastname"
                list($person_info['firstname'], $person_info['lastname']) = explode(' ', $person_info['fullname'], 2);
            }
        }

        // Try with last/first name
        if (   isset($person_info['firstname'])
            && isset($person_info['lastname']))
        {
            $person_qb = midcom_db_person::new_query_builder();
            $person_qb->add_constraint('firstname', '=', $person_info['firstname']);
            $person_qb->add_constraint('lastname', '=', $person_info['lastname']);
            $persons = $person_qb->execute();
            if (   is_array($persons)
                && count($persons) > 0)
            {
                return $persons[0];
            }
        }

        // Lastly try having fullname as first or last name
        if (isset($person_info['fullname']))
        {
            $person_qb = midcom_db_person::new_query_builder();
            $person_qb->begin_group('OR');
                $person_qb->add_constraint('firstname', '=', $person_info['fullname']);
                $person_qb->add_constraint('lastname', '=', $person_info['fullname']);
            $person_qb->end_group();
            $persons = $person_qb->execute();
            if (   is_array($persons)
                && count($persons) > 0)
            {
                return $persons[0];
            }
        }

        return false;
    }

    function _create_thread_for_post(&$post)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $thread = new net_nemein_discussion_thread_dba();
        $thread->node = (int)$this->topic;
        $thread->title = $post->subject;
        $thread->name = midcom_generate_urlname_from_string($post->subject);
        // In fact these get updated when the post is created soon after
        $thread->posts = 1;
        $thread->firstpost = $post->id;
        $thread->latestpost = $post->id;
        // Try to figure out the correct latestposttime to use
        if (is_numeric($post->metadata->published))
        {
            $thread->latestposttime = $post->metadata->published;
        }
        else
        {
            // The problem here is that the timestamp is in UTC
            $thread->latestposttime = strtotime($post->metadata->published);
        }
        // Make sure name is unique
        $i = 1;
        while (true)
        {
            if ($i > 1000)
            {
                debug_add('Duplicate name retry limit exceeded', MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $qb = net_nemein_discussion_thread_dba::new_query_builder();
            $qb->add_constraint('node', '=', $thread->node);
            $qb->add_constraint('name', '=', $thread->name);
            $results = $qb->execute();
            if ($results === false)
            {
                debug_add('QB failure when searching for duplicate names, aborting', MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            if (empty($results))
            {
                break;
            }
            $thread->name = midcom_generate_urlname_from_string($post->subject) . sprintf("-%03d",$i);
            $i++;
        }

        if (!$thread->create())
        {
            debug_add('Failed to create thread, last error: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        debug_pop();
        return $thread;
    }

    /**
     * Fetch a post based on message-id header
     *
     * @param string $message_id message-id to search for
     * @return object net_nemein_discussion_post_dba (or false on failure)
     */
    function get_post_by_message_id($message_id)
    {
        if (empty($message_id))
        {
            return false;
        }
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->add_constraint('domain', '=', 'net.nemein.discussion.mailheaders');
        $qb->add_constraint('name', '=', 'Message-Id');
        $qb->add_constraint('value', '=', (string)$message_id);
        $results = $qb->execute();
        if (empty($results))
        {
            return false;
        }
        
        foreach ($results as $result)
        {
            try
            {
                $post = new net_nemein_discussion_post($result->parentguid);
                if ($post->guid)
                {
                    return $post;
                }
            }
            catch (midgard_error_exception $e)
            {
                continue;
            }
        }

        return false;
    }

    /**
     * Rewrites org_openpsa_mail object properties according to configuration
     *
     * @param object &$mail reference to org_openpsa_mail object
     * @return boolean indicating success/failure
     */
    function _rewrite(&$mail)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_a($mail, 'org_openpsa_mail'))
        {
            debug_add("\$mail is not a valid org_openpsa_mail instance", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $rewrites = $this->_config->get('email_import_rewrites');
        $subj_prefix = trim($this->_config->get('email_out_subject_prefix'));
        if (!empty($subj_prefix))
        {
            if (!isset($rewrites['subject']))
            {
                $rewrites['subject'] = array();
            }
            // Add removal of the subject prefix to rewrites
            $rewrites['subject'][] = array
            (
                'search' => '/' . str_replace(array('(', ')', '[', ']', '/'), array('\(', '\)', '\[', '\]', '\/'), $subj_prefix) . '\s*/',
                'replace' => '',
            );
        }
        foreach ($rewrites as $property => $sr_array)
        {
            foreach ($sr_array as $sr)
            {
                debug_add("property: {$property}, search: '{$sr['search']}', replace: '{$sr['replace']}'");
                switch (true)
                {
                    case (isset($mail->$property)):
                        debug_add("\$mail->{$property} before: {$mail->$property}");
                        $mail->$property = preg_replace($sr['search'], $sr['replace'], $mail->$property);
                        debug_add("\$mail->{$property} after: {$mail->$property}");
                        break;
                    case (isset($mail->headers[$property])):
                        debug_add("\$mail->headers[{$property}] before: {$mail->headers[$property]}");
                        $mail->headers[$property] = preg_replace($sr['search'], $sr['replace'], $mail->headers[$property]);
                        debug_add("\$mail->headers[{$property}] after: {$mail->headers[$property]}");
                        break;
                    default:
                        debug_add("No property/header named '{$property}' found in \$mail (NOTE: needs exact match)", MIDCOM_LOG_INFO);
                        break;
                }
            }
        }
        debug_pop();
        return true;
    }

}

?>