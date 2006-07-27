<?php
/**
 * @package net.nemein.incidentdb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * IncidentDB AIS interface class
 * 
 * @todo document
 * 
 * @package net.nemein.incidentdb
 */
class net_nemein_incidentdb_admin {

    var $_debug_prefix;

    var $_config;
    var $_topic;
    var $_auth;
    var $_root_event;
    var $_l10n;
    var $_l10n_midcom;

    var $errcode;
    var $errstr;


    function net_nemein_incidentdb_admin($topic, $config) {
        $this->_debug_prefix = "net.nemein.incidentdb admin::";

        $this->_config = $config;
        $this->_topic = $topic;
        $this->_root_event = null;
        
        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.incidentdb");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");

        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
    }


    function can_handle($argc, $argv) {
        if ($argc == 0)
            return TRUE;
        else
            return FALSE;
    }


    function handle($argc, $argv) {
        debug_push($this->_debug_prefix . "handle");
        
        $this->_auth = new net_nemein_incidentdb__auth($this->_topic, $this->_config);
        if (is_null($this->_config->get("root_event_guid")))
            $this->_create_root_event();
        else 
            $this->_root_event = mgd_get_object_by_guid($this->_config->get("root_event_guid"));
        
        if (! $this->_root_event) {
            $msg = sprintf($this->_l10n->get("failed to open root event"),
                           $this->_config->get("root_event_guid"),
                           mgd_errstr());
            debug_add ("Root event undefined, last midgard error was: " . mgd_errstr());
            $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
        }
        
        debug_pop();
        return TRUE;
    }


    function show() {
        global $view_config;
        global $view_topic;

        // get l10n libraries
        $GLOBALS["view_l10n"] = $this->_l10n;
        $GLOBALS["view_l10n_midcom"] = $this->_l10n_midcom;

        $view_config = $this->_config;
        $view_topic = $this->_topic;

        echo "<p>INCIDENT AIS Interface</p>";
        echo "<p>SchemaDB: " . $this->_config->get("schemadb") . " </p>";
        $tmp = new midcom_helper_datamanager ($this->_config->get("schemadb"));
        if ($tmp === false) {
            echo "<p>could not create datamanger. layout problems?</p>";
            return TRUE;
        }

        echo "<ul>";
        foreach ($tmp->list_layouts() as $name)
            echo "<li>$name</li>";
        echo "</ul>";
        
        echo "<p>Root Event GUID: " . $this->_config->get("root_event_guid") . "</p>";
        echo "<p>Root Event Title: " . $this->_root_event->title . "</p>";
        
        echo "<p>Auth Object Dump:</p>\n<pre>\n";
        print_r ($this->_auth);
        echo "\n</pre>\n";
        
        return TRUE;
    }


    function get_metadata() {
        return FALSE;
    }

    /************** PRIVATE HELPER METHODS **********************/
    
    function _create_root_event () {
        debug_push($this->_debug_prefix . "_create_root_event" );
        debug_add("Need to create root event as it seems.");
        $event = mgd_get_event();
        $tmp = $this->_auth->get_rptgroup();
        $event->owner = $tmp->id;
        $event->title = "__net.nemein.incidentdb_" . $this->_topic->guid();
        $event->description = "Autocreated by net.nemein.incidentdb.";
        $event->up = 0;
        $event->type = 0;
        debug_print_r("This is the event we create: ", $event);
        $id = $event->create();
        if ($id === false) {
            $msg = sprintf($this->_l10n->get("failed to create root event"),
                           mgd_errstr());
            $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
            debug_add("Could not create root event: " . mgd_errstr());
            return null;
        }
        $event = mgd_get_event($id);
        debug_print_r("The re-get-ed event looks like this: ", $event);
        $this->_topic->parameter("net.nemein.incidentdb","root_event_guid",$event->guid());
        $msg = sprintf($this->_l10n->get("created root event"),
                       $event->title);
        $GLOBALS["view_contentmgr"]->msg .= "$msg<br>\n";
        $this->_root_event = $event;
        debug_pop();
    }
    
} // admin

?>