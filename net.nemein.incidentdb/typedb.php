<?php
/**
 * @package net.nemein.incidentdb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * IncidentDB Event type helper class
 * 
 * @todo document
 * 
 * @package net.nemein.incidentdb
 */
class net_nemein_incidentdb_typedb {
    
    var $_topic;
    var $_config;
    var $_auth;
    var $_root_event;
    var $_eventlist;
    
    var $_datamanager;
    
    function net_nemein_incidentdb_typedb ($topic, $config, $auth, $rootevent) {
        $this->_topic = $topic;
        $this->_config = $config;
        $this->_auth = $auth;
        $this->_root_event = $rootevent;
        
        $this->_datamanager = new midcom_helper_datamanager ($this->_config->get("schemadb"));
        
        if ($this->_datamanager === false) {
            debug_add("INCIDENTDB::TYPEDB Constructor: Could not create datamanager.");
            $this = false;
            return false;
        }
        
        $this->_eventlist = new net_nemein_incidentdb_eventlist ($this->_topic, 
                              $this->_config, $this->_auth, $this->_root_event, 
                              $this->get_types());
    }
    
    function get_types () {
        $schemadb = $this->_datamanager->get_layout_database();
        $result = Array();
        foreach ($schemadb as $key => $schema)
            $result[(int) $key] = $schema["description"];
        return $result;
    }
    
    function & get_eventlist_ref () { return $this->_eventlist; }
    
    
}

?>