<?php
/**
 * @package org.openpsa.calendar
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * OpenPSA group calendar
 *
 *
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_interface extends midcom_baseclasses_components_interface
{

    function __construct()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'org.openpsa.calendar';
        $this->_autoload_class_definitions = array('midcom_dba_classes.inc');
        $this->_autoload_files = array(
            'resource.php',
            'event_resource.php',
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
     * Iterate over all events and create index record using the datamanager indexer
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
        if (   is_object($event)
            && $event->id)
        {
            debug_pop();
            return "event/{$guid}/";
            break;
        }
        debug_pop();
        return null;
    }

    /**
     * Support for contacts person merge
     */
    function org_openpsa_contacts_duplicates_merge_person(&$person1, &$person2, $mode)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        switch($mode)
        {
            case 'all':
                break;
            /*
            case 'future':
                // Calendar should have future mode but we don't support it yet

                break;
            */
            default:
                // Mode not implemented
                debug_add("mode {$mode} not implemented", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
                break;
        }
        $qb = org_openpsa_calendar_eventmember::new_query_builder();
        // Make sure we stay in current SG even if we could see more
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        $qb->begin_group('OR');
            // We need the remaining persons memberships later when we compare the two
            $qb->add_constraint('uid', '=', $person1->id);
            $qb->add_constraint('uid', '=', $person2->id);
        $qb->end_group();
        $members = $qb->execute();
        if ($members === false)
        {
            // Some error with QB
            debug_add('QB Error', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Transfer memberships
        $membership_map = array();
        foreach ($members as $member)
        {
            if ($member->uid != $person1->id)
            {
                debug_add("Transferred event membership #{$member->id} to person #{$person1->id} (from #{$member->uid})");
                $member->uid = $person1->id;
            }
            if (   !isset($membership_map[$member->eid])
                || !is_array($membership_map[$member->eid]))
            {
                $membership_map[$member->eid] = array();
            }
            $membership_map[$member->eid][] = $member;
        }
        unset($members);
        // Merge memberships
        foreach ($membership_map as $eid => $members)
        {
            foreach ($members as $key => $member)
            {
                if (count($members) == 1)
                {
                    // We only have one membership in this event, skip rest of the logic
                    if (!$member->update())
                    {
                        // Failure updating member
                        debug_add("Failed to update eventmember #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                        debug_pop();
                        return false;
                    }
                    continue;
                }

                // TODO: Compare memberships to determine which of them are identical and thus not worth keeping

                if (!$member->update())
                {
                    // Failure updating member
                    debug_add("Failed to update eventmember #{$member->id}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
            }
        }

        // Transfer metadata dependencies from classes that we drive
        $classes = array(
            'org_openpsa_calendar_event',
            'org_openpsa_calendar_eventmember',
        );
        foreach($classes as $class)
        {
            if ($version_not_18 = true)
            {
                switch($class)
                {
                    default:
                        $metadata_fields = array(
                            'creator' => 'id',
                            'revisor' => 'id' // Though this will probably get touched on update we need to check it anyways to avoid invalid links
                        );
                        break;
                }
            }
            else
            {
                // TODO: 1.8 metadata format support
            }
            $ret = org_openpsa_contacts_duplicates_merge::person_metadata_dependencies_helper($class, $person1, $person2, $metadata_fields);
            if (!$ret)
            {
                // Failure updating metadata
                debug_add("Failed to update metadata dependencies in class {$class}, errsrtr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
        }
        debug_pop();
        return true;
    }

}
?>