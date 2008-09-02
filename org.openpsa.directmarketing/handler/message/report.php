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
class org_openpsa_directmarketing_handler_message_report extends midcom_baseclasses_components_handler
{
    /**
     * The message which has been created
     *
     * @var org_openpsa_directmarketing_message
     * @access private
     */
    var $_message = null;

    var $_datamanager = false;

    /**
     * Simple default constructor.
     */
    function org_openpsa_directmarketing_handler_message_compose()
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
     * Builds the message report array
     */
    function _analyze_message_report(&$data)
    {
        $segmentation_param = false;
        if (   isset($data['message_array']['report_segmentation'])
            && !empty($data['message_array']['report_segmentation']))
        {
            $segmentation_param = $data['message_array']['report_segmentation'];
        }
        $this->_request_data['report'] = array();
        $qb_receipts = org_openpsa_directmarketing_campaign_message_receipt::new_query_builder();
        $qb_receipts->add_constraint('message', '=', $this->_request_data['message']->id);
        $qb_receipts->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_MESSAGERECEIPT_SENT);
        $receipts = $qb_receipts->execute_unchecked();
        $this->_request_data['report']['receipt_data'] = array();
        $receipt_data =& $this->_request_data['report']['receipt_data'];
        $receipt_data['first_send'] = false;
        $receipt_data['last_send'] = false;
        $receipt_data['sent'] = count($receipts);
        $receipt_data['bounced'] = 0;
        foreach ($receipts as $receipt)
        {
            if (   $receipt_data['first_send'] === false
                || $receipt->timestamp < $receipt_data['first_send'])
            {
                $receipt_data['first_send'] = $receipt->timestamp;
            }
            if (   $receipt_data['last_send'] === false
                || $receipt->timestamp > $receipt_data['last_send'])
            {
                $receipt_data['last_send'] = $receipt->timestamp;
            }
            if ($receipt->bounced)
            {
                $receipt_data['bounced']++;
            }
        }

        $this->_request_data['report']['campaign_data'] = array();
        $campaign_data =& $this->_request_data['report']['campaign_data'];
        $campaign_data['unsubscribed'] = 0;
        $qb_unsub = org_openpsa_directmarketing_campaign_member::new_query_builder();
        $qb_unsub->add_constraint('campaign', '=', $this->_request_data['message']->campaign);
        $qb_unsub->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED);
        $qb_unsub->add_constraint('metadata.revised', '>', date('Y-m-d H:i:s', $receipt_data['first_send']));
        $campaign_data['next_message'] = false;
        // Find "next message" and if present use its sendStarted as constraint for this query
        $qb_messages = org_openpsa_directmarketing_campaign_message::new_query_builder();
        $qb_messages->add_constraint('campaign', '=',  $this->_request_data['message']->campaign);
        $qb_messages->add_constraint('id', '<>',  $this->_request_data['message']->id);
        $qb_messages->add_constraint('sendStarted', '>', $receipt_data['first_send']);
        $qb_messages->add_order('sendStarted', 'DESC');
        $qb_messages->set_limit(1);
        $messages = $qb_messages->execute_unchecked();
        if (   is_array($messages)
            && isset($messages[0]))
        {
            $campaign_data['next_message'] = $messages[0];
            $qb_unsub->add_constraint('metadata.revised', '<', date('Y-m-d H:i:s', $messages[0]->sendStarted));
        }
        $campaign_data['unsubscribed'] = $qb_unsub->count_unchecked();

        $this->_request_data['report']['link_data'] = array();
        $link_data =& $this->_request_data['report']['link_data'];
        $link_data['counts'] = array();
        $link_data['percentages'] = array('of_links' => array(), 'of_recipients' => array());
        $link_data['rules'] = array();
        if ($segmentation_param)
        {
            $link_data['segments'] = array();
        }
        $segment_prototype = array();
        $segment_prototype['counts'] = array();
        $segment_prototype['percentages'] = array('of_links' => array(), 'of_recipients' => array());
        $segment_prototype['rules'] = array();

        $qb_links = org_openpsa_directmarketing_link_log::new_query_builder();
        $qb_links->add_constraint('message', '=', $this->_request_data['message']->id);
        $qb_links->add_constraint('target', 'NOT LIKE', '%unsubscribe%');
        $links = $qb_links->execute_unchecked();

        $link_data['total'] = count($links);

        $link_data['tokens'] = array();
        $segment_data['tokens'] = array();
        foreach($links as $link)
        {
            $segment = '';
            $segment_notfound = false;
            if (   $segmentation_param
                && !empty($link->person))
            {
                $person = new midcom_db_person($link->person);
                if (   is_object($person)
                    && method_exists($person, 'get_parameter'))
                {
                    $segment = $person->parameter('org.openpsa.directmarketing.segments', $segmentation_param);
                }
                if (empty($segment))
                {
                    $segment = $this->_request_data['l10n']->get('no segment');
                    $segment_notfound = true;
                }
                if (!isset($link_data['segments'][$segment]))
                {
                    $link_data['segments'][$segment] = $segment_prototype;
                }
                $segment_data =& $link_data['segments'][$segment];
            }
            else
            {
                $segment_data = $segment_prototype;
            }
            
            if (!isset($link_data['tokens'][$link->token]))
            {
                $link_data['tokens'][$link->token] = 0;
            }
            if (!isset($segment_data['tokens'][$link->token]))
            {
                $segment_data['tokens'][$link->token] = 0;
            }
            
            if (!isset($link_data['counts'][$link->target]))
            {
                $link_data['counts'][$link->target] = array();
                $link_data['counts'][$link->target]['total'] = 0;
            }
            if (!isset($link_data['counts'][$link->target][$link->token]))
            {
                $link_data['counts'][$link->target][$link->token] = 0;
            }
            if (!isset($segment_data['counts'][$link->target]))
            {
                $segment_data['counts'][$link->target] = array();
                $segment_data['counts'][$link->target]['total'] = 0;
            }
            if (!isset($segment_data['counts'][$link->target][$link->token]))
            {
                $segment_data['counts'][$link->target][$link->token] = 0;
            }
            if (!isset($link_data['percentages']['of_links'][$link->target]))
            {
                $link_data['percentages']['of_links'][$link->target] = array();
                $link_data['percentages']['of_links'][$link->target]['total'] = 0;
            }
            if (!isset($link_data['percentages']['of_links'][$link->target][$link->token]))
            {
                $link_data['percentages']['of_links'][$link->target][$link->token] = 0;
            }
            if (!isset($link_data['percentages']['of_recipients'][$link->target]))
            {
                $link_data['percentages']['of_recipients'][$link->target] = array();
                $link_data['percentages']['of_recipients'][$link->target]['total'] = 0;
            }
            if (!isset($link_data['percentages']['of_recipients'][$link->target][$link->token]))
            {
                $link_data['percentages']['of_recipients'][$link->target][$link->token] = 0;
            }
            if (!isset($segment_data['percentages']['of_links'][$link->target]))
            {
                $segment_data['percentages']['of_links'][$link->target] = array();
                $segment_data['percentages']['of_links'][$link->target]['total'] = 0;
            }
            if (!isset($segment_data['percentages']['of_links'][$link->target][$link->token]))
            {
                $segment_data['percentages']['of_links'][$link->target][$link->token] = 0;
            }
            if (!isset($segment_data['percentages']['of_recipients'][$link->target]))
            {
                $segment_data['percentages']['of_recipients'][$link->target] = array();
                $segment_data['percentages']['of_recipients'][$link->target]['total'] = 0;
            }
            if (!isset($segment_data['percentages']['of_recipients'][$link->target][$link->token]))
            {
                $segment_data['percentages']['of_recipients'][$link->target][$link->token] = 0;
            }
            if (!isset($link_data['rules'][$link->target]))
            {
                $link_data['rules'][$link->target] = array
                (
                    'comment' => sprintf($this->_request_data['l10n']->get('all persons who have clicked on link "%s" in message #%d and have not unsubscribed from campaign #%d'), $link->target, $link->message, $this->_request_data['message']->campaign),
                    'type' => 'AND',
                    'classes' => array
                    (
                        array
                        (
                            'comment' => $this->_request_data['l10n']->get('link and message limits'),
                            'type' => 'AND',
                            'class' => 'org_openpsa_directmarketing_link_log',
                            'rules' => array
                            (
                                array
                                (
                                    'property' => 'target',
                                    'match' => '=',
                                    'value' => $link->target,
                                ),
                                // PONDER: do we want to limit to this message only ??
                                array
                                (
                                    'property' => 'message',
                                    'match' => '=',
                                    'value' => $link->message,
                                ),
                            ),
                        ),
                        // Add rule that prevents unsubscribed persons from ending up to the smart-campaign ??
                        array
                        (
                            'comment' => $this->_request_data['l10n']->get('not-unsubscribed -limits'),
                            'type' => 'AND',
                            'class' => 'org_openpsa_directmarketing_campaign_member',
                            'rules' => array
                            (
                                array
                                (
                                    'property' => 'orgOpenpsaObtype',
                                    'match' => '<>',
                                    'value' => ORG_OPENPSA_OBTYPE_CAMPAIGN_MEMBER_UNSUBSCRIBED,
                                ),
                                array
                                (
                                    'property' => 'campaign',
                                    'match' => '=',
                                    'value' => $this->_request_data['message']->campaign,
                                ),
                            ),
                        ),
                    ),
                );
            }
            if (!isset($segment_data['rules'][$link->target]))
            {
                $segment_data['rules'][$link->target] = $link_data['rules'][$link->target];

                if ($segment_notfound)
                {
                    $use_segment = '';
                }
                else
                {
                    $use_segment = $segment;
                }
                $segmentrule = array
                (
                    'comment' => $this->_request_data['l10n']->get('segment limits'),
                    'type' => 'AND',
                    'class' => 'midcom_db_person',
                    'rules' => array
                    (
                        array
                        (
                            'property' => 'parameter.domain',
                            'match' => '=',
                            'value' => 'org.openpsa.directmarketing.segments',
                        ),
                        array
                        (
                            'property' => 'parameter.name',
                            'match' => '=',
                            'value' => $segmentation_param,
                        ),
                        array
                        (
                            'property' => 'parameter.value',
                            'match' => '=',
                            'value' => $use_segment,
                        ),
                    ),
                );
                if (!empty($use_segment))
                {
                    // On a second thought, we cannot query for empty parameter values...
                    $segment_data['rules'][$link->target]['comment'] = sprintf($this->_request_data['l10n']->get('all persons in market segment "%s" who have clicked on link "%s" in message #%d and have not unsubscribed from campaign #%d'), $segment, $link->target, $link->message, $this->_request_data['message']->campaign);
                    $segment_data['rules'][$link->target]['classes'][] = $segmentrule;
                }
            }
            $link_data['counts'][$link->target]['total']++;
            $link_data['counts'][$link->target][$link->token]++;
            $segment_data['counts'][$link->target]['total']++;
            $segment_data['counts'][$link->target][$link->token]++;
            $link_data['percentages']['of_links'][$link->target]['total'] = ($link_data['counts'][$link->target]['total']/$link_data['total'])*100;
            $link_data['percentages']['of_links'][$link->target][$link->token] = ($link_data['counts'][$link->target][$link->token]/$link_data['total'])*100;
            $segment_data['percentages']['of_links'][$link->target]['total'] = ($segment_data['counts'][$link->target]['total']/$link_data['total'])*100;
            $segment_data['percentages']['of_links'][$link->target][$link->token] = ($segment_data['counts'][$link->target][$link->token]/$link_data['total'])*100;

            $link_data['tokens'][$link->token]++;
            $segment_data['tokens'][$link->token]++;

            $link_data['percentages']['of_recipients'][$link->target]['total'] = ((count($link_data['counts'][$link->target])-1)/($receipt_data['sent']-$receipt_data['bounced']))*100;
            $link_data['percentages']['of_recipients'][$link->target][$link->token] = ($link_data['counts'][$link->target][$link->token]/($receipt_data['sent']-$receipt_data['bounced']))*100;
            $segment_data['percentages']['of_recipients'][$link->target]['total'] = ((count($segment_data['counts'][$link->target])-1)/($receipt_data['sent']-$receipt_data['bounced']))*100;
            $segment_data['percentages']['of_recipients'][$link->target][$link->token] = ($segment_data['counts'][$link->target][$link->token]/($receipt_data['sent']-$receipt_data['bounced']))*100;
            if(   (!isset($link_data['percentages']['of_recipients']['total']))
               || $link_data['percentages']['of_recipients'][$link->target]['total'] > $link_data['percentages']['of_recipients']['total'])
            {
                $link_data['percentages']['of_recipients']['total'] = $link_data['percentages']['of_recipients'][$link->target]['total'];
            }
            if(   (!isset($segment_data['percentages']['of_recipients']['total']))
               || $segment_data['percentages']['of_recipients'][$link->target]['total'] > $segment_data['percentages']['of_recipients']['total'])
            {
                $segment_data['percentages']['of_recipients']['total'] = $segment_data['percentages']['of_recipients'][$link->target]['total'];
            }
        }
        arsort($link_data['counts']);
        arsort($link_data['percentages']['of_links']);
        arsort($link_data['percentages']['of_recipients']);

        if ($segmentation_param)
        {
            ksort($link_data['segments']);
            foreach ($link_data['segments'] as $segment => $dummy)
            {
                $segment_data =& $link_data['segments'][$segment];
                arsort($segment_data['counts']);
                arsort($segment_data['percentages']['of_links']);
                arsort($segment_data['percentages']['of_recipients']);
            }
        }

        return true;
    }

    function _create_campaign_from_link()
    {
        $campaign = new org_openpsa_directmarketing_campaign();
        $campaign->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_CAMPAIGN_SMART;
        $eval = '$tmp_array = ' . $_POST['org_openpsa_directmarketing_campaign_rule_' . $_POST['org_openpsa_directmarketing_campaign_userule']] . ';';
        $eval_ret = @eval($eval);
        if ($eval_ret === false)
        {
            return false;
            // this will exit
        }
        $campaign->rules = $tmp_array;
        $campaign->description = $tmp_array['comment'];
        $campaign->title = sprintf($this->_request_data['l10n']->get('from link "%s"'), $_POST['org_openpsa_directmarketing_campaign_label_' . $_POST['org_openpsa_directmarketing_campaign_userule']]);
        $campaign->testers[$_MIDGARD['user']] = true;
        if (!$campaign->create())
        {
            return false;
            // this will exit
        }
        $campaign->schedule_update_smart_campaign_members();
        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
            . "campaign/edit/{$campaign->guid}.html");
        // This will exit()
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report($handler_id, $args, &$data)
    {
        $this->_message = new org_openpsa_directmarketing_campaign_message($args[0]);
        if (   !is_object($this->_message)
            || !$this->_message->id)
        {
            return false;
        }
        $data['message'] =& $this->_message;
        $this->_load_datamanager();
        $this->_datamanager->autoset_storage($data['message']);
        $data['message_array'] = $this->_datamanager->get_content_raw();
        $this->_campaign = new org_openpsa_directmarketing_campaign($this->_message->campaign);
        if (   !is_object($this->_campaign)
            || !$this->_campaign->id)
        {
            return false;
        }
        $data['campaign'] =& $this->_campaign;
        $this->_component_data['active_leaf'] = "campaign_{$data['campaign']->id}";

        if (   isset($_POST['org_openpsa_directmarketing_campaign_userule'])
            && isset($_POST['org_openpsa_directmarketing_campaign_rule_' . $_POST['org_openpsa_directmarketing_campaign_userule']])
            && !empty($_POST['org_openpsa_directmarketing_campaign_rule_' . $_POST['org_openpsa_directmarketing_campaign_userule']])
            )
        {
            $this->_create_campaign_from_link();
        }
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "campaign/{$this->_campaign->guid}/",
            MIDCOM_NAV_NAME => $this->_campaign->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "message/{$this->_message->guid}/",
            MIDCOM_NAV_NAME => $this->_message->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "message/report/{$this->_message->guid}.html",
            MIDCOM_NAV_NAME => $this->_l10n->get('message report'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);


        $this->_view_toolbar->add_item
        (
            Array(
                MIDCOM_TOOLBAR_URL => "message/{$this->_request_data['message']->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("back"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );
        if (   !empty($_MIDCOM->auth->user)
            && !empty($_MIDCOM->auth->user->guid))
        {
            $preview_url = "message/compose/{$this->_message->guid}/{$_MIDCOM->auth->user->guid}.html";
        }
        else
        {
            $preview_url = "message/compose/{$this->_message->guid}.html";
        }
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => $preview_url,
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('preview message'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'p',
                MIDCOM_TOOLBAR_ENABLED => $this->_message->can_do('midgard:read'),
                MIDCOM_TOOLBAR_OPTIONS => array('target' => '_BLANK'),
            )
        );
        return $this->_analyze_message_report($data);
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_report($handler_id, &$data)
    {
        midcom_show_style('show-message-report');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_status($handler_id, $args, &$data)
    {
        $this->_request_data['message_obj'] = new org_openpsa_directmarketing_campaign_message($args[0]);
        $reply = new org_openpsa_helpers_ajax();
        $stat = $this->_request_data['message_obj']->send_status();
        if ($stat == false)
        {
            $reply->simpleReply(false, 'message->send_status returned false');
        }
        $members = $stat[0];
        $receipts = $stat[1];
        $reply->start();
            $reply->addTag('result', true);
            $reply->addTag('members', $members);
            $reply->addTag('receipts', $receipts);
        $reply->end();
        //This will exit
    }

}
?>