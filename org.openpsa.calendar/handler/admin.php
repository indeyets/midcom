<?php

/**
 * @package org.openpsa.calendar
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.46 2006/06/08 16:24:37 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.calendar site interface class.
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_handler_admin extends midcom_baseclasses_components_handler
{
    /**
     * Datamanager2 instance
     * 
     * @access private
     * @var midcom_helper_datamanager2_datamanager
     */
    var $_datamanager;
    
    /**
     * Constructor. Connect to the parent class constructor.
     */
    function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Handle the creation phase
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _handler_admin($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_request_data['view'] = $args[1];
        
        // Get the event
        $this->_event = new org_openpsa_calendar_event($args[0]);
        $data['event'] =& $this->_event;
        
        // This is a popup
        $_MIDCOM->skip_page_style = true;

        switch ($args[1])
        {
            case 'delete':
                return $this->_handler_delete($handler_id, $args, &$data);
                
            case 'edit':
                return $this->_handler_edit($handler_id, $args, &$data);
                
        }
        
        return false;

        // Check if the action is a valid one
        switch ($args[1])
        {
            case 'delete':
                $_MIDCOM->auth->require_do('midgard:delete', $this->_request_data['event']);

                $this->_request_data['delete_succeeded'] = false;
                if (array_key_exists('org_openpsa_calendar_deleteok', $_POST))
                {
                    $this->_request_data['delete_succeeded'] = $this->_request_data['event']->delete();
                    if ($this->_request_data['delete_succeeded'])
                    {
                        $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.calendar'), $this->_request_data['l10n']->get('event deleted'), 'ok');
                        // Close the popup and refresh main calendar
                        $_MIDCOM->add_jsonload('window.opener.location.reload();window.close();');
                    } else {
                        // Failure, give a message
                        $_MIDCOM->uimessages->add($this->_request_data['l10n']->get('org.openpsa.calendar'), $this->_request_data['l10n']->get('failed to delete event, reason ') . mgd_errstr(), 'error');
                    }
                    // Update the index
                    $indexer =& $_MIDCOM->get_service('indexer');
                    $indexer->delete($this->_request_data['event']->guid);
                }
                else
                {
                    $this->_view_toolbar->add_item
                    (
                        array
                        (
                            MIDCOM_TOOLBAR_URL => 'event/'.$this->_request_data['event']->id.'/',
                            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get('cancel'),
                            MIDCOM_TOOLBAR_HELPTEXT => null,
                            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/cancel.png',
                            MIDCOM_TOOLBAR_ENABLED => true,
                        )
                    );
                }
                debug_pop();
                return true;
            case 'edit':
                //debug_add("got POST\n===\n" .  sprint_r($_POST) . "===\n");
                $_MIDCOM->auth->require_do('midgard:update', $this->_request_data['event']);

                switch ($this->_datamanager->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_view = 'default';

                        // Add toolbar items
                        org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);

                        debug_pop();
                        return true;

                    case MIDCOM_DATAMGR_SAVED:
                        $indexer =& $_MIDCOM->get_service('indexer');
                        $indexer->index($this->_datamanager);

                        $this->_view = 'default';
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . 'event/' . $this->_request_data['event']->id. '/?reload=1');
                        // This will exit()

                    case MIDCOM_DATAMGR_CANCELLED:
                        $this->_view = 'default';
                        debug_pop();
                        $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX)
                            . 'event/' . $this->_request_data['event']->id. '/?reload=1');
                        // This will exit()

                    case MIDCOM_DATAMGR_FAILED:
                        if (is_array( $this->_request_data['event']->busy_em))
                        {
                            debug_add('resource conflict hooked, handling it');
                            // Add toolbar items
                            org_openpsa_helpers_dm_savecancel($this->_view_toolbar, $this);
                            $this->_event_resourceconflict_messages(&$this->_request_data['event']);
                            debug_pop();
                            return true;
                        }
                        else
                        {
                            //Some other error, raise hell.
                            $this->errstr = 'Datamanager failed: ' . $GLOBALS['midcom_errstr'];
                            $this->errcode = MIDCOM_ERRCRIT;
                            debug_pop();
                            return false;
                        }
                }
                debug_pop();
                return true;
            default:
                debug_pop();
                return false;
        }
        debug_pop();
    }
    
    /**
     * Show the requested administrator view
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array &$data          Public request data, passed by reference
     */
    function _show_admin($handler_id, &$data)
    {
        switch ($this->_request_data['view'])
        {
            case 'edit':
                $this->_show_edit($handler_id, &$data);
                break;
            
            case 'delete':
                $this->_show_delete($handler_id, &$data);
                break;
            
            case 'conflict_handler':
                $this->_request_data['popup_title'] = 'resource conflict';
                midcom_show_style('show-popup-header');
                $this->_request_data['event_dm'] =& $this->_datamanager;
                midcom_show_style('show-event-conflict');
                midcom_show_style('show-popup-footer');
            break;
        }
    }

    /**
     * Handle the editing phase
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _handler_edit($handler_id, $args, &$data)
    {
        $this->_event->require_do('midgard:edit');
        
        // Load the controller
        $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
        $this->_controller->schemadb =& $this->_request_data['schemadb'];
        $this->_controller->set_storage($this->_event);
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 controller instance for article {$this->_article->id}.");
            // This will exit.
        }
        
        switch ($this->_controller->process_form())
        {
            case 'save':
            case 'cancel':
                $_MIDCOM->relocate("event/{$this->_event->id}/");
                // This will exit
        }
        
        return true;
    }
    
    /**
     * Show event editing interface
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array &$data          Public request data, passed by reference
     */
    function _show_edit($handler_id, &$data)
    {
        // Set title to popup
        $this->_request_data['popup_title'] = sprintf($this->_request_data['l10n']->get('edit %s'), $this->_request_data['event']->title);

        // Show popup
        midcom_show_style('show-popup-header');
        $this->_request_data['event_dm'] =& $this->_controller;
        midcom_show_style('show-event-edit');
        midcom_show_style('show-popup-footer');
    }
    
    /**
     * Handle the delete phase
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array $args           Variable arguments
     * @param array &$data          Public request data, passed by reference
     * @return boolean              Indicating success
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_event->require_do('midgard:delete');
        $this->_request_data['delete_succeeded'] = false;
        
        // Cancel pressed
        if (isset($_POST['org_openpsa_calendar_delete_cancel']))
        {
            $_MIDCOM->relocate("event/{$this->_event->id}/");
            // This will exit
        }
        
        // Delete confirmed, remove the event
        if (isset($_POST['org_openpsa_calendar_deleteok']))
        {
            $this->_request_data['delete_succeeded'] = true;
            $this->_event->delete();
        }
        
        return true;
    }
    
    /**
     * Show event delete interface
     * 
     * @access public
     * @param String $handler_id    Name of the request handler
     * @param array &$data          Public request data, passed by reference
     */
    function _show_delete($handler_id, &$data)
    {
        // Set title to popup
        if ($this->_request_data['delete_succeeded'])
        {
            $this->_request_data['popup_title'] = sprintf($this->_request_data['l10n']->get('event %s deleted'), $this->_request_data['event']->title);
        }
        else
        {
            $this->_request_data['popup_title'] = $this->_request_data['l10n']->get('delete event');
        }
    
        // Show popup
        midcom_show_style('show-popup-header');
        $this->_request_data['event_dm'] =& $this->_datamanager;
        midcom_show_style('show-event-delete');
        midcom_show_style('show-popup-footer');
    }
}
?>