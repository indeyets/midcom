<?php
/**
 * @package org.routamc.statusmessage
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 *
 */

/**
  * Created on 2006-Oct-Thu
  *
  * @package org.routamc.statusmessage
  */
class org_routamc_statusmessage_handler_list extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function org_routamc_statusmessage_handler_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Resolve username or person GUID to a midcom_db_person object
     *
     * @param string $username Username or GUID
     * @return midcom_db_person Matching person or null
     */
    function _resolve_user($username)
    {
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('username', '=', $username);
        $users = $qb->execute();
        if (count($users) > 0)
        {
            return $users[0];
        }

        if (mgd_is_guid($username))
        {
            // Try resolving as GUID as well
            $user = new midcom_db_person($username);
            return $user;
        }

        return null;
    }

    /**
     * Prepare a paged query builder for listing messages
     */
    function &_prepare_message_qb()
    {
        $qb = new org_openpsa_qbpager('org_routamc_statusmessage_message_dba', 'org_routamc_statusmessage_message');
        $qb->results_per_page = $this->_config->get('messages_per_page');
        $this->_request_data['qb'] =& $qb;
        return $qb;
    }

    function _prepare_ajax_controllers()
    {
        // Initiate AJAX controllers for all messages
        $this->_request_data['controllers'] = array();
        foreach ($this->_request_data['messages'] as $message)
        {
            $this->_request_data['controllers'][$message->id] =& midcom_helper_datamanager2_controller::create('ajax');
            $this->_request_data['controllers'][$message->id]->schemadb =& $this->_request_data['schemadb'];
            $this->_request_data['controllers'][$message->id]->set_storage($message);
            $this->_request_data['controllers'][$message->id]->process_ajax();
        }
    }

    /**
     * The handler for displaying a messagegrapher's statusmessage
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_microsummary($handler_id, $args, &$data)
    {
        if ($handler_id == 'list_microsummary')
        {
            $data['user'] = $this->_resolve_user($args[0]);
            if (!$data['user'])
            {
                return false;
            }

            $data['view_title'] = sprintf($this->_l10n->get('status of %s'), $data['user']->name);
            $data['user_url'] = $args[0];
        }
        else
        {
            $data['view_title'] = $this->_l10n->get('status');
            $data['user_url'] = 'all';
        }

        // List messages
        $qb = org_routamc_statusmessage_message_dba::new_query_builder();

        if ($handler_id == 'statusmessage_latest')
        {
            // Limit list of messages to the user
            $qb->add_constraint('metadata.author', '=', $data['user']->guid);
        }

        $qb->add_order('metadata.published', 'DESC');
        $qb->set_limit(1);

        $data['messages'] = $qb->execute();

        if (count($data['messages']) == 0)
        {
            return false;
        }

        // Make messages AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        // Set correct MIME type
        $_MIDCOM->cache->content->content_type('text/plain');
        $_MIDCOM->header('Content-type: text/plain');


        $_MIDCOM->skip_page_style = true;

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_microsummary($handler_id, &$data)
    {
        $data['message'] = $data['messages'][0];

        midcom_show_style('show_microsummary');
    }

    /**
     * The handler for displaying a messagegrapher's statusmessage
     *
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        if ($handler_id == 'statusmessage_list')
        {
            $data['user'] = $this->_resolve_user($args[0]);
            if (!$data['user'])
            {
                return false;
            }

            $data['view_title'] = sprintf($this->_l10n->get('messages of %s'), $data['user']->name);
            $data['user_url'] = $args[0];
        }
        else
        {
            $data['view_title'] = $this->_l10n->get('all messages');
            $data['user_url'] = 'all';
        }

        // List messages
        $qb =& $this->_prepare_message_qb();

        if ($handler_id == 'statusmessage_list')
        {
            // Limit list of messages to the user
            $qb->add_constraint('metadata.author', '=', $data['user']->guid);
        }

        $qb->add_order('metadata.published', 'DESC');
        $data['messages'] = $qb->execute();

        // Make messages AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        $this->_show_statusmessages($handler_id, &$data);
    }

    /**
     * The handler for displaying a messagegrapher's statusmessage
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_latest($handler_id, $args, &$data)
    {
        if ($handler_id == 'list_latest')
        {
            $data['user'] = $this->_resolve_user($args[0]);
            if (!$data['user'])
            {
                return false;
            }

            $data['view_title'] = sprintf($this->_l10n->get('latest messages of %s'), $data['user']->name);
            $data['user_url'] = $args[0];
            $data['limit'] = $args[1];
        }
        else
        {
            $data['view_title'] = $this->_l10n->get('latest messages');
            $data['user_url'] = 'all';

            if ($handler_id == 'list_latest_front')
            {
                $data['limit'] = 10;
            }
            else
            {
                $data['limit'] = $args[0];
            }
        }

        // List messages
        $qb = org_routamc_statusmessage_message_dba::new_query_builder();

        if ($handler_id == 'statusmessage_latest')
        {
            // Limit list of messages to the user
            $qb->add_constraint('metadata.author', '=', $data['user']->guid);
        }

        $qb->add_order('metadata.published', 'DESC');
        $qb->set_limit($data['limit']);

        $data['messages'] = $qb->execute();

        // Make messages AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_latest($handler_id, &$data)
    {
        $this->_show_statusmessages($handler_id, &$data);
    }

    /**
     * The handler for displaying messages in time window
     *
     * @param mixed $handler_id the array key from the request array
     * @param array $args the arguments given to the handler
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     * @todo 1.7 support
     */
    function _handler_between($handler_id, $args, &$data)
    {
        // TODO: Check format as YYYY-MM-DD via regexp
        $data['from_time'] = @strtotime($args[0]);
        $data['to_time'] = @strtotime($args[1]);
        if (   !$data['from_time']
            || !$data['to_time'])
        {
            return false;
        }

        $data['view_title'] = sprintf($this->_l10n->get('messages from %s - %s'), strftime('%x', $data['from_time']), strftime('%x', $data['to_time']));
        $qb =& $this->_prepare_message_qb();
        $qb->add_constraint('metadata.published', '>=', $data['from_time']);
        $qb->add_constraint('metadata.published', '<=', $data['to_time']);
        $data['messages'] = $qb->execute();

        // Make messages AJAX-editable
        $this->_prepare_ajax_controllers();

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");
        $this->_update_breadcrumb_line($handler_id);

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_between($handler_id, &$data)
    {
        $this->_show_statusmessages($handler_id, &$data);
    }

    /**
     * Display a list of messages. This method is used by several of the request
     * switches.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_statusmessages($handler_id, &$data)
    {
        midcom_show_style('show_statusmessages_header');

        foreach ($data['messages'] as $message)
        {
            $data['message'] = $message;

            $data['message_view'] = $data['controllers'][$message->id]->get_content_html();
            $data['datamanager'] =& $data['controllers'][$message->id]->datamanager;

            midcom_show_style('show_statusmessages_item');
        }

        midcom_show_style('show_statusmessages_footer');
    }

    /**
     * Helper, updates the context so that we get a complete breadcrumb line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line($handler_id)
    {
        $tmp = Array();

        switch ($handler_id)
        {
            case 'statusmessage_list_all':
            case 'statusmessage_list':
                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "list/{$this->_request_data['user_url']}/",
                    MIDCOM_NAV_NAME => $this->_request_data['view_title'],
                );
                break;
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>