<?php
/**
 * @package org.openpsa.directmarketing
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: index.php 4051 2006-09-12 07:32:51Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum index
 *
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_handler_message_send extends midcom_baseclasses_components_handler
{
    /**
     * The message which has been created
     *
     * @var org_openpsa_directmarketing_message
     * @access private
     */
    var $_message = null;

    /**
     * Simple default constructor.
     */
    function org_openpsa_directmarketing_handler_message_send()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Internal helper, loads the datamanager for the current message. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_message']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for messages.");
            // This will exit.
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_send_bg($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->request_sudo();

        //Load message
        $data['message'] = new org_openpsa_directmarketing_campaign_message($args[0]);
        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($data['message']);
        $data['message_obj'] =& $data['message'];

        if (!$data['message'])
        {
            debug_pop();
            return false;
        }
        //Check other paramerers
        if (   !isset($args[1])
            || !is_numeric($args[1]))
        {
            debug_add('Batch number missing', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $data['batch_number'] = $args[1];
        if (!isset($args[2]))
        {
            debug_add('Job GUID missing', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $job = new midcom_services_at_entry($args[2]);
        if (!is_a($job, 'midcom_services_at_entry'))
        {
            debug_add('Invalid job GUID', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $data['message_array'] = $this->_datamanager->get_content_raw();
        $data['message_array']['dm_types'] =& $this->_datamanager->types;
        if (!array_key_exists('content', $data['message_array']))
        {
            debug_add('"content" not defined in schema, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        ignore_user_abort();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->auth->drop_sudo();
        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_send_bg($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->request_sudo();
        debug_add('Forcing content type: text/plain');
        $_MIDCOM->cache->content->content_type('text/plain');
        $composed = $this->_prepare_send($data);
        $data['message_obj']->test_mode = false;
        $data['message_obj']->send_output = false;
        $bgstat = $data['message_obj']->send_bg($data['batch_url_base_full'], $data['batch_number'], $composed, $data['compose_from'], $data['compose_subject'], $data['message_array']);
        if (!$bgstat)
        {
            //TODO: echo some sort of error for the AT handler to catch (plaintext)
            echo "ERROR\n";
        }
        else
        {
            echo "Batch #{$data['batch_number']} DONE\n";
        }
        $_MIDCOM->auth->drop_sudo();
        debug_pop();
    }

    function _prepare_send(&$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $nap = new midcom_helper_nav();
        $node = $nap->get_node($nap->get_current_node());
        $data['compose_url'] = $node[MIDCOM_NAV_RELATIVEURL] . 'message/compose/' . $data['message_obj']->guid();
        $data['batch_url_base_full'] = $node[MIDCOM_NAV_RELATIVEURL] . 'message/send_bg/' . $data['message_obj']->guid();
        debug_add("compose_url: {$data['compose_url']}");
        debug_add("batch_url base: {$data['batch_url_base_full']}");
        $de_backup = ini_get('display_errors');
        $le_backup = ini_get('log_errors');
        ini_set('log_errors', true);
        ini_set('display_errors', false);
        ob_start();
        $_MIDCOM->dynamic_load($data['compose_url']);
        $composed = ob_get_contents();
        ob_end_clean();
        ini_set('display_errors', $de_backup);
        ini_set('log_errors', $le_backup);
        //We force the content-type since the compositor might have set it to something else in compositor for preview purposes
        debug_add('Forcing content type: text/html');
        $_MIDCOM->cache->content->content_type('text/html');

        //PONDER: Should we leave these entirely for the methods to parse from the array ?
        $data['compose_subject'] = '';
        $data['compose_from'] = '';
        if (array_key_exists('subject', $data['message_array']))
        {
            $data['compose_subject'] = &$data['message_array']['subject'];
        }
        if (array_key_exists('from', $data['message_array']))
        {
            $data['compose_from'] = &$data['message_array']['from'];
        }

        //Get SMS/MMS settings from component configuration
        if ($smslib_api = $this->_config->get('smslib_api'))
        {
            $data['message_obj']->sms_lib_api = $smslib_api;
        }
        if ($smslib_uri = $this->_config->get('smslib_uri'))
        {
            $data['message_obj']->sms_lib_location = $smslib_uri;
        }
        else if ($email2sms_address = $this->_config->get('email2sms_address'))
        {
            $data['message_obj']->sms_lib_location = $email2sms_address;
        }
        if ($smslib_client_id = $this->_config->get('smslib_client_id'))
        {
            $data['message_obj']->sms_lib_client_id = $smslib_client_id;
        }
        if ($smslib_user = $this->_config->get('smslib_user'))
        {
            $data['message_obj']->sms_lib_user = $smslib_user;
        }
        if ($smslib_password = $this->_config->get('smslib_password'))
        {
            $data['message_obj']->sms_lib_password = $smslib_password;
        }

        if ($mail_send_backend = $this->_config->get('mail_send_backend'))
        {
            $data['message_array']['mail_send_backend'] = $mail_send_backend;
        }
        if ($bouncer_address = $this->_config->get('bouncer_address'))
        {
            $data['message_array']['bounce_detector_address'] = $bouncer_address;
        }
        if ($link_detector_address = $this->_config->get('linkdetector_address'))
        {
            $data['message_array']['link_detector_address'] = $link_detector_address;
        }
        if ($token_size = $this->_config->get('token_size'))
        {
            $data['message_obj']->token_size = $token_size;
        }

        debug_pop();
        return $composed;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_send($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        //Load message
        $data['message'] = new org_openpsa_directmarketing_campaign_message($args[0]);
        $data['campaign'] = new org_openpsa_directmarketing_campaign($data['message']->campaign);
        $this->_component_data['active_leaf'] = "campaign_{$data['campaign']->id}";

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "message/{$data['message']->guid}/",
            MIDCOM_NAV_NAME => $data['message']->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "send_test/{$data['message']->guid}/",
            MIDCOM_NAV_NAME => $this->_l10n->get('send'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($data['message']);
        $data['message_obj'] =& $data['message'];

        if ($handler_id === 'delayed_send_message')
        {
            $data['delayed_send'] = true;
            $data['send_start'] = strtotime($args[1]);
            if (   $data['send_start'] == -1
                || $data['send_start'] === false)
            {
                //TODO: We should probably fail the send in stead of defaulting to immediate send
                debug_add("Failed to parse \"{$args[1]}\" into timestamp", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }
        else
        {
            $this->_request_data['send_start'] = time();
            $this->_request_data['delayed_send'] = false;
        }

        if ($handler_id === 'test_send_message')
        {
            $data['message']->test_mode = true;
        }
        else
        {
            $data['message']->test_mode = false;
        }

        $data['message_array'] = $this->_datamanager->get_content_raw();
        $data['message_array']['dm_types'] =& $this->_datamanager->types;
        if (!array_key_exists('content', $data['message_array']))
        {
            debug_add('"content" not defined in schema, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        ignore_user_abort();
        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_send($handler_id, &$data)
    {
        $composed = $this->_prepare_send($data);
        // TODO: Figure out the correct use of style elements, this is how it was but it's not exactly optimal...
        switch($handler_id)
        {
            case 'test_send_message':
                // on-line sned
                $data['message_obj']->send_output = true;
                $sendstat = $data['message_obj']->send($composed, $data['compose_from'], $data['compose_subject'], $data['message_array']);
                break;
            default:
                // Schedule background send
                debug_add('Registering background send job to start on: ' . date('Y-m-d H:i:s', $data['send_start']));
                $at_handler_arguments = array(
                    'batch' => 1,
                    'url_base' => $data['batch_url_base_full'],
                );
                midcom_services_at_interface::register($data['send_start'], 'org.openpsa.directmarketing', 'background_send_message', $at_handler_arguments);
                midcom_show_style('send-start');
                break;
        }
    }

}
?>