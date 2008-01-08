<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: email.php 5434 2007-03-02 16:32:35Z rambo $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * E-Mail import handler. This uses the OpenPsa 2 email importer MDA system. Emails are imported into blog,
 * with a possible attached image getting stored using 'image' type in schema if available.
 *
 * @package org.routamc.photostream
 */
class org_routamc_photostream_handler_api_email extends midcom_baseclasses_components_handler
{
    /**
     * The photo to operate on
     *
     * @var org_routamc_photostream_photo_dba
     * @access private
     */
    var $_photo;

    /**
     * Email importer
     *
     * @var org_openpsa_mail
     * @access private
     */
    var $_decoder;

    function org_routamc_photostream_handler_api_email()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function _create_photo($title)
    {
        $photographer = $this->_find_email_person($this->_request_data['from']);
        if (!$photographer)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'photographer not found');
        }

        $photographer_user = $_MIDCOM->auth->get_user($photographer->guid);
        if (!$this->_topic->can_do('midgard:create', $photographer_user))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'User doesn\'t have posting privileges');
        }

        $this->_photo = new org_routamc_photostream_photo_dba();
        $this->_photo->node = $this->_topic->id;
        $this->_photo->title = $title;

        //Figure out photographer
        $this->_photo->photographer = $photographer->id;
        if (!$this->_photo->photographer)
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
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Cannot set any photographer for the photo');
                // This will exit.
            }
            $this->_photo->photographer = $results[0]->id;
        }

        if (! $this->_photo->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_photo);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new photo, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        $this->_photo->parameter('midcom.helper.datamanager2', 'schema_name', $this->_config->get('api_email_schema'));
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Created photo {$this->_photo->guid}");
        debug_pop();

        return true;
    }

    /**
     * Internal helper, loads the datamanager for the current photo. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_photo))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for photo {$this->_photo->id}.");
            // This will exit.
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_import($handler_id, $args, &$data)
    {
        if (!$this->_config->get('api_email_enable'))
        {
            return false;
        }

        //Content-Type
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type('text/plain');

        // Parse email
        $this->_decode_email();
        $this->_parse_email_persons();
        $data['tags_field'] = false;
        foreach ($this->_request_data['schemadb'][$this->_config->get('api_email_schema')]->fields as $name => $field)
        {
            // FIXME: use datamanager->types and check is_a($type, 'midcom_helper_datamanager2_type_photo')
            if ($field['type'] == 'photo')
            {
                $this->_request_data['photo_field'] = $name;
            }
            if ($field['type'] == 'tags')
            {
                $data['tags_field'] = $name;
            }
        }

        $_MIDCOM->auth->request_sudo('org.routamc.photostream');

        // Create photo
        $this->_create_photo($this->_decoder->subject);

        // Load the photo to DM2
        $this->_load_datamanager();

        // Try to find tags in email content
        $content = $this->_decoder->body;
        $content_tags = $this->_config->get('api_email_default_tags') . ' ';
        $_MIDCOM->componentloader->load_graceful('net.nemein.tag');
        if (class_exists('net_nemein_tag_handler'))
        {
            // unconditionally tag
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("content before machine tag separation\n===\n{$content}\n===\n");
            $content_tags .= net_nemein_tag_handler::separate_machine_tags_in_content($content);
            if (!empty($content_tags))
            {
                debug_add("found machine tags string: {$content_tags}");
                net_nemein_tag_handler::tag_object($this->_photo, net_nemein_tag_handler::string2tag_array($content_tags));
            }
            debug_add("content AFTER machine tag separation\n===\n{$content}\n===\n");
            debug_pop();
        }

        // Populate rest of the data
        $this->_datamanager->types['description']->value = $content;
        if (!empty($data['tags_field']))
        {
            // if we have tags field put content_tags value there as well or they will get deleted!
            $this->_datamanager->types[$data['tags_field']]->value = $content_tags;
        }
        $body_switched = false;
        $photo_added = false;
        foreach ($this->_decoder->attachments as $att)
        {
            debug_add("processing attachment {$att['name']}");

            switch (true)
            {
                case (strpos($att['mimetype'], 'image/') !== false):
                    if ($photo_added)
                    {
                        // Only add the first image
                        break;
                    }
                    $photo_added = $this->_add_image($att);
                    break;
                case (strtolower($att['mimetype']) == 'text/plain'):
                    if (!$body_switched)
                    {
                        // Use first text/plain part as the content
                        $this->_datamanager->types['description']->value = $att['content'];
                        $body_switched = true;
                        break;
                    }
                    // Fall-through if not switching
                default:
                    // Don't know what to do with this
            }
        }

        if (!$photo_added)
        {
            // Could not add any image, abort
            // Purge the photo
            $this->_photo->delete();
            // PONDER: There must be a reason not to use generate_error, but I can't remember it
            echo "ERROR: Could not save an image to object. Midgard error was: " . mgd_errstr() . "\n";
            $_MIDCOM->finish();
            exit();
        }

        if (!$this->_datamanager->save())
        {
            // Purge the photo
            $this->_photo->delete();

            // Give error the the MDA so it doesn't delete the message
            // PONDER: There must be a reason not to use generate_error, but I can't remember it
            echo "ERROR: Datamanager failed to save the object. Midgard error was: " . mgd_errstr() . "\n";
            $_MIDCOM->finish();
            exit();
        }

        // Index the photo
        /*
        $indexer =& $_MIDCOM->get_service('indexer');
        org_routamc_photostream_viewer::index($this->_datamanager, $indexer, $this->_topic);
        */

        $_MIDCOM->auth->drop_sudo();
        debug_pop();
        return true;
    }

    function _show_import($handler_id, &$data)
    {
        //All done
        echo "OK\n";
    }

    function _decode_email()
    {
        //Load o.o.mail
        $_MIDCOM->load_library('org.openpsa.mail');

        //Make sure we have the components we use and the Mail_mimeDecode package
        if (!class_exists('org_openpsa_mail'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'library org.openpsa.mail could not be loaded.');
            // This will exit.
        }

        $this->_decoder = new org_openpsa_mail();

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

        $this->_decoder = new org_openpsa_mail();
        $this->_decoder->body = $_POST['message_source'];
        $this->_decoder->mime_decode();
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("got subject: {$this->_decoder->subject}");
        debug_print_r("got headers: " , $this->_decoder->headers);
        debug_add("got body\n===\n{$this->_decoder->body}\n===\n");
        debug_pop();
    }

    function _parse_email_persons()
    {
        //Parse email addresses
        $regex = '/<?([a-zA-Z0-9_.-]+?@[a-zA-Z0-9_.-]+)>?[ ,]?/';
        $emails = array();
        if (preg_match_all($regex, $this->_decoder->headers['To'], $matches_to))
        {
            foreach ($matches_to[1] as $email)
            {
                //Each address only once
                $emails[$email] = $email;
            }
        }
        if (preg_match_all($regex, $this->_decoder->headers['Cc'], $matches_cc))
        {
            foreach ($matches_cc[1] as $email)
            {
                //Each address only once
                $emails[$email] = $email;
            }
        }
        $from = false;
        if (preg_match_all($regex, $this->_decoder->headers['From'], $matches_from))
        {
            foreach ($matches_from[1] as $email)
            {
                //Each address only once
                $emails[$email] = $email;
                //It's unlikely that we'd get multiple matches in From, but we use the latest
                $this->_request_data['from'] = $email;
            }
        }
    }

    function _add_image($att)
    {
        if (!array_key_exists('photo_field', $this->_request_data))
        {
            // No image fields in schema, revert to regular attachment handling
            return false;
        }

        // Save image to a temp file
        $tmp_name = tempnam('/tmp', 'org_routamc_photostream_handler_api_email_');
        $fp = fopen($tmp_name, 'w');

        if (!fwrite($fp, $att['content']))
        {
            //Could not write, clean up and continue
            debug_add("Error when writing file {$tmp_name}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            fclose($fp);
            return false;
        }

        return $this->_datamanager->types[$this->_request_data['photo_field']]->set_image($att['name'], $tmp_name, $att['name']);
    }

    function _find_email_person($email, $prefer_user = true)
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
}