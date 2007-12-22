<?php

$_MIDCOM->componentloader->load('org.openpsa.calendar');

/**
 * MidCOM wrapper for org_openpsa_event with various helper functions
 * refactored from OpenPSA 1.x calander
 * TODO: Figure out a good way to always use UTC for internal time storage
 * @package org.openpsa.calendar
 */
class midcom_org_maemo_event extends midcom_org_openpsa_event
{
    var $participants = array(); //list of participants (stored as eventmembers, referenced here for easier access)
    var $old_participants = array(); //as above, for diffs

    function midcom_org_maemo_event($id = null)
    {
        return parent::midcom_org_openpsa_event($id);
    }

    function _on_loaded()
    {
        return parent::_on_loaded();
    }

    /**
     * Preparations related to all save operations (=create/update)
     */
    function _prepare_save($ignorebusy_em = false, $rob_tentantive = false, $repeat_handler='this')
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Starting maemo preparations, this:\n---\n".sprint_r($this)."---", MIDCOM_LOG_DEBUG);
        
        if (! is_array($this->participants))
        {
            $this->participants = unserialize($this->participants);
            if (! is_array($this->participants))
            {
                $this->participants = array();
            }
            
            $participants = array();
            foreach ($this->participants as $k => $person_id)
            {
                $participants[$person_id] = true;
            }
            $this->participants = $participants;
        }
        
        debug_add("Maemo preparations done, this:\n---\n".sprint_r($this)."---", MIDCOM_LOG_DEBUG);

        debug_pop();
        return parent::_prepare_save($ignorebusy_em, $rob_tentantive, $repeat_handler);
    }
    
    function return_as_dm2_hacked()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Starting dm2 hacking, this:\n---\n".sprint_r($this)."---", MIDCOM_LOG_DEBUG);
        
        if (is_array($this->participants))
        {
            $participants = array();
            foreach ($this->participants as $person_id => $enabled)
            {
                $participants[] = $person_id;
            }
            $this->participants = serialize($participants);
        }

        debug_add("DM2 hacking done, this:\n---\n".sprint_r($this)."---", MIDCOM_LOG_DEBUG);
        debug_pop();
        
        return $this;
    }
    
    function is_public()
    {
        return $this->orgOpenpsaAccesstype == ORG_OPENPSA_ACCESSTYPE_PUBLIC;
    }

}


/**
 * @package org.maemo.calendar
 */
class org_maemo_calendar_event extends midcom_org_maemo_event
{
    function org_maemo_calendar_event($identifier = NULL)
    {
        return parent::midcom_org_maemo_event($identifier);
    }
}
?>