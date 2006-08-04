<?php

/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: workflow_handler.php,v 1.3 2006/02/03 15:21:22 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.projects task handler and viewer class.
 */
class org_openpsa_projects_workflow_handler
{
    var $_datamanagers;
    var $_request_data;
    
    function org_openpsa_projects_workflow_handler(&$datamanagers, &$request_data)
    {
        $this->_datamanagers = &$datamanagers;
        $this->_request_data = &$request_data;
    }
    
    function _load_task($identifier)
    {
        $task = new org_openpsa_projects_task($identifier);
        
        if (!is_object($task))
        {
            return false;
        }
        
        // Load the task to datamanager
        if (!$this->_datamanagers['task']->init($task))
        {
            return false;
        }
        return $task;
    }
    
    function _handler_action($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        if (!isset($this->_request_data['action']))
        {
            $this->_request_data['action'] = $args[1];
        }
        if (!isset($this->_request_data['reply_mode']))
        {
            $this->_request_data['reply_mode'] = 'ajax';
        }
        $this->_request_data['task'] = $this->_load_task($args[0]);
        if (!$this->_request_data['task'])
        {
            $this->errstr = "Could not fetch task";
            $this->errcode = MIDCOM_ERRCRIT;
            return false;
        }
        
        switch($this->_request_data['action'])
        {
            case 'accept':
                return $this->_handle_accept();
                break;
            case 'decline':
                return $this->_handle_decline();
                break;
            case 'complete':
                return $this->_handle_complete();
                break;
            case 'approve':
                return $this->_handle_approve();
                break;
            case 'reject':
                return $this->_handle_reject();
                break;
            case 'remove_complete':
                return $this->_handle_remove_complete();
                break;
            case 'remove_approve':
                return $this->_handle_remove_approve();
                break;
            case 'close':
                return $this->_handle_close();
                break;
            case 'reopen':
                return $this->_handle_reopen();
                break;
            default:
                switch($this->_request_data['reply_mode'])
                {
                    case 'ajax':
                        //TODO: return ajax error
                    break;
                    default:
                    case 'redirect':
                        $this->errstr = "Method not implemented";
                        $this->errcode = MIDCOM_ERRCRIT;
                    break;
                }
                break;
        }
        
        //We should not fall this far trough
        return false;
    }
    
    function _handle_accept()
    {
        $stat = $this->_request_data['task']->accept();
        $errstr = mgd_errstr();
        switch($this->_request_data['reply_mode'])
        {
            case 'ajax':
                //TODO: return ajax status
            break;
            default:
            case 'redirect':
                if (!$stat)
                {
                    $this->errstr = "Error {$errstr} when saving";
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                return $this->_redirect();
                //This will exit
            break;
        }
    }

    function _handle_decline()
    {
        $stat = $this->_request_data['task']->decline();
        $errstr = mgd_errstr();
        switch($this->_request_data['reply_mode'])
        {
            case 'ajax':
                //TODO: return ajax status
            break;
            default:
            case 'redirect':
                if (!$stat)
                {
                    $this->errstr = "Error {$errstr} when saving";
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                return $this->_redirect();
                //This will exit
            break;
        }
    }

    function _handle_complete()
    {
        $stat = $this->_request_data['task']->complete();
        $errstr = mgd_errstr();
        switch($this->_request_data['reply_mode'])
        {
            case 'ajax':
                //TODO: return ajax status
            break;
            default:
            case 'redirect':
                if (!$stat)
                {
                    $this->errstr = "Error {$errstr} when saving";
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                return $this->_redirect();
                //This will exit
            break;
        }
    }

    function _handle_remove_complete()
    {
        $stat = $this->_request_data['task']->remove_complete();
        $errstr = mgd_errstr();
        switch($this->_request_data['reply_mode'])
        {
            case 'ajax':
                //TODO: return ajax status
            break;
            default:
            case 'redirect':
                if (!$stat)
                {
                    $this->errstr = "Error {$errstr} when saving";
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                return $this->_redirect();
                //This will exit
            break;
        }
    }

    function _handle_approve()
    {
        $stat = $this->_request_data['task']->approve();
        $errstr = mgd_errstr();
        switch($this->_request_data['reply_mode'])
        {
            case 'ajax':
                //TODO: return ajax status
            break;
            default:
            case 'redirect':
                if (!$stat)
                {
                    $this->errstr = "Error {$errstr} when saving";
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                return $this->_redirect();
                //This will exit
            break;
        }
    }

    function _handle_remove_approve()
    {
        $stat = $this->_request_data['task']->remove_approve();
        $errstr = mgd_errstr();
        switch($this->_request_data['reply_mode'])
        {
            case 'ajax':
                //TODO: return ajax status
            break;
            default:
            case 'redirect':
                if (!$stat)
                {
                    $this->errstr = "Error {$errstr} when saving";
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                return $this->_redirect();
                //This will exit
            break;
        }
    }

    function _handle_reject()
    {
        $stat = $this->_request_data['task']->reject();
        $errstr = mgd_errstr();
        switch($this->_request_data['reply_mode'])
        {
            case 'ajax':
                //TODO: return ajax status
            break;
            default:
            case 'redirect':
                if (!$stat)
                {
                    $this->errstr = "Error {$errstr} when saving";
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                return $this->_redirect();
                //This will exit
            break;
        }
    }

    function _handle_close()
    {
        $stat = $this->_request_data['task']->close();
        $errstr = mgd_errstr();
        switch($this->_request_data['reply_mode'])
        {
            case 'ajax':
                //TODO: return ajax status
            break;
            default:
            case 'redirect':
                if (!$stat)
                {
                    $this->errstr = "Error {$errstr} when saving";
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                return $this->_redirect();
                //This will exit
            break;
        }
    }

    function _handle_reopen()
    {
        $stat = $this->_request_data['task']->reopen();
        $errstr = mgd_errstr();
        switch($this->_request_data['reply_mode'])
        {
            case 'ajax':
                //TODO: return ajax status
            break;
            default:
            case 'redirect':
                if (!$stat)
                {
                    $this->errstr = "Error {$errstr} when saving";
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
                }
                return $this->_redirect();
                //This will exit
            break;
        }
    }
    
    function _redirect()
    {
        if (   !isset($this->_request_data['redirect_to'])
            || empty($this->_request_data['redirect_to']))
        {
            //Cannot redirect, throw error
        }
        $_MIDCOM->relocate($this->_request_data['redirect_to']);
        //This will exit
    }
    
    function _show_action($handler_id, &$data)
    {
        //We actually should not ever get this far
        return;
    }
    
    function _handler_post($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        //Look for action among POST variables, then load main handler...
        if (   !isset($_POST['org_openpsa_projects_workflow_action'])
            || !is_array($_POST['org_openpsa_projects_workflow_action'])
            || count($_POST['org_openpsa_projects_workflow_action'])==0)
        {
            //We do not have proper POST available, abort
            return false;
        }
        
        //Go trough the array, in theory it should have only one element and in any case only the last of them will be processed
        foreach ($_POST['org_openpsa_projects_workflow_action'] as $action => $val)
        {
            $this->_request_data['action'] = $action;
        }
        
        $this->_request_data['reply_mode'] = 'redirect';
        if (!isset($_POST['org_openpsa_projects_workflow_action_redirect']))
        {
            //NOTE: This might header not be trustworthy...
            $this->_request_data['redirect_to'] = $_SERVER['HTTP_REFERER'];
        }
        else
        {
            $this->_request_data['redirect_to'] = $_POST['org_openpsa_projects_workflow_action_redirect'];
        }
        return $this->_handler_action($handler_id, $args, $data);
    }
    
    function _show_post($handler_id, &$data)
    {
        //We actually should not ever get this far
        return;
    }
}
?>
