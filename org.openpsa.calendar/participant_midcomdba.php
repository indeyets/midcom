<?php
/**
 * Midcom wants this class present and QB etc use this, so keep logic here
 */
class midcom_org_openpsa_eventmember extends __midcom_org_openpsa_eventmember
{
    function midcom_org_openpsa_eventmember ($id = null)
    {
        return parent::__midcom_org_openpsa_eventmember($id);
    }

    /**
     * Wrapped so we can hook notifications
     */
    function create($notify=true, $event=false)
    {
        $ret = parent::create();
        if (   $ret
            && $notify)
        {
            $this->notify('add', $event);
        }
        return $ret;
    }

    /**
     * Wrapped so we can hook notifications
     */
    function update($notify=true, $event=false)
    {
        //It's unlikely we ever use this method for participants
        if ($notify)
        {
            $this->notify('update', $event);
        }
        return parent::update();
    }

    /**
     * Wrapped so we can hook notifications and also because current core doesn't support deletes
     */
    function delete($notify=true, $event=false)
    {
        if ($notify)
        {
            $this->notify('remove', $event);
        }
        return parent::delete();
    }
    
    /**
     * The subclasses need to override this method
     */
    function notify($repeat_handler='this', $event=false)
    {
        return false;
    }

    /**
     * return a given person object from cache or DB
     */
    function &get_person_obj_cache($id)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //If id is 0 or empty abort
        if (!$id)
        {
            debug_pop();
            return false;
        }
        
        //Get cached person object if present if not get from DB and cache
        if (!array_key_exists('persons_cache', $GLOBALS['midcom_component_data']['org.openpsa.calendar']))
        {
            $GLOBALS['midcom_component_data']['org.openpsa.calendar']['persons_cache'] = array();
        }
        if (!array_key_exists($id, $GLOBALS['midcom_component_data']['org.openpsa.calendar']['persons_cache']))
        {
            $GLOBALS['midcom_component_data']['org.openpsa.calendar']['persons_cache'][$id] = new org_openpsa_contacts_person($id);
        }
        $person =& $GLOBALS['midcom_component_data']['org.openpsa.calendar']['persons_cache'][$id];
        
        debug_pop();
        return $person;
    }
    
    /**
     * Returns the person this member points to if that person can be used for notifications
     */
    function &get_person_obj()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $person =& $this->get_person_obj_cache($this->uid);
        
        //We need to have an email which to send to so if no email no point
        if (empty($person->email))
        {
            debug_add('person #'. $person->id . 'has no email address, aborting');
            debug_pop();
            return false;
        }

        debug_pop();
        return $person;
    }
    
    /**
     * Returns the event this eventmember points to
     */
    function get_event_obj()
    {
        $event = new org_openpsa_calendar_event($this->eid);
        return $event;
    }

}

/**
 * Wrap level to get component namespaced class name
 */
class org_openpsa_calendar_eventmember extends midcom_org_openpsa_eventmember
{

    function _constructor($identifier)
    {
        return $this->org_openpsa_calendar_eventmember($identifier);
    }

    function org_openpsa_calendar_eventmember($identifier=NULL)
    {
        return parent::midcom_org_openpsa_eventmember($identifier);
    }

}

/**
 * Wrapping for special case participant
 */
class org_openpsa_calendar_eventparticipant extends org_openpsa_calendar_eventmember
{
    var $event;
    var $person;
    var $participant;
    
    function org_openpsa_calendar_eventparticipant($identifier=NULL) {
        if (parent::_constructor($identifier))
        {
            $this->event =& $this->eid;
            $this->participant =& $this->uid;
            $this->person =& $this->uid;
            if (!$this->orgOpenpsaObtype)
            {
                $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_EVENTPARTICIPANT;
            }
            return true;
        }
        return false;
    }
    
    function get_parent_guid_uncached()
    {
        if ($this->event)
        {
            $event = new org_openpsa_calendar_event($this->event);
            return $event;
        }
        else
        {
            return $GLOBALS['midcom_component_data']['org.openpsa.calendar']['calendar_root_event'];
        }
    }    
        
    //TODO: Rewrite
    function notify($type = 'update', $event = false, $nl = "\n") {
        debug_push_class(__CLASS__, __FUNCTION__);
        $l10n =& $_MIDCOM->i18n->get_l10n('org.openpsa.calendar');
        $recipient =& $this->get_person_obj();
        if (!$recipient)
        {
            debug_add('recipient could not be gotten, aborting', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        
        //In general we should have the event passed to us since we migt be notifying about changes that have not been committed yet
        if (!$event)
        {
            $event = $this->get_event_obj();
        }
        
        if (   ($recipient->id == $_MIDGARD['user'])
            && !$event->send_notify_me)
        {
            //Do not send notification to current user
            debug_add('event->send_notify_me is false and recipient is current user, aborting');
            debug_pop();
            return false;
        }
        
        $message = Array();
        $action = 'org.openpsa.calendar:noevent';
                
        switch ($type)
        {
            //Event information was updated
            case 'update':
                //PONDER: This in theory should have the old event title
                $action = 'org.openpsa.calendar:event_update';
                $message['subject'] = sprintf($l10n->get('event "%s" was updated'), $event->title);
                $message['body'] = sprintf($l10n->get('event "%s" was modified, updated information below.') . "{$nl}{$nl}", $event->title);
                $message['body'] .= $event->details_text(false, $this, $nl);
            break;
            //Participant was added to the event
            case 'add':
                $action = 'org.openpsa.calendar:event_add';
                $message['subject'] = sprintf($l10n->get('you have been added to event "%s"'), $event->title);
                $message['body'] = sprintf($l10n->get('you have been added to event "%s" participants list, event information below.') . "{$nl}{$nl}", $event->title);
                $message['body'] .= $event->details_text(false, $this, $nl);
            break;
            //Participant was removed from event
            case 'remove':
                $action = 'org.openpsa.calendar:event_remove';            
                $message['subject'] = sprintf($l10n->get('you have been removed from event "%s"'), $event->title);
                $message['body'] = sprintf($l10n->get('you have been removed from event "%s" (%s) participants list.'), $event->title, $event->format_timeframe());
            break;
            //Event was cancelled (=deleted)
            case 'cancel':
                $action = 'org.openpsa.calendar:event_cancel';
                $message['subject'] = sprintf($l10n->get('event "%s" was cancelled'), $event->title);
                $message['body'] = sprintf($l10n->get('event "%s" (%s) was cancelled.'), $event->title, $event->format_timeframe());
            break;
        }
        
        //TODO: attach vcal when we have working dumps

        return org_openpsa_notifications::notify($action, $recipient->guid, $message);
    }

// *** End class org_openpsa_calendar_eventparticipant ***
}

/**
 * Wrapper class for eventmembers as resources to an event.
 */
class org_openpsa_calendar_eventresource extends org_openpsa_calendar_eventmember {
    //More meaningfull aliases for some fields
    var $resource;
    var $event;
    var $notes;
      
    function org_openpsa_calendar_eventresource($identifier=NULL) {
        if (parent::_constructor($identifier))
        {
            $this->resource =& $this->uid;
            $this->event =& $this->eid;
            $this->notes =& $this->extra;
            if (!$this->orgOpenpsaObtype)
            {
                $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_EVENTRESOURCE;
            }
            return true;
        }
        return false;
    }

    function update($notify=true, $event=false)
    {
        if ($notify)
        {
            $this->notify('notesupdated', $event);
        }
        return parent::update();
    }

    //TODO: When we support resources again make a working notify method for them
    
//*** End class org_openpsa_calendar_eventresource ***
}
?>