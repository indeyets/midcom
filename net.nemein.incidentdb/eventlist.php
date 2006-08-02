<?php
/**
 * @package net.nemein.incidentdb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * IncidentDB Event management helper class
 * 
 * @todo document
 * 
 * @package net.nemein.incidentdb
 */
class net_nemein_incidentdb_eventlist {
    
    // Configuration
    var $_topic;
    var $_config;
    var $_auth;
    var $_root_event;
    var $_incident_types;
    
    // Current Query Settings
    var $_type;
    var $_start;
    var $_end;
    
    // Datamanger Instance
    var $_datamanager;
    
    function net_nemein_incidentdb_eventlist ($topic, $config, $auth, $rootevent, $types) {
        $this->_topic = $topic;
        $this->_config = $config;
        $this->_auth = $auth;
        $this->_root_event = $rootevent;
        $this->_incident_types = $types;
        
        $this->_datamanager = new midcom_helper_datamanager ($this->_config->get("schemadb"));
        
        if ($this->_datamanager === false) {
            debug_add("INCIDENTDB::EVENTLIST Constructor: Could not create datamanager.");
            $x =& $this;
            $x = false;
            return false;
        }
        
        $_type = null;
        $_start = null;
        $_end = null;
    }
    
    function set_type ($type) { 
        /* Typecast this so that we have a guranteed integer here, seems that this
         * could make trouble.
         * I hate PHP.
         */
        if (array_key_exists((int)$type, $this->_incident_types)) {
            $this->_type = $type; 
            return true;
        } else {
            $this->_type = null;
            debug_add("INCIDENTDB::EVENTLIST set_type: $type is unkown, setting type to NULL.");
            return false;
        }
    }
    
    function set_start ($start) { $this->_start = $start; }
    function set_end ($end)     { $this->_end = $end; }
    
    function get_type ()     { return $this->_type; }
    function get_start ()    { return $this->_start; }
    function get_end ()      { return $this->_end; }
    
    function query () {
        debug_push("incidentdb::eventlist query");
        $start = is_null($this->_start) ? 0 : $this->_start;
        $end = is_null($this->_end) ? 2147483647 : $this->_end;
        
        debug_add("Will query all events from $start to $end for " 
                  . (is_null($this->_type) ? "all types" : "type $this->_type" ) );
        
        if (is_null($this->_type))
            $result = mgd_list_events_between ($this->_root_event->id, $start, $end, "start");
        else
            $result = mgd_list_events_between ($this->_root_event->id, $start, $end, "start",
                        $this->_type);
        
        if (! $result) {
            debug_add("Did not found any events. mgd_errstr was " . mgd_errstr());
            debug_pop();
            return Array();
        }
        
        $res = Array();
        
        while ($result->fetch()) {
            $event = mgd_get_event ($result->id);
            
            /* check: person id
             * if we are manager, show all, else check wether the
             * event creator is the same as the current user.
             */
            $tmp = $this->_auth->get_person();
            $personid = $tmp->id;
            if ($this->_auth->is_manager() || $event->creator == $personid) {
                $res[$event->id] = $event;
                debug_print_r("Evend ID $event->id has been added as: ", $res[$event->id]);
            } else {
                debug_add("Skipping object, creator is $event->creator, we are $personid.");
                continue;
            }
        }
        
        debug_pop();
        return $res;
    }
    
    function create_new_incident ($type) {
        if (! $this->_auth->can_write()) {
            debug_add("INCIDENTDB::EVENTLIST create failed, we can't write.");
            return false;
        }
        $event = mgd_get_event();
        $event->owner = 0; // Inherit this
        $event->up = $this->_root_event->id;
        $event->type = $type;
        $id = $event->create();
        if (! $id) {
            debug_add("INCIDENTDB::EVENTLIST mgd_create faild: " . mgd_errstr());
            return false;
        } else {
            debug_add("INCIDETNDB::EVENTLIST created event id $id");
        }
        $event = mgd_get_event($id);
        $event->parameter("midcom.helper.datamanager", "layout", $type);
        return $event;
    }
    
    function & get_datamanager_for_incident (&$event) {
        if ($this->_datamanager->init($event)) {
            return $this->_datamanager;
        } else {
            return null;
        }
    }
}


?>