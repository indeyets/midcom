<?php
/**
 * @package org.openpsa.calendar
 * @author Nemein Oy, http://www.nemein.com/
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapper for org_openpsa_event with various helper functions
 * refactored from OpenPSA 1.x calander
 * @todo Figure out a good way to always use UTC for internal time storage
 * @package org.openpsa.calendar
 */
class midcom_org_openpsa_event extends __midcom_org_openpsa_event
{
    var $participants = array(); //list of participants (stored as eventmembers, referenced here for easier access)
    var $resources = array();    //like $participants but for resources.
    var $old_participants = array(); //as above, for diffs
    var $old_resources = array();    //ditto

        /* Tasks as resources are not handled yet at all but will be their own object similar to eventmember and we use similar cache strategy
      var $task_resources; //array, keys are GUIDs of tasks values true
        */
    /* Skip repeat handling for now
      var $repeat_rule;
      var $repeat_prev;  //GUID, For repeating events, previous event
      var $repeat_next;  //GUID, For repeating events, next event
      var $repeat_rule;  //Array, describes the repeat rules:
    */
/*                                * = mandatory key
                                  * ['type'], string: daily,weekly,monthly_by_dom
                                  * ['interval'], int: 1 means every day/week/monthday
                                  * ['from'], int: timestamp of date from which repeating starts (1 second after midnight)
                                    ['to'], int: timestamp of date to which the repeating ends (1 second before midnight)
                                    ['num'], int: how many occurences of repeat fit between from and to (mind the interval!)
                                    ['days'], array: keys are weekday numbers, values TRUE/FALSE

                                    It's mandatory to have 'to' or 'num' defined, the other can be calculated from the other,
                                    if both are defined 'to' has precedence.
*/

    var $externalGuid = '';    //vCalendar (or similar external source) GUID for this event (for vCalendar imports)
    var $old_externalGuid = '';    //as above, for diffs

    var $vCal_store = array(); //some vCal specific stuff
            /*
                        * unserialized from vCalSerialized
                        * not used for anything yet
            */

    var $busy_em = false; //In case of busy eventmembers this is an array
    var $busy_er = false; // In case of busy event resources this is an array
    var $_compatibility = array(); //Some compatibility switches, mainly for vCal imports
    var $_carried_participants_obj = array(); //Participants that were not added or removed in update
    var $_carried_resources_obj = array(); //Resources that were not added or removed in update

    var $send_notify = true; //Send notifications to participants of the event
    var $send_notify_me = false; //Send notification also to current user
    var $notify_force_add = false; //Used to work around DM creation features to get correct notification type out
    var $search_relatedtos = true;

    function midcom_org_openpsa_event($id = null)
    {
        return parent::__midcom_org_openpsa_event($id);
    }

    function get_parent_guid_uncached()
    {
        if (   array_key_exists('calendar_root_event', $GLOBALS['midcom_component_data']['org.openpsa.calendar'])
            && $this->id != $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->id)
        {
            return $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'];
        }
        else
        {
            return null;
        }
    }

    function _on_loaded()
    {
        $this->_unserialize_vcal();
        $l10n = $_MIDCOM->i18n->get_l10n('org.openpsa.calendar');

        // Check for empty title in existing events
        if (   $this->id
            && !$this->title) {
            //TODO: localization
            $this->title = $l10n->get('untitled');
        }

        // Preserve vCal GUIDs once set
        if (isset($this->externalGuid)) {
            $this->old_externalGuid = $this->externalGuid;
        }

        // Populates resources and participants list
        $this->_get_em();

        // Hide details if we're not allowed to see them
        if (!$_MIDCOM->auth->can_do('org.openpsa.calendar:read', $this))
        {
            // Hide almost all properties
            while (list($key, $value) = each($this))
            {
                switch ($key)
                {
                    case 'id':
                    case 'guid':
                    case 'start':
                    case 'end':
                    case 'resources':
                    case 'participants':
                    case 'orgOpenpsaAccesstype':
                        $this->$key = $value;
                        break;
                    case 'title':
                        $this->$key = $l10n->get('private event');
                        break;
                    default:
                        $this->$key = null;
                        break;
                }
            }
        }

        return true;
    }


    /**
     * Handles updates to repeating events
     */
    function update_repeat($handler='this')
    {
        //TODO: Re-implement
        return false;
    }

    /**
     * Check whether given user is participant in event
     */
    function is_participant($user=false)
    {
        if (!$user)
        {
            $user = $_MIDGARD['user'];
        }

        return array_key_exists($user, $this->participants);
    }

    /**
     * Check wheter current user can edit this event
     *
     * @todo Deprecate this in favor of direct ACL calls
     */
    function can_edit()
    {
        return $_MIDCOM->auth->can_do('midgard:update', $this);
    }

    /**
     * Check whether current user can view this event
     *
     * @todo Deprecate this in favor of direct ACL calls
     */
    function can_view()
    {
        return $_MIDCOM->auth->can_do('org.openpsa.calendar:read', $this);
    }

    /**
     * Fixes newline etc encoding issues in serialized data
     *
     * @param string $data The data to fix.
     * @return string $data with serializations fixed.
     */
    function _fix_serialization($data = null)
    {
        return org_openpsa_helpers_fix_serialization($data);
    }

    /**
     * Unserializes vCalSerialized to vCal_store
     */
    function _unserialize_vcal()
    {
        $unserRet = @unserialize($this->vCalSerialized);
        if ($unserRet === false)
        {
            //Unserialize failed (probably newline/encoding issue), try to fix the serialized string and unserialize again
            $unserRet = @unserialize($this->_fix_serialization($this->vCalSerialized));
            if ($unserRet === false)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add('Failed to unserialize vCalSerialized', MIDCOM_LOG_WARN);
                debug_pop();
                $this->vCal_store = array();
                return;
            }
        }
        $this->vCal_store = $unserRet;
    }

    /**
     * Serializes vCal_store to vCalSerialized
     */
    function _serialize_vcal()
    {
        //TODO: do not store those variables that are regenerated on runtime
/* copied from old, must be refactored
               //Do not store vCal variables that are properties of the event itself
               unset ($this->vCal_variables['DESCRIPTION'], $this->vCal_parameters['DESCRIPTION']);
               unset ($this->vCal_variables['SUMMARY'], $this->vCal_parameters['SUMMARY']);
               unset ($this->vCal_variables['LOCATION'], $this->vCal_parameters['LOCATION']);
               unset ($this->vCal_variables['DTSTART'], $this->vCal_parameters['DTSTART']);
               unset ($this->vCal_variables['DTEND'], $this->vCal_parameters['DTEND']);
               unset ($this->vCal_variables['CLASS'], $this->vCal_parameters['CLASS']);
               unset ($this->vCal_variables['STATUS'], $this->vCal_parameters['STATUS']);
               unset ($this->vCal_variables['TRANSP'], $this->vCal_parameters['TRANSP']);
*/
        $this->vCalSerialized = serialize($this->vCal_store);
    }

    /**
     * Preparations related to all save operations (=create/update)
     */
    function _prepare_save($ignorebusy_em = false, $rob_tentantive = false, $repeat_handler='this')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Starting preparations, this:\n---\n".sprint_r($this)."---", MIDCOM_LOG_DEBUG);

        // Make sure we have accessType
        if (!$this->orgOpenpsaAccesstype)
        {
            $this->orgOpenpsaAccesstype = ORG_OPENPSA_ACCESSTYPE_PUBLIC;
        }

        // Make sure we have objType
        /*if (!$this->orgOpenpsaObjtype)
        {
            $this->orgOpenpsaObjtype = ORG_OPENPSA_OBTYPE_EVENT;
        }*/

        //Force types
        $this->start = (int)$this->start;
        $this->end = (int)$this->end;
        if (   !$this->start
            || !$this->end)
        {
            debug_add('Event must have start and end timestamps');
            debug_pop();
            mgd_set_errno(MGD_ERR_RANGE);
            return false;
        }

        /*
         * Force start end end seconds to 1 and 0 respectively
         * (to avoid stupid one second overlaps)
         */
        $this->start = mktime(  date('G', $this->start),
                                date('i', $this->start),
                                1,
                                date('n', $this->start),
                                date('j', $this->start),
                                date('Y', $this->start));
        $this->end = mktime(date('G', $this->end),
                            date('i', $this->end),
                            0,
                            date('n', $this->end),
                            date('j', $this->end),
                            date('Y', $this->end));

        if ($this->end < $this->start)
        {
            debug_add('Event cannot end before it starts, aborting');
            debug_pop();
            return false;
        }

        //Check up
        if (!$this->up)
        {
            $this->up = (int)$GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event']->id;
        }
        //Doublecheck
        if (!$this->up)
        {
            debug_add('Event up not set, aborting');
            $this->errstr='Event UP not set';
            debug_pop();
            return false; //Calendar events must always be under some other event
        }

        //check for busy participants/resources
        if (   $this->busy_em($rob_tentantive)
            && !$ignorebusy_em)
        {
            debug_add("Unresolved resource conflicts, aborting, busy_em:\n---\n" . sprint_r($this->busy_em) . "---\n");
            $this->errstr='Resource conflict with busy event'; //': '.sprint_r($this->busy_em);
            debug_pop();
            return false;
        }
        else
        {
            $this->busy_em = false; //Make sure this is only present for the latest event op
        }

        /* placeholder so that I won't forget
        if (!(isset($this->_compatibility['times']['override_last-modified']) && $this->_compatibility['times']['override_last-modified']===TRUE))
        {
            $this->vCal_variables['LAST-MODIFIED']=$this->vCal_stamp(time(), array('TZID' => 'UTC')).'Z';
        }
        */

        /*
         * Calendar events always have 'inherited' owner
         * different bit buckets for calendar events might have different owners.
         */
        $this->owner = 0;

        //Preserve vCal GUIDs once set
        if (isset($this->old_externalGuid)) {
            $this->externalGuid=$this->old_externalGuid;
        }

        $this->_serialize_vcal();

        debug_add("Preparations done, this:\n---\n".sprint_r($this)."---", MIDCOM_LOG_DEBUG);

        debug_pop();
        return true;
    }

    //TODO: Move these options elsewhere
    function _on_creating($ignorebusy_em = false, $rob_tentantive = false, $repeat_handler='this')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$this->_prepare_save($ignorebusy_em, $rob_tentantive, $repeat_handler))
        {
            //Some requirement for an update failed, see $this->__errstr;
            debug_add('prepare_save failed, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }

    function _on_created()
    {
        $this->_get_em('old_');
        //TODO: handle the repeats somehow (if set)
        $this->_update_em();
        if ($this->search_relatedtos)
        {
            //TODO: add check for failed additions
            $this->get_suspected_task_links();
            $this->get_suspected_sales_links();
        }
        return true;
    }

    /**
     * Returns a defaults template for relatedto objects
     *
     * @return object org_openpsa_relatedto_relatedto
     */
    function _suspect_defaults()
    {
        $link_def = new org_openpsa_relatedto_relatedto();
        $link_def->fromComponent = 'org.openpsa.calendar';
        $link_def->fromGuid = $this->guid;
        $link_def->fromClass = get_class($this);
        $link_def->status = ORG_OPENPSA_RELATEDTO_STATUS_SUSPECTED;
        return $link_def;
    }

    /**
     * Queries org.openpsa.projects for suspected task links and saves them
     */
    function get_suspected_task_links()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //Safety
        if (!$this->_suspects_classes_present())
        {
            debug_add('required classes not present, aborting', MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }

        // Do not seek if we have only one participant (gives a ton of results, most of them useless)
        if (count($this->participants) < 2)
        {
            debug_add("we have less than two participants, skipping seek");
            debug_pop();
            return;
        }

        // Do no seek if we already have confirmed links
        $qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $qb->add_constraint('status', '=', ORG_OPENPSA_RELATEDTO_STATUS_CONFIRMED);
        $qb->add_constraint('fromGuid', '=',  $this->guid);
        $qb->add_constraint('fromComponent', '=',  'org.openpsa.calendar');
        $qb->add_constraint('toComponent', '=',  'org.openpsa.projects');
        $links = $qb->execute();
        if (!empty($links))
        {
            $cnt = count($links);
            debug_add("Found {$cnt} confirmed links already, skipping seek");
            debug_pop();
            return;
        }

        $link_def = $this->_suspect_defaults();
        $projects_suspect_links = org_openpsa_relatedto_suspect::find_links_object_component($this, 'org.openpsa.projects', $link_def);
        //debug_add("got suspected links:\n===\n" . sprint_r($projects_suspect_links) . "===\n");
        foreach ($projects_suspect_links as $linkdata)
        {
            debug_add("processing task/project #{$linkdata['other_obj']->id}, type: {$linkdata['other_obj']->orgOpenpsaObtype} (class: " . get_class($linkdata['other_obj']) . ")");
            //Only save links to tasks
            if ($linkdata['other_obj']->orgOpenpsaObtype == ORG_OPENPSA_OBTYPE_TASK)
            {
                $stat = $linkdata['link']->create();
                if ($stat)
                {
                    debug_add("saved link to task #{$linkdata['other_obj']->id} (link id #{$linkdata['link']->id})", MIDCOM_LOG_INFO);
                }
                else
                {
                    debug_add("could not save link to task #{$linkdata['other_obj']->id}, errstr" . mgd_errstr(), MIDCOM_LOG_WARN);
                }
            }
        }

        debug_pop();
        return;
    }

    /**
     * Check if we have necessary classes available to do relatedto suspects
     *
     * @return bool
     */
    function _suspects_classes_present()
    {
        if (   !class_exists('org_openpsa_relatedto_relatedto')
            || !class_exists('org_openpsa_relatedto_suspect'))
        {
            return false;
        }
        return true;
    }

    /**
     * Queries org.openpsa.sales for suspected task links and saves them
     */
    function get_suspected_sales_links()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('called');
        //Safety
        if (!$this->_suspects_classes_present())
        {
            debug_add('required classes not present, aborting', MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }

        // Do no seek if we already have confirmed links
        $qb = org_openpsa_relatedto_relatedto::new_query_builder();
        $qb->add_constraint('status', '=', ORG_OPENPSA_RELATEDTO_STATUS_CONFIRMED);
        $qb->add_constraint('fromGuid', '=',  $this->guid);
        $qb->add_constraint('fromComponent', '=',  'org.openpsa.calendar');
        $qb->add_constraint('toComponent', '=',  'org.openpsa.sales');
        $links = $qb->execute();
        if (!empty($links))
        {
            $cnt = count($links);
            debug_add("Found {$cnt} confirmed links already, skipping seek");
            debug_pop();
            return;
        }

        $link_def = $this->_suspect_defaults();
        $sales_suspect_links = org_openpsa_relatedto_suspect::find_links_object_component($this, 'org.openpsa.sales', $link_def);
        foreach ($sales_suspect_links as $linkdata)
        {
            debug_add("processing sales link {$linkdata['other_obj']->guid}, (class: " . get_class($linkdata['other_obj']) . ")");
            $stat = $linkdata['link']->create();
            if ($stat)
            {
                debug_add("saved link to {$linkdata['other_obj']->guid} (link id #{$linkdata['link']->id})", MIDCOM_LOG_INFO);
            }
            else
            {
                debug_add("could not save link to {$linkdata['other_obj']->guid}, errstr" . mgd_errstr(), MIDCOM_LOG_WARN);
            }
        }

        debug_add('done');
        debug_pop();
        return;
    }

    //TODO: move these options elsewhere
    function _on_updating($ignorebusy_em = false, $rob_tentantive = false, $repeat_handler='this')
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        //TODO: Handle repeats

        if (!$this->_prepare_save($ignorebusy_em, $rob_tentantive, $repeat_handler))
        {
            //Some requirement for an update failed, seee $this->__errstr;
            debug_add('prepare_save failed, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        //Reset these (in case we do multiple updates in a row, or something)
        $this->old_resources = array();
        $this->old_participants = array();
        //Get old resources and participants
        $this->_get_em('old_');

        /*
        debug_add("this->participants\n===\n" .  sprint_r($this->participants) . "===\n");
        debug_add("this->old_participants\n===\n" .  sprint_r($this->old_participants) . "===\n");
        debug_add("this->_carried_participants_obj\n===\n" .  sprint_r($this->_carried_participants_obj) . "===\n");
        */

        $this->_update_em($repeat_handler);
        //TODO: add check for failed removals/additions
        debug_pop();
        return true;
    }

    function _on_updated()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add("this->participants\n===\n" .  sprint_r($this->participants) . "===\n");
        debug_add("this->old_participants\n===\n" .  sprint_r($this->old_participants) . "===\n");
        debug_add("this->_carried_participants_obj\n===\n" .  sprint_r($this->_carried_participants_obj) . "===\n");

        if ($this->send_notify)
        {
            foreach ($this->_carried_participants_obj as $resObj)
            {
                debug_add("Notifying participant #{$resObj->id}");
                if ($this->notify_force_add)
                {
                    $resObj->notify('add', &$this);
                }
                else
                {
                    $resObj->notify('update', &$this);
                }
            }
            foreach ($this->_carried_resources_obj as $resObj)
            {
                debug_add("Notifying resource #{$resObj->id}");
                if ($this->notify_force_add)
                {
                    $resObj->notify('add', &$this);
                }
                else
                {
                    $resObj->notify('update', &$this);
                }
            }
        }

        // Handle ACL accordingly
        foreach ($this->participants as $person_id => $selected)
        {
            $user = $_MIDCOM->auth->get_user($person_id);

            // All participants can read and update
            $this->set_privilege('org.openpsa.calendar:read', $user->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:read', $user->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:update', $user->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:delete', $user->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:create', $user->id, MIDCOM_PRIVILEGE_ALLOW);
            $this->set_privilege('midgard:privileges', $user->id, MIDCOM_PRIVILEGE_ALLOW);
        }

        if ($this->orgOpenpsaAccesstype == ORG_OPENPSA_ACCESSTYPE_PRIVATE)
        {
            $this->set_privilege('org.openpsa.calendar:read', 'EVERYONE', MIDCOM_PRIVILEGE_DENY);
        }
        else
        {
            $this->set_privilege('org.openpsa.calendar:read', 'EVERYONE', MIDCOM_PRIVILEGE_ALLOW);
        }

        if ($this->search_relatedtos)
        {
            $this->get_suspected_task_links();
            $this->get_suspected_sales_links();
        }
        debug_pop();
        return true;
    }

    function _get_member_by_personid($id, $type='participant')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = org_openpsa_calendar_eventparticipant::new_query_builder();
        $qb->add_constraint('eid', '=', $this->id);
        $qb->add_constraint('uid', '=', $id);
        $results = $qb->execute_unchecked();
        debug_add("qb returned:\n===\n" . sprint_r($results) . "===\n");
        if (empty($results))
        {
            debug_pop();
            return false;
        }
        debug_pop();
        return $results[0];
    }

    function _get_member_by_resourceid($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb =  org_openpsa_calendar_event_resource_dba::new_query_builder();
        $qb->add_constraint('event', '=', $this->id);
        $qb->add_constraint('resource', '=', $id);
        $results = $qb->execute_unchecked();
        debug_add("qb returned:\n===\n" . sprint_r($results) . "===\n");
        if (empty($results))
        {
            debug_pop();
            return false;
        }
        debug_pop();
        return $results[0];
    }


    //TODO: move this option elsewhere
    function _on_deleting($repeat_handler='this')
    {
        //Remove participants
        reset ($this->participants);
        while (list ($id, $bool) = each ($this->participants))
        {
            $obj =  $this->_get_member_by_personid($id, 'participant');
            if (is_object($obj))
            {
                if (   $repeat_handler=='this'
                    && $this->send_notify)
                {
                    $obj->notify('cancel', &$this);
                }
                $obj->delete(false);
            }
        }

        //Remove resources
        reset ($this->resources);
        while (list ($id, $bool) = each ($this->resources))
        {
            $obj =  $this->_get_member_by_personid($id, 'resource');
            if (is_object($obj))
            {
                if (   $repeat_handler=='this'
                    && $this->send_notify)
                {
                    $obj->notify('cancel', &$this);
                }
                $obj->delete(false);
            }
        }

        //Remove event parameters
        mgd_delete_extensions($this);

        return true;
    }

    /**
     * Find event with arbitrary GUID either in externalGuid or guid
     */
    function search_vCal_uid($uid)
    {
        //TODO: MidCOM DBAize
        $qb = new MidgardQueryBuilder('org_openpsa_event');
        $qb->begin_group('OR');
            $qb->add_constraint('guid', '=', $uid);
            $qb->add_constraint('externalGuid', '=', $uid);
        $qb->end_group();
        $ret = @$qb->execute();
        if (   $ret
            && count($ret) > 0)
        {
            //It's unlikely to have more than one result and this should return an object (or false)
            return $ret[0];
        }
        else
        {
            return false;
        }
    }

      //TODO: Rewrite for QB
      function _search_person($uid, $param) { //Search persons.
                //Make sure we have our list and cache available
                if (!is_object($GLOBALS['org_openpsa_calendar_perListCache'])) {
                    $GLOBALS['org_openpsa_calendar_perListCache']=mgd_list_persons();
                    $GLOBALS['org_openpsa_calendar_perListCache_walked']=FALSE;
                }
                $lst=&$GLOBALS['org_openpsa_calendar_perListCache'];

                if (!is_array($GLOBALS['org_openpsa_calendar_perCache'])) {
                    $GLOBALS['org_openpsa_calendar_perCache']=array();
                }
                $cache=&$GLOBALS['org_openpsa_calendar_perCache'];

                //Really quick cache check
                if (isset($cache['uid'][$uid])) return $cache['uid'][$uid];

                list ($uid_type, $uid_value) = explode (":", $uid, 2);
                $uid_type=strtolower($uid_type);

                //Cache uid_type check.
                if (isset($cache[$uid_type][$uid_value])) {
                    $cahce['uid'][$uid]=&$cache[$uid_type][$uid_value];
                    return $cahce['uid'][$uid];
                }

                //Slow seek in persons list
                if (!$GLOBALS['org_openpsa_calendar_perListCache_walked'] && is_object($lst)) {
                    $brokenLoop=FALSE;
                    while ($lst->fetch()) {
                            if (!is_object($GLOBALS['NNCAL_PERSONSCACHE'][$lst->id])) {
                               $GLOBALS['NNCAL_PERSONSCACHE'][$lst->id]=mgd_get_person($lst->id);
                            }
                            $person=$GLOBALS['NNCAL_PERSONSCACHE'][$lst->id];
                            switch($uid_type) {
                                default:
                                case 'mailto':
                                    //echo "DEBUG-_search_person: person->email=$person->email, uid=$uid, uid_value=$uid_value <br>\n";
                                    //Check email match
                                    if ($person->email) {
                                        $person->email=strtolower($person->email);
                                        if ($person->email===strtolower($uid_value)) {
                                            //We have strong email match
                                            $cache['uid'][$uid]=$person->id;
                                            $cache['mailto'][$person->email]=&$cache['uid'][$uid];
                                            return $cache['uid'][$uid];
                                        }
                                     } else if (!$person->email && $person->name) {
                                        $person->email=preg_replace('/[^0-9_\x61-\x7a]/i','_',strtolower($person->name)).'_is_not@openpsa.org';
                                        //OpenPSA generated email "address" match, this is strong as well.
                                        if ($uid_value===$person->email) {
                                            $cache['uid'][$uid]=$person->id;
                                            $cache['mailto'][$uid_value]=&$cache['uid'][$uid];
                                            return $cache['uid'][$uid];
                                        }
                                    }
                                break;
                            }
                            //For further use pass email to cache
                            if ($person->email) {
                                $cache['mailto'][$person->email]=$person->id;
                            }

                            //Uid type checks inconclusive, check CN
                            if (isset($param['CN'])) {
                                //This has a problem with people that have same names...
                                if ($param['CN']===$person->name || $param['CN']===$person->rname) {
                                    $cache['cn'][$param['CN']]=$person->id;
                                    return $cache['cn'][$param['CN']];
                                }
                            }

                            //Put name to cache for further use.
                            if ($person->rname) $cache['cn'][$person->rname]=$person->id;
                            if ($person->name) $cache['cn'][$person->name]=&$cache['cn'][$person->rname];
                    }
                    if (!$brokenLoop) {
                        $GLOBALS['org_openpsa_calendar_perListCache_walked']=TRUE;
                    }
                }

                //We have checked the whole list for stronger CN/email matches, now we just have to check CN cache (not very reliable)
                if (isset($param['CN']) && isset($cache['cn'][$param['CN']])) {
                    return $cache['cn'][$param['CN']];
                }

        return FALSE;
      }

      //TODO: Rewrite for MgdSchema
      function _person_status($id) {
                global $midgard, $nemein_net;
                //Returns 'PARTICIPANT', 'RESOURCE' or 'CRM'
                if (mgd_is_member($nemein_net['group'], $id)) {
                    //Members of __Nemein_Net User must be participants
                    return 'PARTICIPANT';
                }

                if (!is_array($GLOBALS['org_openpsa_calendar_grpCache'])) {
                    $GLOBALS['org_openpsa_calendar_grpCache']=array();
                }
                $cache=&$GLOBALS['org_openpsa_calendar_grpCache'];

                $memLst=mgd_list_memberships($id);
                if ($memLst) {
                    while ($memLst->fetch()) {
                        if (!is_object($cache[$memLst->gid])) {
                            $cache[$memLst->gid]=mgd_get_group($memLst->gid);
                        }
                        $grp=&$cache[$memLst->gid];
                        while ($grp->up!==0) {
                            if ($grp->name==='__CRM') {
                                //While walking back the group tree found CRM root group
                                return 'CRM';
                            }
                            if ($grp->name==='__Calendar Resources') {
                                //While walking back the group tree found Calendar resources root group.
                                return 'RESOURCE';
                            }

                            if ($grp->up && !is_object($cache[$grp->up])) {
                                $cache[$grp->up]=mgd_get_group($grp->up);
                            }
                            $grp=&$cache[$grp->up];
                        }

                    }
                }

        return FALSE;
      }

/* still here to remind me of repeats
      function delete_old($rep_handler='this') {

               if ($this->repeat_next || $this->repeat_prev) {
                  if (!$rep_handler) {
                     return 0; //Require repeat handler when repeats are set
                  } else {
                     if ($this->repeat_prev) $prev_ev=new org_openpsa_calendar_Event($this->repeat_prev);
                     if ($this->repeat_next) $next_ev=new org_openpsa_calendar_Event($this->repeat_next);
                     switch ($rep_handler) {
                            case "reprule":
                            case "this":
                                 if ($prev_ev && $next_ev) {
                                    $prev_ev->repeat_next=$this->repeat_next;
                                    $prev_ev->save();
                                    $next_ev->repeat_prev=$this->repeat_prev;
                                    $next_ev->save();
                                 } else {
                                    if ($prev_ev) {
                                       $prev_ev->repeat_next='';
                                       $prev_ev->save();
                                    }
                                    if ($next_ev) {
                                       $next_ev->repeat_prev='';
                                       $next_ev->save();
                                    }
                                 }
                            break;
                            case "future":
                                 if ($prev_ev) {
                                    $prev_ev->repeat_next='';
                                    $prev_ev->save();
                                 }
                                 if ($next_ev) {
                                    $next_ev->repeat_prev='';
                                    $next_ev->delete('future');
                                 }
                            break;
                            case "past":
                                 if ($prev_ev) {
                                    $prev_ev->repeat_next='';
                                    $prev_ev->delete('past');
                                 }
                                 if ($next_ev) {
                                    $next_ev->repeat_prev='';
                                    $next_ev->save();
                                 }
                            break;
                            case "all":
                                 if ($prev_ev) {
                                    $prev_ev->repeat_next='';
                                    $prev_ev->delete('past');
                                 }
                                 if ($next_ev) {
                                    $next_ev->repeat_prev='';
                                    $next_ev->delete('future');
                                 }
                            break;
                     }
                  }
               }
        return $ret;
      }
*/

    function _busy_em_event_constraints(&$qb_ev, $fieldname = 'eid')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        /*
        debug_add("qb_ev \n===\n" .  sprint_r($qb_ev) . "===\n");
        debug_add("calling: \$qb_ev->add_constraint('{$fieldname}.busy', '<>', false)");
        */
        $qb_ev->add_constraint($fieldname . '.busy', '<>', false);
        if ($this->id)
        {
            $qb_ev->add_constraint($fieldname . '.id', '<>', (int)$this->id);
        }
        //Target event starts or ends inside this events window or starts before and ends after
        $qb_ev->begin_group('OR');
            $qb_ev->begin_group('AND');
                $qb_ev->add_constraint($fieldname . '.start', '>=', (int)$this->start);
                $qb_ev->add_constraint($fieldname . '.start', '<=', (int)$this->end);
            $qb_ev->end_group();
            $qb_ev->begin_group('AND');
                $qb_ev->add_constraint($fieldname . '.end', '<=', (int)$this->end);
                $qb_ev->add_constraint($fieldname . '.end', '>=', (int)$this->start);
            $qb_ev->end_group();
            $qb_ev->begin_group('AND');
                $qb_ev->add_constraint($fieldname . '.start', '<=', (int)$this->start);
                $qb_ev->add_constraint($fieldname . '.end', '>=', (int)$this->end);
            $qb_ev->end_group();
        $qb_ev->end_group();
        debug_pop();
    }
    /**
     * Check for potential busy conflicts to allow more gracefull handling of those conditions
     *
     * Also allows normal events to "rob" resources from tentative ones.
     * NOTE: return false for *no* (or resolved automatically) conflicts and true for unresolvable conflicts
     */
    function busy_em($rob_tentative=false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //If we're not busy it's not worth checking
        if (!$this->busy) {
            debug_add('we allow overlapping, so there is no point in checking others');
            debug_pop();
            return false;
        }
        //If this event is tentative always disallow robbing resources from other tentative events
        if ($this->tentative)
        {
            $rob_tentative = false;
        }
        //We need sudo to see busys in events we normally don't see and to rob resources from tentative events
        $_MIDCOM->auth->request_sudo();

        //Storage for events that have been modified due the course of this method
        $modified_events = array();

        /**
         * Look for duplicate events only if we have participants or resources, otherwise we incorrectly get all events at
         * the same timeframe as duplicates since there are no participant constraints to narrow things down
         */
        if (!empty($this->participants))
        {
            //We attack this "backwards" in the sense that in the end we need the events but this is faster way to filter them
            $qb_ev = org_openpsa_calendar_eventmember::new_query_builder();
            $this->_busy_em_event_constraints($qb_ev, 'eid');
            //Shared eventmembers
            $qb_ev->begin_group('OR');
                reset ($this->participants);
                foreach ($this->participants as $uid => $bool)
                {
                    $qb_ev->add_constraint('uid', '=', $uid);
                }
            $qb_ev->end_group();
            $ret_ev = $qb_ev->execute();
            unset($qb_ev);
        }
        else
        {
            $ret_ev = array();
        }

        // Shared resources need a separate check (different member object)
        if (!empty($this->resources))
        {
            $qb_ev2 = org_openpsa_calendar_event_resource_dba::new_query_builder();
            $this->_busy_em_event_constraints($qb_ev2, 'event');
            $qb_ev2->begin_group('OR');
                reset ($this->resources);
                foreach ($this->resources as $resource => $bool)
                {
                    $qb_ev2->add_constraint('resource', '=', $resource);
                }
            $qb_ev2->end_group();
            $ret_ev2 = $qb_ev2->execute();
            unset($qb_ev2);
        }
        else
        {
            $ret_ev2 = array();
        }

        // TODO: Shared tasks need a separate check (different member object)

        // Both QBs returned empty sets
        if (   (   !is_array($ret_ev)
                || count($ret_ev) === 0)
            && (   !is_array($ret_ev2)
                || count($ret_ev2) === 0)
            )
        {
            //No busy events found within the timeframe
            $_MIDCOM->auth->drop_sudo();
            debug_add('no overlaps found');
            debug_pop();
            return false;
        }

        //We might get multiple matches for same event/person
        $processed_events_participants = array();
        if (!is_array($ret_ev))
        {
            //Safety
            $ret_ev = array();
        }
        foreach ($ret_ev as $member)
        {
            //Check if we have processed this participant/event combination already
            if (   array_key_exists($member->eid, $processed_events_participants)
                && array_key_exists($member->uid, $processed_events_participants[$member->eid]))
            {
                continue;
            }
            if (   !array_key_exists($member->eid, $processed_events_participants)
                || !is_array($processed_events_participants[$member->eid]))
            {
                $processed_events_participants[$member->eid] = array();
            }
            $processed_events_participants[$member->eid][$member->uid] = true;

            $event = new org_openpsa_calendar_event($member->eid);
            debug_add("overlap found in event {$event->title} (#{$event->id})");

            if (   $event->tentative
                && $rob_tentative)
            {
                debug_add('event is tentative, robbing resources');
                //"rob" resources from tentative event
                $event = new org_openpsa_calendar_event($event->id);

                //participants
                reset($this->participants);
                foreach ($this->participants as $id => $bool)
                {
                    if (array_key_exists($id, $event->participants))
                    {
                        unset($event->participants[$id]);
                    }
                }
                $modified_events[$event->id] = $event;
            }
            else
            {
                debug_add('event is normal, flagging busy');
                //Non tentative event, flag busy resources
                if (!is_array($this->busy_em))
                {
                    //this is false under normal circumstances
                    $this->busy_em = array();
                }
                if (   !array_key_exists($member->guid, $this->busy_em)
                    || !is_array($this->busy_em[$member->uid]))
                {
                    //for mapping
                    $this->busy_em[$member->uid] = array();
                }
                //PONDER: The display end might have issues with event guid that they cannot see without sudo...
                $this->busy_em[$member->uid][] = $event->guid;
            }

        }

        //We might get multiple matches for same event/resource
        $processed_events_resources = array();
        if (!is_array($ret_ev2))
        {
            //Safety
            $ret_ev2 = array();
        }
        foreach ($ret_ev2 as $member)
        {
            //Check if we have processed this resource/event combination already
            if (   array_key_exists($member->event, $processed_events_resources)
                && array_key_exists($member->resource, $processed_events_resources[$member->event]))
            {
                continue;
            }
            if (   !array_key_exists($member->event, $processed_events_resources)
                || !is_array($processed_events_resources[$member->event]))
            {
                $processed_events_resources[$member->event] = array();
            }
            $processed_events_resources[$member->event][$member->resource] = true;

            if (array_key_exists($member->event, $modified_events))
            {
                $event =& $modified_events[$member->event];
                $set_as_modified = false;
            }
            else
            {
                $event = new org_openpsa_calendar_event($member->event);
                $set_as_modified = true;
            }
            debug_add("overlap found in event {$event->title} (#{$event->id})");

            if (   $event->tentative
                && $rob_tentative)
            {
                debug_add('event is tentative, robbing resources');
                //"rob" resources from tentative event
                $event = new org_openpsa_calendar_event($event->id);

                //resources
                reset($this->resources);
                foreach ($this->resources as $id => $bool)
                {
                    if (array_key_exists($id, $event->resources))
                    {
                        unset($event->resources[$id]);
                    }
                }
                if ($set_as_modified)
                {
                    $modified_events[$event->id] = $event;
                }
            }
            else
            {
                debug_add('event is normal, flagging busy');
                //Non tentative event, flag busy resources
                if (!is_array($this->busy_er))
                {
                    //this is false under normal circumstances
                    $this->busy_er = array();
                }
                if (   !array_key_exists($member->guid, $this->busy_er)
                    || !is_array($this->busy_er[$member->resource]))
                {
                    //for mapping
                    $this->busy_er[$member->resource] = array();
                }
                //PONDER: The display end might have issues with event guid that they cannot see without sudo...
                $this->busy_er[$member->resource][] = $event->guid;
            }

        }

        if (   is_array($this->busy_em)
            || is_array($this->busy_er))
        {
            //Unresolved conflicts (note return value is for conflicts not lack of them)
            $_MIDCOM->auth->drop_sudo();
            debug_add('unresolvable conflicts found, returning true');
            debug_pop();
            mgd_set_errno(MGD_ERR_ERROR);
            return true;
        }

        foreach($modified_events as $event)
        {
            //These events have been robbed of (some of) their resources
            if (   (   count($event->participants)==0
                    || (   count($event->participants)==1
                        && array_key_exists($event->creator, $event->participants)
                        )
                    )
                &&  count($event->resources)==0)
            {
                /* If modified event has no-one or only creator as participant and no resources
                   then delete it (as it's unlikely the stub event is usefull anymore) */
                debug_add("event {$event->title} (#{$event->id}) has been robbed of all of it's resources, calling delete");
                //TODO: take notifications and repeats into account
                $event->delete();
            }
            else
            {
                //Otherwise just commit the changes
                //TODO: take notifications and repeats into account
                debug_add("event {$event->title} (#{$event->id}) has been robbed of some it's resources, calling update");
                $event->update();
            }
        }

        $_MIDCOM->auth->drop_sudo();
        //No conflicts found or they could be automatically resolved
        $this->busy_em = false;
        $this->busy_er = false;
        debug_pop();
        return false;
    }


    /**
     * Fills $this->participants and $this->resources
     */
    function _get_em($prefix='')
    {
        if (!$this->id)
        {
            return;
        }
        //Make sure $prefix has an acceptable value
        switch ($prefix)
        {
            case 'old_':
            break;
            default:
                $prefix='';
            break;
        }

        //Create shorthand references to the arrays wanted
        $partVar = $prefix.'participants';
        $part =& $this->$partVar;
        $resVar = $prefix.'resources';
        $res =& $this->$resVar;

        //Reset to empty arrays
        $res = array();
        $part = array();

        // Participants
        $qb = new MidgardQueryBuilder('org_openpsa_eventmember');
        $qb->add_constraint('eid', '=', $this->id);
        $ret = $qb->execute();
        if (   is_array($ret)
            && count($ret)>0)
        {
            foreach ($ret as $member)
            {
                $part[$member->uid] = true;
            }
        }
        // Resources
        //mgd_debug_start();
        $qb2 = new MidgardQueryBuilder('org_openpsa_calendar_event_resource');
        $qb2->add_constraint('event', '=', $this->id);
        $ret2 = $qb2->execute();
        //mgd_debug_stop();
        if (   is_array($ret2)
            && count($ret2)>0)
        {
            foreach ($ret2 as $member)
            {
                $res[$member->resource] = true;
            }
        }
        return true;
    }

    function resource_diff($arr1, $arr2)
    {
        reset($arr1);
        reset($arr2);
        $ret = array();
        foreach($arr1 as $key => $value)
        {
            if (!array_key_exists($key, $arr2))
            {
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    function resource_intersect($arr1, $arr2)
    {
        reset($arr1);
        reset($arr2);
        $ret = array();
        foreach($arr1 as $key => $value)
        {
            if (array_key_exists($key, $arr2))
            {
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    /**
     * Creates/removes eventmembers based on $this->resources and $this->participants
     */
    function _update_em($repeat_handler = 'this')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //There is probably a better way...
        if ($repeat_handler != 'this')
        {
            $this->send_notify = false;
        }

        $ret = array();
        $ret['resources'] = array();
        $ret['resources']['added'] = array();
        $ret['resources']['removed'] = array();
        $ret['participants'] = array();
        $ret['participants']['added'] = array();
        $ret['participants']['removed'] = array();
        if (!is_array($this->resources))
        {
            $this->resources = array();
        }
        if (!is_array($this->participants))
        {
            $this->participants = array();
        }
        if (!is_array($this->old_resources))
        {
            $this->old_resources = array();
        }
        if (!is_array($this->old_participants))
        {
            $this->old_participants = array();
        }

        // ** Start with resources
        $added_resources = $this->resource_diff($this->resources, $this->old_resources);
        $removed_resources = $this->resource_diff($this->old_resources, $this->resources);
        $carried_resources = $this->resource_intersect($this->resources, $this->old_resources);

        foreach ($added_resources as $resourceId => $bool)
        {
            $resObj = new org_openpsa_calendar_event_resource_dba();
            $resObj->resource = $resourceId;
            $resObj->event = $this->id;
            $ret['resources']['added'][$resObj->resource] = $resObj->create($this->send_notify, &$this);
        }

        foreach ($removed_resources as $resourceId => $bool)
        {

            $resObj = $this->_get_member_by_resourceid($resourceId);
            if (is_object($resObj))
            {
                $ret['resources']['removed'][$resObj->resource] = $resObj->delete($this->send_notify, &$this);
                // TODO: Remove ACL permissions from removed members
            }
        }

        // Make sure we can read the carried objects
        $_MIDCOM->auth->request_sudo();
        foreach ($carried_resources as $resourceId => $bool)
        {
            $resObj = $this->_get_member_by_resourceid($resourceId);
            if (!is_object($resObj))
            {
                debug_add("Failed to get resource object for  #{$resourceId}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                continue;
            }
            $this->_carried_resources_obj[] = $resObj;
        }
        $_MIDCOM->auth->drop_sudo();
        // ** Done with resources

        // ** Start with participants
        $added_participants = $this->resource_diff($this->participants, $this->old_participants);
        $removed_participants = $this->resource_diff($this->old_participants, $this->participants);
        $carried_participants = $this->resource_intersect($this->participants, $this->old_participants);

        foreach ($added_participants as $participantId => $bool)
        {
            $resObj = new org_openpsa_calendar_eventparticipant();
            $resObj->uid = $participantId;
            $resObj->eid = $this->id;
            $resObj->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_EVENTPARTICIPANT;
            $ret['participants']['added'][$resObj->uid] = $resObj->create($this->send_notify, &$this);
        }

        foreach ($removed_participants as $participantId => $bool)
        {
            $resObj = $this->_get_member_by_personid($participantId);
            if (!is_object($resObj))
            {
                debug_add("Failed to get participant object for person #{$participantId}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                continue;
            }
            $ret['participants']['removed'][$resObj->uid] = $resObj->delete($this->send_notify, &$this);
        }

        // Make sure we can read the carried objects
        $_MIDCOM->auth->request_sudo();
        foreach ($carried_participants as $participantId => $bool)
        {
            $partObj = $this->_get_member_by_personid($participantId);
            if (!is_object($partObj))
            {
                debug_add("Failed to get participant object for person #{$participantId}, errstr: " . mgd_errstr(), MIDCOM_LOG_ERROR);
                continue;
            }
            $this->_carried_participants_obj[] = $partObj;
        }
        $_MIDCOM->auth->drop_sudo();
        // ** Done with participants
        /*
        debug_add("added_participants\n===\n" .  sprint_r($added_participants) . "===\n");
        debug_add("removed_participants\n===\n" .  sprint_r($removed_participants) . "===\n");
        debug_add("this->participants\n===\n" .  sprint_r($this->participants) . "===\n");
        debug_add("this->old_participants\n===\n" .  sprint_r($this->old_participants) . "===\n");
        debug_add("carried_participants\n===\n" .  sprint_r($carried_participants) . "===\n");
        debug_add("this->_carried_participants_obj\n===\n" .  sprint_r($this->_carried_participants_obj) . "===\n");
        */

        debug_add("returning:\n===\n" . sprint_r($ret) . "===\n");
        debug_pop();
        return $ret;
    }

    /**
     * gets person object from database id
     */
    function _pid_to_obj($pid)
    {
        return new midcom_baseclasses_database_person($pid);
    }

    /**
     *
     */
    function _pid_to_obj_cached($pid)
    {
        if (   !isset($GLOBALS['org_openpsa_event_pid_cache'])
            || !is_array($GLOBALS['org_openpsa_event_pid_cache']))
        {
            $GLOBALS['org_openpsa_event_pid_cache'] = array();
        }
        if (!isset($GLOBALS['org_openpsa_event_pid_cache'][$pid]))
        {
            $GLOBALS['org_openpsa_event_pid_cache'][$pid] = midcom_org_openpsa_event::_pid_to_obj($pid);
        }
        return $GLOBALS['org_openpsa_event_pid_cache'][$pid];
    }

    /**
     * Returns a string describing $this->start - $this->end
     */
    function format_timeframe()
    {
        //TODO: Make smarter
        return strftime('%c', $this->start) . ' - ' . strftime('%c', $this->end);
    }

    /**
     * Returns a string describing the event and it's participants
     */
    function details_text($display_title = true, $member = false, $nl = "\n")
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $l10n =& $_MIDCOM->i18n->get_l10n('org.openpsa.calendar');
        $str = '';
        if ($display_title)
        {
            $str .= sprintf($l10n->get('title: %s') . $nl, $this->title);
        }
        $str .= sprintf($l10n->get('location: %s') . $nl, $this->location);
        $str .= sprintf($l10n->get('time: %s') . $nl, $this->format_timeframe());
        $str .= sprintf($l10n->get('participants: %s') . $nl, $this->implode_members($this->participants));
        //Not supported yet
        //$str .= sprintf($l10n->get('resources: %s') . $nl, $this->implode_members($this->resources));
        //TODO: Tentative, overlaps, public
        $str .= sprintf($l10n->get('description: %s') . $nl, $this->description);
        debug_pop();
        return $str;
    }

    /**
     * Returns a comma separated list of persons from array
     */
    function implode_members($array)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!is_array($array))
        {
            debug_add('input was not an array, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $str = '';
        reset($array);
        $cnt = count ($array)-1;
        $i = 0;
        foreach($array as $pid => $bool)
        {
            $person =& org_openpsa_calendar_eventmember::get_person_obj_cache($pid);
            debug_add('pid: ' . $pid . ', person->id: ' . $person->id . ', person->firstname: ' . $person->firstname . ', person->lastname: ' . $person->lastname . ', person->name: ' . $person->name . ', person->rname: ' . $person->rname);
            $str .= $person->name;
            if ($i != $cnt)
            {
                $str .= ', ';
            }
            $i++;
        }
        debug_pop();
        return $str;
    }

    /**
     * Method for exporting event in vCalendar format
     *
     * @param string newline format, defaults to \r\n
     * @param array compatibility options to override
     * @return string vCalendar data
     */
    function vcal_export($nl = "\r\n", $compatibility = array())
    {
        $encoder = new org_openpsa_helpers_vxparser();
        $encoder->merge_compatibility($compatibility);

        // Simple key/value pairs, for multiple occurances of same key use array as value
        $vcal_keys = array();
        // For extended key data, like charset
        $vcal_key_parameters = array();

        // TODO: handle UID smarter
        $vcal_keys['UID'] = "{$this->guid}-midgardGuid";

        $vcal_keys['LAST-MODIFIED'] = $encoder->vcal_stamp($this->metadata->revised, array('TZID' => 'UTC')) . 'Z';
        // Difference between these two is very fuzzy
        $vcal_keys['DTSTAMP'] = $encoder->vcal_stamp($this->metadata->created, array('TZID' => 'UTC')) . 'Z';
        $vcal_keys['CREATED'] =& $vcal_keys['DTSTAMP'];
        // Type handling
        switch ($this->orgOpenpsaAccesstype)
        {
            case ORG_OPENPSA_ACCESSTYPE_PUBLIC:
                $vcal_keys['CLASS'] = 'PUBLIC';
                break;
            default:
            case ORG_OPENPSA_ACCESSTYPE_PRIVATE:
                $vcal_keys['CLASS'] = 'PRIVATE';
                break;
        }
        // "busy" or "transparency" as vCalendar calls it
        if ($this->busy)
        {
            $vcal_keys['TRANSP'] = 'OPAQUE';
        }
        else
        {
            $vcal_keys['TRANSP'] = 'TRANSPARENT';
        }
        // tentative vs confirmed
        $vcal_keys['STATUS'] = 'CONFIRMED';
        // we don't categorize events, at least yet
        $vcal_keys['CATEGORIES'] = 'MEETING';
        // we don't handle priorities
        $vcal_keys['PRIORITY'] = 1;
        // Basic fields
        $vcal_keys['SUMMARY'] = $encoder->escape_separators($this->title);
        $vcal_keys['DESCRIPTION'] = $encoder->escape_separators($this->description);
        $vcal_keys['LOCATION'] = $encoder->escape_separators($this->location);
        // Start & End in UTC
        $vcal_keys['DTSTART'] = $encoder->vcal_stamp($this->start, array('TZID' => 'UTC')) . 'Z';
        $vcal_keys['DTEND'] = $encoder->vcal_stamp($this->end, array('TZID' => 'UTC')) . 'Z';
        // Participants
        $vcal_keys['ATTENDEE'] = array();
        $vcal_key_parameters['ATTENDEE'] = array();
        foreach ($this->participants as $uid => $bool)
        {
            // Just a safety
            if (!$bool)
            {
                continue;
            }
            $person = midcom_org_openpsa_event::_pid_to_obj_cached($uid);
            if (empty($person->email))
            {
                // Attendee must have email address of valid format, these must also be unique.
                $person->email = preg_replace('/[^0-9_\x61-\x7a]/i','_', strtolower($person->name)) . '_is_not@openpsa.org';
            }
            $vcal_keys['ATTENDEE'][] = "mailto:{$person->email}";
            $vcal_key_parameters['ATTENDEE'][] = array(
                    'ROLE' => 'REQ-PARTICIPANT',
                    'CUTYPE' => 'INVIDUAL',
                    'STATUS' => 'ACCEPTED',
                    'CN' => $encoder->escape_separators($person->rname, true),
                );
        }
        $ret = "BEGIN:VEVENT{$nl}";
        $ret .= $encoder->export_vx_variables_recursive($vcal_keys, $vcal_key_parameters, false, $nl);
        $ret .= "END:VEVENT{$nl}";
        return $ret;
    }

    /**
     * Method for getting correct vcal file headers
     *
     * @param string method vCalendar method (defaults to "publish")
     * @param string newline format, defaults to \r\n
     * @return string vCalendar data
     */
    function vcal_headers($method="publish", $nl="\r\n")
    {
        $method = strtoupper($method);
        $ret = '';
        $ret .= "BEGIN:VCALENDAR{$nl}";
        $ret .= "VERSION:2.0{$nl}";
        $ret .= "PRODID:-//Nemein/OpenPSA2 Calendar V2.0.0//EN{$nl}";
        $ret .= "METHOD:{$method}{$nl}";
        //TODO: Determine server timezone and output correct header (we still send all times as UTC)
        return $ret;
    }

    /**
     * Method for getting correct vcal file footers
     *
     * @param string newline format, defaults to \r\n
     * @return string vCalendar data
     */
    function vcal_footers($nl="\r\n")
    {
        $ret = '';
        $ret .= "END:VCALENDAR{$nl}";
        return $ret;
    }

}

/**
 * Anothet wrap level
 * @package org.openpsa.calendar
 */
class org_openpsa_calendar_event extends midcom_org_openpsa_event
{
    function org_openpsa_calendar_event($identifier = NULL)
    {
        return parent::midcom_org_openpsa_event($identifier);
    }
}


?>