<?php
/**
* @package net.nemein.incidentdb
* @author The Midgard Project, http://www.midgard-project.org 
* @version $Id$
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
* IncidentDB MidCOM interface class.
* 
* @package net.nemein.incidentdb
*/
class net_nemein_incidentdb_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_incidentdb_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'net.nemein.incidentdb';
        $this->_autoload_files = Array('viewer.php', 'admin.php', 'navigation.php', '_auth.php', 'typedb.php', 'eventlist.php');
    }
    
    /**
     * Iterate over all articles and create index record using the datamanger indexer
     * method and component-level security.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        
    	$auth = new net_nemein_incidentdb__auth($topic, $config);
    	$root_event = mgd_get_object_by_guid($config->get("root_event_guid"));
    	$typedb = new net_nemein_incidentdb_typedb ($topic, $config, $auth, $root_event);
        $eventlist =& $typedb->get_eventlist_ref();
        
        // Query all.        
        $events = $eventlist->query();
        
        foreach ($events as $id => $event)
        {
            $datamanager =& $eventlist->get_datamanager_for_incident($event);
            $document = $indexer->new_document($datamanager);
            $document->security = 'component';
            $indexer->index($document);
            $datamanager->destroy();
        }
        
        debug_pop();
        return true;
    }
    
    /**
     * Verifies the permissions of a given document: Managers see all documents, 
     * normal users see only the documents they are the creator of.
     */
    function _on_check_document_permissions (&$document, $config, $topic)
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        $auth = new net_nemein_incidentdb__auth($topic, $config);
        $event = mgd_get_object_by_guid($document->source);
        if (! $event)
        {
            debug_add("Failed to retrieve the event {$document->source} for document {$document->title}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            debug_add("Skipping Event.", MIDCOM_LOG_INFO);
        }
        $tmp = $auth->get_person();
        $personid = $tmp->id;
        debug_pop();
        return ($event->creator == $personid || $auth->is_manager()); 
    }
}

?>