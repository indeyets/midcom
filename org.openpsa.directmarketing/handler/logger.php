<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: import.php,v 1.4 2006/06/19 09:39:42 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.directmarketing campaign handler and viewer class.
 * @package org.openpsa.directmarketing
 */
class org_openpsa_directmarketing_handler_logger extends midcom_baseclasses_components_handler
{
    function org_openpsa_directmarketing_handler_logger()
    {
        parent::__construct();
    }

    /**
     * Logs a bounce from bounce_detector.php for POSTed token, marks the send receipt
     * and the campaign member as bounced.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_bounce($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !array_key_exists('token', $_POST)
            || empty($_POST['token']))
        {
            //Token not given
            debug_add('Token not present in POST or empty', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $messages = array();
        $campaigns = array();
        $this->_request_data['update_status'] = array('receipts' => array(), 'members' => array());

        $_MIDCOM->auth->request_sudo('org.openpsa.directmarketing');
        debug_add("Looking for token '{$_POST['token']}' in sent receipts");
        $ret = $this->_qb_token_receipts($_POST['token']);
        debug_add("_qb_token_receipts({$_POST['token']}) returned ===\n" . sprint_r($ret) . "===\n");
        if (empty($ret))
        {
            //Token not present
            debug_add("No receipts with token '{$_POST['token']}' found", MIDCOM_LOG_WARN);
            debug_pop();
            $_MIDCOM->auth->drop_sudo();
            return false;
        }
        //While in theory we should have only one token lets use foreach just to be sure
        foreach ($ret as $receipt)
        {
            //Mark receipt as bounced
            debug_add("Found receipt #{$receipt->id}, marking bounced");
            $receipt->bounced = time();
            $this->_request_data['update_status']['receipts'][$receipt->guid] = $receipt->update();

            //Mark member(s) as bounced (first get campaign trough message)
            if (!array_key_exists($receipt->message, $campaigns))
            {
                $messages[$receipt->message] = new org_openpsa_directmarketing_campaign_message($receipt->message);
            }
            $message =& $messages[$receipt->message];
            if (!array_key_exists($message->campaign, $campaigns))
            {
                $campaigns[$message->campaign] = new org_openpsa_directmarketing_campaign($message->campaign);
            }
            $campaign =& $campaigns[$message->campaign];
            debug_add("Receipt belongs to message '{$message->title}' (#{$message->id}) in campaign '{$campaign->title}' (#{$campaign->id})");

            $qb2 = org_openpsa_directmarketing_campaign_member::new_query_builder();
            $qb2->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER);
            //PONDER: or should be just mark the person bounced in ALL campaigns while we're at it ?
            //Just in case we somehow miss the campaign
            if (isset($campaign->id))
            {
                $qb2->add_constraint('campaign', '=', $campaign->id);
            }
            $qb2->add_constraint('person', '=', $receipt->person);
            $ret2 = $qb2->execute();
            if (empty($ret2))
            {
                continue;
            }
            foreach ($ret2 as $member)
            {
                debug_add("Found member #{$member->id}, marking bounced");
                $member->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_BOUNCED;
                $this->_request_data['update_status']['members'][$member->guid] = $member->update();
            }
        }

        $_MIDCOM->auth->drop_sudo();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type('text/plain');
        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_bounce($handler_id, &$data)
    {
        echo "OK\n";
        //PONDER: check  $this->_request_data['update_status'] and display something else in case all is not ok ?
    }

    /**
     * QB search for message receipts with given token and type
     * @param string $token token string
     * @param int $type receipt type, defaults to ORG_OPENPSA_MESSAGERECEIPT_SENT
     * @return array QB->execute results
     */
    function _qb_token_receipts($token, $type = ORG_OPENPSA_MESSAGERECEIPT_SENT)
    {
        $qb = org_openpsa_directmarketing_campaign_message_receipt::new_query_builder();
        $qb->add_constraint('token', '=', $token);
        $qb->add_constraint('orgOpenpsaObtype', '=', $type);
        //mgd_debug_start();
        $ret = $qb->execute();
        //mgd_debug_stop();
        return $ret;
    }

    /**
     * Logs a link click from link_detector.php for POSTed token, binds to person
     * and creates received and read receipts as well
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_link($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !array_key_exists('token', $_POST)
            || empty($_POST['token']))
        {
            //Token not given
            debug_add('Token not present in POST or empty', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   !array_key_exists('link', $_POST)
            || empty($_POST['link']))
        {
            //Link not given
            debug_add('Link not present in POST or empty', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $_MIDCOM->auth->request_sudo('org.openpsa.directmarketing');
        debug_add("Looking for token '{$_POST['token']}' in sent receipts");
        $ret = $this->_qb_token_receipts($_POST['token']);
        debug_add("_qb_token_receipts({$_POST['token']}) returned ===\n" . sprint_r($ret) . "===\n");
        if (empty($ret))
        {
            //Token not present
            debug_add("No receipts with token '{$_POST['token']}' found", MIDCOM_LOG_WARN);
            debug_pop();
            $_MIDCOM->auth->drop_sudo();
            return false;
        }
        //While in theory we should have only one token lets use foreach just to be sure
        foreach ($ret as $receipt)
        {
            $this->_create_link_receipt($receipt, $_POST['token'], $_POST['link']);
        }

        $_MIDCOM->auth->drop_sudo();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type('text/plain');
        debug_pop();
        return true;
    }

    function _create_link_receipt(&$receipt, &$token, &$target)
    {
        if (!array_key_exists('create_status', $this->_request_data))
        {
            $this->_request_data['create_status'] = array('receipts' => array(), 'links' => array());
        }

        //Store the click in database
        $link = new org_openpsa_directmarketing_link_log();
        $link->person = $receipt->person;
        $link->message = $receipt->message;
        $link->target = $target;
        $link->token = $token;
        //mgd_debug_start();
        $this->_request_data['create_status']['links'][$target] = $link->create();
        //mgd_debug_stop();

        //Create received and read receipts
        $read_receipt = new org_openpsa_directmarketing_campaign_message_receipt();
        $read_receipt->person = $receipt->person;
        $read_receipt->message = $receipt->message;
        $read_receipt->token = $token;
        $read_receipt->orgOpenpsaObtype = ORG_OPENPSA_MESSAGERECEIPT_RECEIVED;
        //mgd_debug_start();
        $this->_request_data['create_status']['receipts'][$token] = $read_receipt->create();
        //mgd_debug_stop();
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_link($handler_id, &$data)
    {
        echo "OK\n";
        //PONDER: check $this->_request_data['create_status'] and display something else in case all is not ok ?
    }

    /**
     * Duplicates link_detector.php functionality in part (to avoid extra apache configurations)
     * and handles the logging mentioned above as well.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_redirect($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_request_data['target'] = false;
        $this->_request_data['token'] = false;
        if (   count($args) == 2
            && !empty($args[1]))
        {
            //Due to the way browsers handle the URLs this form only works for root pages
            $this->_request_data['target'] = $args[1];
        }
        elseif (   array_key_exists('link', $_GET)
                && !empty($_GET['link']))
        {
            $this->_request_data['target'] = $_GET['link'];
        }
        if (!empty($args[0]))
        {
            $this->_request_data['token'] = $args[0];
        }
        if (!$this->_request_data['token'])
        {
            //Token not given
            debug_add('Token empty', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (!$this->_request_data['target'])
        {
            //Link not given
            debug_add('Target not present in address or GET, or is empty', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        //TODO: valid target domains check

        //If we have a dummy token don't bother with looking for it, just go on.
        if ($this->_request_data['token'] === 'dummy')
        {
            $_MIDCOM->skip_page_style = true;
            debug_pop();
            $_MIDCOM->relocate($this->_request_data['target']);
            //This will exit unless fails
            return true;
        }

        $_MIDCOM->auth->request_sudo('org.openpsa.directmarketing');
        debug_add("Looking for token '{$this->_request_data['token']}' in sent receipts");
        $ret = $this->_qb_token_receipts($this->_request_data['token']);
        debug_add("_qb_token_receipts({$this->_request_data['token']}) returned ===\n" . sprint_r($ret) . "===\n");
        if (empty($ret))
        {
            //Token not present
            debug_add("No receipts with token '{$this->_request_data['token']}' found", MIDCOM_LOG_WARN);
            debug_pop();
            $_MIDCOM->auth->drop_sudo();
            return false;
        }

        //While in theory we should have only one token lets use foreach just to be sure
        foreach ($ret as $receipt)
        {
            $this->_create_link_receipt($receipt, $this->_request_data['token'], $this->_request_data['target']);
        }

        $_MIDCOM->auth->drop_sudo();
        $_MIDCOM->skip_page_style = true;
        debug_pop();
        $_MIDCOM->relocate($this->_request_data['target']);
        //This will exit unless fails
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_redirect($handler_id, &$data)
    {
        //TODO: make an element to display in case our relocate fails (with link to the intended target...)
    }

}
?>