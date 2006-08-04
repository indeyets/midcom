<?php
/**
 * OpenPSA group calendar
 * 
 * 
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_interface extends midcom_baseclasses_components_interface
{
    
    function org_openpsa_calendar_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'org.openpsa.calendar';
        $this->_autoload_class_definitions = array('midcom_dba_classes.inc');
        $this->_autoload_files = array(
            'admin.php',
            'calendar_midcomdba.php',
            'participant_midcomdba.php',
            'viewer.php',
            'navigation.php',
        );
        $this->_autoload_libraries = Array( 
            'midcom.helper.datamanager', 
            'org.openpsa.core', 
            'org.openpsa.mail', 
            'org.openpsa.helpers',
            'org.openpsa.calendarwidget',
            'org.openpsa.relatedto', 
            'org.openpsa.notifications', 
        );
        
        /* 
         * Calendar uses visibility permissions slightly differently than
         * midgard:read
         */
        $this->_acl_privileges['read'] = MIDCOM_PRIVILEGE_ALLOW;
    }


    function _on_initialize()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('Load contacts classes');
        $_MIDCOM->componentloader->load('org.openpsa.contacts');
        
        // Check for calendar event tree.
        $qb = org_openpsa_calendar_event::new_query_builder();
        $qb->add_constraint('title', '=', '__org_openpsa_calendar');
        $qb->add_constraint('up', '=', '0');
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'] = $ret[0];
        }
        else
        {
            debug_add("OpenPSA Calendar root event could not be found", MIDCOM_LOG_ERROR);
            //Attempt to auto-initialize
            $_MIDCOM->auth->request_sudo();
            $event = new midcom_db_event();
            $event->up = 0;
            $event->title = '__org_openpsa_calendar';
            $ret = $event->create();
            $_MIDCOM->auth->drop_sudo();
            if (!$ret)
            {
                $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'] = false;
                debug_add("Failed to create OpenPsa root event, reason ".mgd_errstr(), MIDCOM_LOG_ERROR);
                //If we return false here ACL editor etc will choke
                //return false;
            }
            else
            {
                $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'] = $event;
            }
        }
        
        debug_pop();
        return true;
    }

    /**
     * Iterate over all events and create index record using the datamanger indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_calendar_event');
        $qb->add_constraint('up', '=',  $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->id);
        $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
        if (   is_array($ret)
            && count($ret) > 0)
        {
            foreach ($ret as $event)
            {
                $datamanager = new midcom_helper_datamanager($config->get('schemadb'));
                if (! $datamanager)
                {
                    debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $this->_config->get('schemadb'),
                        MIDCOM_LOG_WARN);
                    continue;
                }
                
                if (! $datamanager->init($event))
                {
                    debug_add("Warning, failed to initialize datamanager for Event {$event->id}. See Debug Log for details.", MIDCOM_LOG_WARN);
                    debug_print_r('Event dump:', $event);
                    continue;
                }
                
                $indexer->index($datamanager);
                $datamanager->destroy();
            }
        }
        debug_pop();
    }
    
    /**
     * Returns string of JS code for opening the new event popup
     */
    function calendar_newevent_js($node, $start = false, $resource = false, $url_append = '')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!org_openpsa_calendar_interface::_popup_verify_node($node))
        {
            debug_pop();
            return false;
        }

        $height = $node[MIDCOM_NAV_CONFIGURATION]->get('calendar_popup_height');
        $width = $node[MIDCOM_NAV_CONFIGURATION]->get('calendar_popup_width');

        if (   $resource
            && $start)
        {
            $url = "{$node[MIDCOM_NAV_FULLURL]}event/new/{$resource}/{$start}.html";
        }
        else if ($resource)
        {
            $url = "{$node[MIDCOM_NAV_FULLURL]}event/new/{$resource}/";
        }
        else
        {
            $url = "{$node[MIDCOM_NAV_FULLURL]}event/new/";
        }
        $url .= $url_append;
        $js = "window.open('{$url}', 'newevent', '" . org_openpsa_calendar_interface::_js_window_options($height, $width) . "'); return false;";

        debug_pop();
        return $js;
    }

    /**
     * Returns string of JS code for opening the edit event popup
     *
     * PONDER: In theory we should be able to get the node with just the event guid ?
     */
    function calendar_editevent_js($event, $node)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!org_openpsa_calendar_interface::_popup_verify_node($node))
        {
            debug_pop();
            return false;
        }
        
        $height = $node[MIDCOM_NAV_CONFIGURATION]->get('calendar_popup_height');
        $width = $node[MIDCOM_NAV_CONFIGURATION]->get('calendar_popup_width');

        $js = "window.open('{$node[MIDCOM_NAV_FULLURL]}event/{$event}/', ";
        $js .= "'event_{$event}', '" . org_openpsa_calendar_interface::_js_window_options($height, $width) . "'); return false;";

        debug_pop();
        return $js;
    }
    
    /**
     * Returns string of correct window options for JS
     */
    function _js_window_options($height, $width)
    {
        $ret = "toolbar=0,";
        $ret .= "location=0,";
        $ret .= "status=0,";
        $ret .= "height={$height},";
        $ret .= "width={$width},";
        $ret .= "dependent=1,";
        $ret .= "alwaysRaised=1,";
        $ret .= "scrollbars=1,";
        $ret .= "resizable=1";
        return $ret;
    }
    
    /**
     * Verifies that given node has all we need to construct the popup
     */
    function _popup_verify_node($node)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !is_array($node)
            || !array_key_exists(MIDCOM_NAV_FULLURL, $node)
            || !array_key_exists(MIDCOM_NAV_CONFIGURATION, $node)
            || empty($node[MIDCOM_NAV_FULLURL])
            || empty($node[MIDCOM_NAV_CONFIGURATION]))
        {
            debug_add('given node is not valid', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }

    function _on_resolve_permalink($topic, $config, $guid)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $event = false;
        
        $event = new org_openpsa_calendar_event($guid);
        debug_add("event: ===\n" . sprint_r($event) . "===\n");

        switch (true)
        {
            case is_object($event):
                debug_pop();
                return "event/{$guid}/";
                break;
        }
        debug_pop();
        return null;
    }

}
?>