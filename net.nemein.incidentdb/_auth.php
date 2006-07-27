<?php
/**
 * @package net.nemein.incidentdb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * IncidentDB Authentication/Authorization helper class
 * 
 * @todo document
 * 
 * @package net.nemein.incidentdb
 */
class net_nemein_incidentdb__auth {
    
    var $_midgard;    
    var $_person;
    var $_mgrgroup;
    var $_rptgroup;
    var $_canwrite;
    var $_ismanager;
    var $_ispoweruser;

    var $_topic;
    var $_config;
    
    function net_nemein_incidentdb__auth ($topic, $config) {
        $this->_topic = $topic;
        $this->_config = $config;
        
        $this->_midgard = mgd_get_midgard();
        if (! $this->_midgard->user) {
            $GLOBALS["midcom"]->generate_error(MIDCOM_ERRFORBIDDEN, 
                "Authentication failed (Midgard Auth not enabled?)");
        }
        $this->_person = mgd_get_person($this->_midgard->user);
        $this->_mgrgroup = mgd_get_object_by_guid($this->_config->get("managergrpguid"));
        $this->_rptgroup = $this->_get_topic_ownergrp($this->_topic);
        
        $this->_canwrite = mgd_is_topic_owner($this->_topic->id) ? true : false;
        $this->_ismanager = mgd_is_member($this->_mgrgroup->id) ? true : false;
        $this->_ispoweruser = $this->_person->parameter("Interface","Power_User") != "NO" ? true : false;
    }
    
    function get_midgard()  { return $this->_midgard; }
    function get_person()   { return $this->_person; }
    function get_mgrgroup() { return $this->_mgrgroup; }
    function get_rptgroup() { return $this->_rptgroup; }
    function can_write()    { return $this->_canwrite; }
    function is_manager()   { return $this->_ismanager; }
    function is_poweruser() { return $this->_ispoweruser; }
    
    function _get_topic_ownergrp ($topic) {
        while ($topic->owner == 0 && $topic->up != 0)
            $topic = mgd_get_topic ($topic->up);
        if ($topic && $topic->owner != 0) {
            return mgd_get_group($topic->owner);
        } else {
            $sg = mgd_get_sitegroup($this->_midgard->sitegroup);
            return mgd_get_group($sg->admingroup);
        }
    }
}



?>