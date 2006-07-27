<?php
/**
 * @package net.nemein.incidentdb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * IncidentDB site interface class
 * 
 * @todo document
 * 
 * @package net.nemein.incidentdb
 */
class net_nemein_incidentdb_viewer {

    var $_debug_prefix;

    var $_config;      // Configuration object
    var $_topic;       // Our current Topic
    var $_auth;        // Current authentication information
    var $_root_event;  // Root Event Tree Object
    var $_l10n;
    var $_l10n_midcom;
    
    var $_typedb;      // Incident Type Database
    var $_eventlist;   // Event Query interface class
    
    var $_mode;        // mode of operations
    var $_event;       // Current incident in use
    var $_datamanager; // Current Datamanager instance in use

    var $errcode;
    var $errstr;
    

    function net_nemein_incidentdb_viewer($topic, $config) {
        $this->_debug_prefix = "incidentdb viewer::";

        $this->_config = $config;
        $this->_topic = $topic;
        $this->_auth = null;
        $this->_root_event = null;
        $this->_typedb = null;

        $i18n =& $GLOBALS["midcom"]->get_service("i18n");
        $this->_l10n = $i18n->get_l10n("net.nemein.incidentdb");
        $this->_l10n_midcom = $i18n->get_l10n("midcom");
        
        $this->errcode = MIDCOM_ERROK;
        $this->errstr = "";
        $this->_mode = "";
        $this->_datamanager = null;
        $this->_event = null;
    }


    function can_handle($argc, $argv) {
        if ($argc == 0)
            return true;
        
        switch ($argv[0]) {
            case "create":
                if ($argc == 2 && is_numeric($argv[1]))
                    return true;
                if ($argc == 3 && $argv[1] == "type" && is_numeric ($argv[2]))
                    return true;
                break;            
            
            case "edit":
                // FALL THROUGH
            case "view":
                if ($argc == 2 && is_numeric ($argv[1]))
                    return true;
                break;
        }
        
        return false;
    }


    function handle($argc, $argv) {
        debug_push ($this->_debug_prefix . "handle");
        
        $this->_auth = new net_nemein_incidentdb__auth ($this->_topic, $this->_config);
        
        if (is_null($this->_config->get("root_event_guid"))) {
            $this->errcode = MIDCOM_ERRCRIT;
            $this->errstr = $this->_l10n->get("root event not yet present");
            return FALSE;
        } else {
            $this->_root_event = mgd_get_object_by_guid($this->_config->get("root_event_guid"));
        }
        
        $this->_typedb = new net_nemein_incidentdb_typedb ($this->_topic, $this->_config, 
                           $this->_auth, $this->_root_event);
        $this->_eventlist =& $this->_typedb->get_eventlist_ref();
        
        if ($argc == 0) {
            $this->_mode = "index";
            debug_pop();
            return true;
        }
        
        if ($argv[0] == "view") {
            $this->_event = mgd_get_event ($argv[1]);
            if (! $this->_event) {
                $this->errcode = MIDCOM_ERRNOTFOUND;
                $this->errstr = "Cannot open event ID $argv[1].";
                debug_pop();
                return false;
            }
            
            $this->_datamanager =& $this->_eventlist->get_datamanager_for_incident($this->_event);
            if (is_null($this->_datamanager)) {
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Could not set datamanager instance to event " 
                                . $this->_event->id;
                debug_pop();
                return false;
            }
            $this->_mode = "view";
            debug_pop();
            return true;
        }
        
        if ($argv[0] == "edit") {
            $this->_event = mgd_get_event ($argv[1]);
            if (! $this->_event) {
                $this->errcode = MIDCOM_ERRNOTFOUND;
                $this->errstr = "Cannot open event ID $argv[1].";
                debug_pop();
                return false;
            }
            
            $this->_datamanager =& $this->_eventlist->get_datamanager_for_incident($this->_event);
            if (is_null($this->_datamanager)) {
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = "Could not set datamanager instance to event " 
                                . $this->_event->id;
                debug_pop();
                return false;
            }
            switch ($this->_datamanager->process_form()) {
                case MIDCOM_DATAMGR_EDITING:
                    $this->_mode = "edit";
                    debug_pop();
                    return true;

                case MIDCOM_DATAMGR_SAVED:
                    // TODO Switch to detail view if possible
                    // For now we redirect to the index.
                    $this->_update_event_timestamp();
                    $this->_update_index();
                    $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                    $GLOBALS['midcom']->relocate($prefix);
                    // This will exit.

                case MIDCOM_DATAMGR_CANCELLED:
                    debug_add("Creation has been cancelled, returning to index view");
                    $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                    $GLOBALS['midcom']->relocate($prefix);
                    // This will exit.
                    
                case MIDCOM_DATAMGR_FAILED:
                    $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                    $this->errcode = MIDCOM_ERRCRIT;
                    return false;
            }
        } // $argv[0] == "edit" 
        
        if ($argv[0] == "create") {
            if ($argc == 3 && array_key_exists($argv[2], $this->_typedb->get_types())) {

                /* Create a new incident, then redirect to the creation edit loop */
                debug_add("Creating a new incident with type $argv[2].");
                $event = $this->_eventlist->create_new_incident($argv[2]);
                if (! $event) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Unkown Incident Type";
                    debug_pop();
                    return false;
                }
                $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                $GLOBALS['midcom']->relocate("{$prefix}/create/{$event->id}.html");
                // this will exit.
            }
            if ($argc == 2) {
                $this->_event = mgd_get_event ($argv[1]);
                if (! $this->_event) {
                    $this->errcode = MIDCOM_ERRNOTFOUND;
                    $this->errstr = "Cannot open event ID $argv[1].";
                    debug_pop();
                    return false;
                }
                
                $this->_datamanager =& $this->_eventlist->get_datamanager_for_incident($this->_event);
                if (is_null($this->_datamanager)) {
                    $this->errcode = MIDCOM_ERRCRIT;
                    $this->errstr = "Could not set datamanager instance to event " 
                                    . $this->_event->id;
                    debug_pop();
                    return false;
                }
                
                switch ($this->_datamanager->process_form()) {
                    case MIDCOM_DATAMGR_EDITING:
                        $this->_mode = "create";
                        debug_pop();
                        return true;
    
                    case MIDCOM_DATAMGR_SAVED:
                        // TODO Switch to detail view if possible
                        // For now we redirect to the index.
                        $this->_update_event_timestamp();
                        $this->_update_index();
                        $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                        $GLOBALS['midcom']->relocate($prefix);
                        // This will exit();
    
                    case MIDCOM_DATAMGR_CANCELLED:
                        debug_add("Creation has been cancelled, deleting record and returning to index view");

                        // Delete parameters to enable deletion
                        $parameters = $this->_event->listparameters();
                        if ($parameters) {
                          while ($parameters->fetch()) {
                             $params = $this->_event->listparameters($parameters->domain);
                             if ($params) {
                               while ($params->fetch()) {
                                  $this->_event->parameter($params->domain,$params->name,"");
                               }
                             }
                          }
                        }

                        if (! mgd_delete_event($this->_event->id)) {
                            $this->errcode = MIDCOM_ERRCRIT;
                            $this->errstr = "Temporary Event could not be deleted: " . mgd_errstr();
                            debug_pop();
                            return false;
                        } else {
                            $prefix = $GLOBALS["midcom"]->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                            $GLOBALS['midcom']->relocate($prefix);
                            // This will exit();
                        }
                        
                    case MIDCOM_DATAMGR_FAILED:
                        $this->errstr = "Datamanager: " . $GLOBALS["midcom_errstr"];
                        $this->errcode = MIDCOM_ERRCRIT;
                        return false;
                }
            } /* argv[1] == create */
            
        }
        
        
        /* We do not know this kind of request */
        debug_pop();
        $this->errcode = MIDCOM_ERRCRIT;
        $this->errstr = "Unkown Method";
        return false;
    }

    function _update_event_timestamp () {
        debug_add('INCIDENTDB Updating Event Timestamp from "' . $this->_event->extra . '"');
        $date = $this->_event->extra;
        if ($date == -1) {
            debug_add("INCIDENTDB: FAILED, defaulting to time()");
            $this->_event->start = time();
            $this->_event->end = time();
        } else {
            debug_add("INCIDENTDB: SUCCES, got $date");
            $this->_event->start = $date;
            $this->_event->end = $date;
        }
        $this->_event->update();
    }
    
    /**
     * Updates the index using the document currently contained in the member $_event
     * and the datamanager $_datamanager. The Datamanger is reinitialized to ensure
     * up-to-date data in the Index.
     * 
     * You should call this each time you add or modify an existing event.
     */
    function _update_index()
    {
        $this->_datamanager->init($this->_event);
        $indexer =& $GLOBALS['midcom']->get_service('indexer');
        $document = $indexer->new_document($this->_datamanager);
        $document->security = 'component';
        $document->document_url = $GLOBALS['midcom']->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "view/{$this->_event->id}.html";
        $indexer->index($document);
    }
    
    function show() {
        global $view;
        global $view_config;

        // set l10n libraries and auth class
        $GLOBALS["view_l10n"] =& $this->_l10n;
        $GLOBALS["view_l10n_midcom"] =& $this->_l10n_midcom;
        $GLOBALS["view_auth"] =& $this->_auth;
        
        eval ("\$this->show_" . $this->_mode . "();");
        
        // Debugging stuff comes beyond this point. This can be safely removed
        // once the component becomes stable.
        echo "<!-- ";
        echo "<hr><h4>net.nemein.incidents Viewer</h4>";
        echo "<p>SchemaDB: " . $this->_config->get("schemadb") . " </p>";
        $tmp = new midcom_helper_datamanager ($this->_config->get("schemadb"));
        if ($tmp === false) {
            echo "<p>could not create datamanger. layout problems?</p>";
            return TRUE;
        }
        
        echo "<p>Type Database returns these schemas:</p>\n<pre>\n";
        print_r ($this->_typedb->get_types());
        echo "</pre>\n";
        
        $person = $this->_auth->get_person();
        $group = $this->_auth->get_mgrgroup();
        
        echo "<p>Root Event GUID: " . $this->_config->get("root_event_guid");
        echo "<br>Root Event Title: " . $this->_root_event->title . "</p>";
        
        echo "<p>Current \$auth:</p>\n<pre>";
        print_r ($this->_auth);

        echo"</pre>";
        echo " -->";
        return TRUE;
    }

    function get_metadata() {
        return FALSE;
    }
    
    function show_index () {
        midcom_show_style("index-begin");
        
        foreach ($this->_typedb->get_types() as $id => $name) {
            $GLOBALS["view"] = Array ("id" => $id, "name" => $name);
            midcom_show_style("index-incidenttype-begin");
            
            $this->_eventlist->set_type($id);
            $this->_eventlist->set_start(null);
            $this->_eventlist->set_end(null);

            $events = $this->_eventlist->query();
            
            $first = true;
            foreach ($events as $eid => $event) {
                $GLOBALS["view_incident_type"] = Array("id" => $id, "name" => $name);
                $GLOBALS["view_datamanager"] =& $this->_eventlist->get_datamanager_for_incident($event);
                $GLOBALS["view"] =& $event;
                if ($first) {
                    midcom_show_style ("index-incident-header");
                    $first = false;
                }
                midcom_show_style("index-incident");
            }
            echo "<!--\n";
            print_r($events);
            echo "\n-->";
            

            $GLOBALS["view"] = Array ("id" => $id, "name" => $name);
            midcom_show_style("index-incidenttype-end");
        }
        midcom_show_style("index-end");
    }
    
    function show_view () {
        $id = $this->_event->type;
        $types = $this->_typedb->get_types();
        $name = $types[$id];
        $GLOBALS["view_incident_type"] = Array ("id" => $id, "name" => $name);
        $GLOBALS["view_mode"] = $this->_mode;
        $GLOBALS["view_datamanager"] = $this->_datamanager;
        $GLOBALS["view"] =& $this->_event;
        
        midcom_show_style("view-incident");
    }

    function show_edit () {
        $id = $this->_event->type;
        $types = $this->_typedb->get_types();
        $name = $types[$id];
        $GLOBALS["view_incident_type"] = Array ("id" => $id, "name" => $name);
        $GLOBALS["view_mode"] = $this->_mode;
        $GLOBALS["view_datamanager"] = $this->_datamanager;
        $GLOBALS["view"] =& $this->_event;
        
        midcom_show_style("edit-incident");
    }
    
    function show_create () { $this->show_edit(); }
    
    
}

?>