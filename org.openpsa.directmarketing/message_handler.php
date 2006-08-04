<?php
/**
 * @package org.openpsa.directmarketing
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: message_handler.php,v 1.28 2006/07/06 15:48:43 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
 
/**
 * org.openpsa.directmarketing message handler and viewer class.
 */
class org_openpsa_directmarketing_message_handler
{
    var $_datamanagers;
    var $_request_data;
    var $_view = 'default'; 
    var $_config = null;
    var $_toolbars = null;
    
    function org_openpsa_directmarketing_message_handler(&$datamanagers, &$request_data, &$config)
    {
        $this->_datamanagers =& $datamanagers;
        $this->_request_data =& $request_data;
        $this->_config =& $config;
        $this->_toolbars = &midcom_helper_toolbars::get_instance();        
    }
    
    function _load($identifier)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $message = new org_openpsa_directmarketing_campaign_message($identifier);
        
        if (!is_object($message))
        {
            debug_add("Message object {$identifier} is not an object");
            debug_pop();
            return false;
        }
        
        // Load the message to datamanager
        if (!$this->_datamanagers['message']->init($message))
        {
            debug_add("Datamanager failed to handle message {$identifier}");
            debug_pop();
            return false;
        }
        debug_pop();
        return $message;
    }
    
    function get_css_class($type)
    {
        $class = 'email';
        switch ($type)
        {
            case ORG_OPENPSA_MESSAGETYPE_SMS:
            case ORG_OPENPSA_MESSAGETYPE_MMS:
                $class = 'mobile';
                break;
            case ORG_OPENPSA_MESSAGETYPE_CALL:
            case ORG_OPENPSA_MESSAGETYPE_FAX:
                $class = 'telephone';
                break;
            case ORG_OPENPSA_MESSAGETYPE_SNAILMAIL:
                $class = 'postal';
                break;
        }
        return $class;
    }
    
    function get_icon($type)
    {
        $icon = 'stock_mail.png';
        switch ($type)
        {
            case ORG_OPENPSA_MESSAGETYPE_SMS:
            case ORG_OPENPSA_MESSAGETYPE_MMS:
                $icon = 'stock_cell-phone.png';
                break;
            case ORG_OPENPSA_MESSAGETYPE_CALL:
            case ORG_OPENPSA_MESSAGETYPE_FAX:
                $icon = 'stock_landline-phone.png';
                break;
            case ORG_OPENPSA_MESSAGETYPE_SNAILMAIL:
                $icon = 'stock_home.png';
                break;
        }
        return $icon;
    }    

    function _creation_dm_callback(&$datamanager)
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        // This is what Datamanager calls to actually create a directory
        $result = array (
            "success" => false,
            "storage" => null,
        );

        $message = new org_openpsa_directmarketing_campaign_message();
        $message->campaign = $this->_request_data['campaign']->id;
        $message->orgOpenpsaObtype = $this->_request_data['message_type'];
        $stat = $message->create();
        if ($stat)
        {
            $this->_request_data['message'] = new org_openpsa_directmarketing_campaign_message($message->id);            
            $result["storage"] =& $this->_request_data['message'];
            $result["success"] = true;
            debug_pop();
            return $result;
        }
        debug_pop();
        return null;
    }   

    function _handler_new($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_request_data['campaign'] = $this->_request_data['campaign_handler']->_load($args[0]);
        /*
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'org_openpsa_directmarketing_campaign_message');
        */
        // PONDER: why is create granted on this object if user has general campaign create, so we check for update
        $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['campaign']);
        if (count($args) != 2)
        {
            debug_pop();
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Invalid number of arguments.");
            // This will exit        
            
        }

        
        if (!$this->_request_data['campaign'])
        {
            debug_pop();
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Failed to load campaign for the message.");
            // This will exit                
        }
        
        if (!array_key_exists($args[1], $this->_datamanagers['message']->_layoutdb))
        {
            debug_pop();
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Datamanager schema {$args[1]} not loaded.");
            // This will exit        
        }
        
        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get("create message"));
        
        $this->_request_data['message_type'] = $this->_datamanagers['message']->_layoutdb[$args[1]]['org_openpsa_directmarketing_messagetype'];
            
        if (!$this->_datamanagers['message']->init_creation_mode($args[1], $this, "_creation_dm_callback"))
        {
            debug_pop();
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanger in creation mode for schema 'default'.");
            // This will exit   
        }
        
        // Add toolbar items
        org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);
        
        switch ($this->_datamanagers['message']->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');
                break;
            
            case MIDCOM_DATAMGR_EDITING:
            case MIDCOM_DATAMGR_SAVED:            
                debug_add("First time submit, the DM has created an object");
                
                // Index the directory
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->index($this->_datamanagers['message']);
                                
                // Relocate to the new directory view
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . 'message/' . $this->_request_data['message']->guid. '/');
                break;
            
            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                    . "campaign/{$this->_request_data['campaign']->guid}/");
                // This will exit
            
            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;
            
            case MIDCOM_DATAMGR_FAILED:
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanger failed to process the request, see the Debug Log for details';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
            
            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;
            
        }
        
        debug_pop();
        return true;

    }

    function _show_new($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        $this->_request_data['message_dm'] = $this->_datamanagers['message'];
        midcom_show_style("show-message-new");
        debug_pop();
    }

    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $this->_request_data['list_type'] = false;
        
        if (count($args) == 2)
        {
            switch ($args[0])
            {
                case 'campaign':
                    $this->_request_data['campaign'] = $this->_request_data['campaign_handler']->_load($args[1]);
        
                    if ($this->_request_data['campaign'])
                    {
                        $this->_request_data['list_type'] = 'campaign';                    
                    }
                    break;
            }
        }
        
        if (!$this->_request_data['list_type'])
        {
            $GLOBALS['midcom']->generate_error(MIDCOM_ERRCRIT,
                "Wrong list type.");
            // This will exit          
        }
        
        return true;
    }

    function _show_list($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if ($this->_request_data['list_type'] == 'campaign')
        {
            debug_add("Instantiating Query Builder for creating message list");
            //$qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_directmarketing_campaign_message');
            $qb = new org_openpsa_qbpager('org_openpsa_directmarketing_campaign_message', 'campaign_messages');
            $qb->results_per_page = 10;
            $qb->add_order('created', 'DESC'); 
            $qb->add_constraint('campaign', '=', $this->_request_data['campaign']->id);
        
            debug_add("Executing Query Builder");        
            $ret = $qb->execute();
            $this->_request_data['qbpager'] =& $qb;
            midcom_show_style("show-message-list-header");
            if (count($ret) > 0)
            {     
                foreach ($ret as $message)
                {
                    $this->_request_data['message'] = $this->_load($message->guid);
                    $this->_request_data['message_array'] = $this->_datamanagers['message']->get_array();
                    $this->_request_data['message_class'] = $this->get_css_class($message->orgOpenpsaObtype);
                    midcom_show_style('show-message-list-item');
                }
            }
            midcom_show_style("show-message-list-footer");        
        }
        debug_pop();        
    }

    function _handler_send_bg($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->request_sudo();
        //Load message
        $this->_request_data['message'] = $this->_load($args[0]);
        if (!$this->_request_data['message'])
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
        $this->_request_data['batch_number'] = $args[1];
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
        
        $this->_request_data['message_array'] = $this->_datamanagers['message']->get_array();
        if (!array_key_exists('content', $this->_request_data['message_array']))
        {
            debug_add('"content" not defined in schema, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->_request_data['message_obj'] = new org_openpsa_directmarketing_campaign_message($args[0]);
        ignore_user_abort();
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->auth->drop_sudo();
        debug_pop();
        return true;
    }

    function _show_send_bg($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->request_sudo();
        debug_add('Forcing content type: text/plain');
        $_MIDCOM->cache->content->content_type('text/plain');
        $composed = $this->_prepare_send();
        $this->_request_data['message_obj']->test_mode = false;
        $this->_request_data['message_obj']->send_output = false;
        $bgstat = $this->_request_data['message_obj']->send_bg($this->_request_data['batch_url_base_full'], $this->_request_data['batch_number'], $composed, $this->_request_data['compose_from'], $this->_request_data['compose_subject'], $this->_request_data['message_array']);
        if (!$bgstat)
        {
            //TODO: echo some sort of error for the AT handler to catch (plaintext)
            echo "ERROR\n";
        }
        else
        {
            echo "Batch #{$this->_request_data['batch_number']} DONE\n";
        }
        $_MIDCOM->auth->drop_sudo();
        debug_pop();
    }

    function _handler_compose($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $_MIDCOM->auth->request_sudo();
        //Load message
        $this->_request_data['message'] = $this->_load($args[0]);
        if (!$this->_request_data['message'])
        {
            debug_pop();
            return false;
        }
        $this->_request_data['message_array'] = $this->_datamanagers['message']->get_array();
        if (!array_key_exists('content', $this->_request_data['message_array']))
        {
            debug_add('"content" not defined in schema, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $this->_request_data['message_obj'] = new org_openpsa_directmarketing_campaign_message($args[0]);
        //Substyle handling
        if (   array_key_exists('substyle', $this->_request_data['message_array'])
            && !empty($this->_request_data['message_array']['substyle'])
            && !preg_match('/^builtin:/', $this->_request_data['message_array']['substyle']))
        {
            debug_add("Appending substyle {$this->_request_data['message_array']['substyle']}");
            $_MIDCOM->substyle_append($this->_request_data['message_array']['substyle']);
        }
        //This isn't neccessary for dynamic-loading, but is nice for "preview".
        $_MIDCOM->skip_page_style = true;
        debug_add('message type: '.$this->_request_data['message_obj']->orgOpenpsaObtype);
        switch($this->_request_data['message_obj']->orgOpenpsaObtype)
        {
            case ORG_OPENPSA_MESSAGETYPE_EMAIL_TEXT:
            case ORG_OPENPSA_MESSAGETYPE_SMS:
                debug_add('Forcing content type: text/plain');
                $_MIDCOM->cache->content->content_type('text/plain');
            break;
            //TODO: Other content type overrides ?
        }
        debug_pop();
        $_MIDCOM->auth->drop_sudo();
        return true;
    }

    function _show_compose($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $prefix='';
        if (   array_key_exists('substyle', $this->_request_data['message_array'])
            && !empty($this->_request_data['message_array']['substyle'])
            && preg_match('/^builtin:(.*)/', $this->_request_data['message_array']['substyle'], $matches_style))
        {
            $prefix = $matches_style[1].'-';
        }
        debug_add("Calling midcom_show_style(\"compose-{$prefix}message\")");
        midcom_show_style("compose-{$prefix}message");
        debug_pop();
    }

    function _handler_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (!$this->_handler_view($handler_id, $args, &$data, false))
        {
            return false;
        }
        
        switch ($args[1])
        {
            case 'send_test':
                $this->_request_data['send_test_mode'] = true;
                $this->_view = 'send_test';
                //Fall-trough intentional
            case 'send':
                if (!isset($this->_request_data['send_test_mode']))
                {
                    $this->_request_data['send_test_mode'] = false;
                }
                $this->_toolbars->bottom->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "message/{$this->_request_data['message']->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("back"),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    )
                );  
                if ($this->_view == 'default')
                {
                    $this->_view = 'send';
                }
                
                $this->_request_data['send_go'] = true;
                //First try at delayed send as well
                if (isset($args[2]))
                {
                    $this->_request_data['delayed_send'] = true;
                    $this->_request_data['send_start'] = strtotime($args[2]);
                    if (   $this->_request_data['send_start'] == -1
                        || $this->_request_data['send_start'] === false)
                    {
                        //TODO: We should probably fail the send in stead of defaulting to immediate send
                        debug_add("Failed to parse \"{$args[2]}\" into timestamp");
                        $this->_request_data['send_start'] = time();
                        $this->_request_data['delayed_send'] = false;
                        //$this->_request_data['send_go'] = false;
                    }
                }
                else
                {
                    $this->_request_data['send_start'] = time();
                    $this->_request_data['delayed_send'] = false;
                }
                
                $this->_request_data['message_array'] = $this->_datamanagers['message']->get_array();
                if (!array_key_exists('content', $this->_request_data['message_array']))
                {
                    debug_add('"content" not defined in schema, aborting', MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                $this->_request_data['message_obj'] = new org_openpsa_directmarketing_campaign_message($args[0]);
                $this->_request_data['message_obj']->test_mode = $this->_request_data['send_test_mode'];
                ignore_user_abort();
                debug_pop();
                return true;
                break;
            case 'edit':
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['metadata']);
                    
                switch ($this->_datamanagers['message']->process_form()) 
                {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = "edit";

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_toolbars->bottom, $this);
                        
                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        // Update the Index 
                        $indexer =& $GLOBALS['midcom']->get_service('indexer');
                        $indexer->index($this->_datamanagers['message']);
                        
                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "message/{$this->_request_data['message']->guid}/");
                        // This will exit()

                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = "default";
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . "message/{$this->_request_data['message']->guid}/");
                        // This will exit()
                
                    case MIDCOM_DATAMGR_FAILED:
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        return false;
                }
                return true;
                break;
            case 'send_status':
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
                break;
        }
        
        debug_pop();    
        return false;
    }
    
    function _show_action($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        
        switch ($this->_view)
        {
            case 'edit':
                $this->_request_data['message_dm'] = $this->_datamanagers['message'];
                midcom_show_style("show-message-edit");
                break;
            case 'send_test':
                $composed = $this->_prepare_send();
                $this->_request_data['message_obj']->test_mode = true;
                $this->_request_data['message_obj']->send_output = true;
                $sendstat = $this->_request_data['message_obj']->send($composed, $this->_request_data['compose_from'], $this->_request_data['compose_subject'], $this->_request_data['message_array']);
                break;
            case 'send':
                $nap = new midcom_helper_nav();
                $node = $nap->get_node($nap->get_current_node());
                $this->_prepare_send();
                $this->_request_data['message_obj']->test_mode = false;
                $this->_request_data['message_obj']->send_output = false;
                $args = array(
                    'batch' => 1,
                    'url_base' => $this->_request_data['batch_url_base_full'],
                );
                if (!$this->_request_data['send_go'])
                {
                    //TODO: do something ! send aborted (mainly becaused delayed send timestamp could not be determined)
                }
                else
                {
                    debug_add('Registering background send job to start on: ' . date('Y-m-d H:i:s', $this->_request_data['send_start']));
                    midcom_services_at_interface::register($this->_request_data['send_start'], 'org.openpsa.directmarketing', 'background_send_message', $args);
                    midcom_show_style('send-start');
                }
                break;
            default:
                break;
        }
        debug_pop();
    }
    
    function _prepare_send()
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        $nap = new midcom_helper_nav();
        $node = $nap->get_node($nap->get_current_node());
        $this->_request_data['compose_url'] = $node[MIDCOM_NAV_RELATIVEURL] . 'message/compose/' . $this->_request_data['message_obj']->guid();
        $this->_request_data['batch_url_base_full'] = $node[MIDCOM_NAV_RELATIVEURL] . 'message/send_bg/' . $this->_request_data['message_obj']->guid();
        debug_add("compose_url: {$this->_request_data['compose_url']}");
        debug_add("batch_url base: {$this->_request_data['batch_url_base_full']}");
        ob_start();
        $_MIDCOM->dynamic_load($this->_request_data['compose_url']);
        $composed = ob_get_contents();
        ob_end_clean();
        //We force the content-type since the compositor might have set it to something else for preview purposes
        debug_add('Forcing content type: text/html');
        $_MIDCOM->cache->content->content_type('text/html');

        //PONDER: Should we leave these entirely for the methods to parse from the array ?
        $this->_request_data['compose_subject'] = '';
        $this->_request_data['compose_from'] = '';
        if (array_key_exists('subject', $this->_request_data['message_array']))
        {
            $this->_request_data['compose_subject'] = &$this->_request_data['message_array']['subject'];
        }
        if (array_key_exists('from', $this->_request_data['message_array']))
        {
            $this->_request_data['compose_from'] = &$this->_request_data['message_array']['from'];
        }
        
        //Get SMS/MMS settings from component configuration
        if ($smslib_api = $this->_config->get('smslib_api'))
        {
            $this->_request_data['message_obj']->sms_lib_api = $smslib_api;
        }
        if ($smslib_uri = $this->_config->get('smslib_uri'))
        {
            $this->_request_data['message_obj']->sms_lib_location = $smslib_uri;
        }
        else if ($email2sms_address = $this->_config->get('email2sms_address'))
        {
            $this->_request_data['message_obj']->sms_lib_location = $email2sms_address;
        }
        if ($smslib_client_id = $this->_config->get('smslib_client_id'))
        {
            $this->_request_data['message_obj']->sms_lib_client_id = $smslib_client_id;
        }
        if ($smslib_user = $this->_config->get('smslib_user'))
        {
            $this->_request_data['message_obj']->sms_lib_user = $smslib_user;
        }
        if ($smslib_password = $this->_config->get('smslib_password'))
        {
            $this->_request_data['message_obj']->sms_lib_password = $smslib_password;
        }

        if ($mail_send_backend = $this->_config->get('mail_send_backend'))
        {
            $this->_request_data['message_array']['mail_send_backend'] = $mail_send_backend;
        }
        if ($bouncer_address = $this->_config->get('bouncer_address'))
        {
            $this->_request_data['message_array']['bounce_detector_address'] = $bouncer_address;
        }
        if ($link_detector_address = $this->_config->get('linkdetector_address'))
        {
            $this->_request_data['message_array']['link_detector_address'] = $link_detector_address;
        }
        if ($token_size = $this->_config->get('token_size'))
        {
            $this->_request_data['message_obj']->token_size = $token_size;
        }

        
        debug_pop();
        return $composed;
    }


    function _handler_view($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);   

        // Get the requested document metadata object
        $this->_request_data['message'] = $this->_load($args[0]);
        if (!$this->_request_data['message'])
        {
            debug_pop();
            return false;
        }
        
        $_MIDCOM->set_pagetitle($this->_request_data['message']->title);
    
        // Add toolbar items
        if ($_MIDCOM->auth->can_do('midgard:update', $this->_request_data['message']))
        {
            // TODO: Edit button when editing is supported
            if (count($args) == 1)
            {
                $this->_toolbars->bottom->add_item(
                    Array(
                        MIDCOM_TOOLBAR_URL => "message/{$this->_request_data['message']->guid}/edit.html",
                        MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("edit"),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                        MIDCOM_TOOLBAR_ENABLED => true,
                    )
                );   
            }
        }
        if (count($args)==1)
        {
            $this->_toolbars->bottom->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "message/compose/{$this->_request_data['message']->guid}",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("preview message"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/view.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_OPTIONS => array('target' => '_BLANK'),
                )
            );  
            $this->_toolbars->top->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "message/{$this->_request_data['message']->guid}/send_test.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("send message to testers"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-send.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );  
        }
        // TODO: Check for sending privilege
        if (   !$this->_request_data['message']->sendCompleted
            && (count($args) == 1))
        {
            $this->_toolbars->top->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => "message/{$this->_request_data['message']->guid}/send.html",
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("send message to whole campaign"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-send.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                    MIDCOM_TOOLBAR_OPTIONS => array(
                            'onClick' => "return confirm('" . $this->_request_data['l10n']->get("are you sure you wish to send this to the whole campaign ?") . "')",
                        ),
                )
            );  
        }
    
        $this->_request_data['campaign'] = $this->_request_data['campaign_handler']->_load($this->_request_data['message']->campaign);
    
        debug_pop();
        return true;
    }

    function _show_view($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);    
        $this->_request_data['message_dm'] = $this->_datamanagers['message'];
        midcom_show_style("show-message");
        debug_pop();
    }


}
?>