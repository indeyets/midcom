<?php
/**
 * @package net.nemein.personnel
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Person viewer MidCOM interface class.
 * 
 * @package net.nemein.personnel
 */
class net_nemein_personnel_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_personnel_interface()
    {
        parent::__construct();
        
        $this->_component = 'net.nemein.personnel';
        $this->_autoload_files = array
        (
            'viewer.php',
            'navigation.php',
        );
        
        $this->_autoload_libraries = Array('midcom.helper.datamanager2');
    }
    
    /**
     * Iterate over all events and create index record using the custom indexer
     * method.
     * 
     * @todo Rewrite to DM1 usage.
     * @todo Prevent indexing of master-topic Calendars (they're for aggregation only)
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        $group_guid = $config->get('group');
        if (!$group_guid)
        {
            return false;
        }
        
        $group = new midcom_db_group($group_guid);
        if (!$group)
        {
            return false;
        }
        
        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('gid', '=', $group->id);
        $members = $qb->execute();

        $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
        $datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        if (! $datamanager)
        {
            debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $config->get('schemadb'),
                MIDCOM_LOG_WARN);
            continue;
        }
        
        foreach ($members as $member)
        {
            $person = new midcom_db_person($member->uid);
            
            if (! $datamanager->autoset_storage($person))
            {
                debug_add("Warning, failed to initialize datamanager for Person {$person->id}. Skipping it.", MIDCOM_LOG_WARN);
                continue;
            }

            net_nemein_personnel_viewer::index($datamanager, $indexer, $topic);
        }
        
        return true;
    }

    /**
     * Simple lookup method which tries to map the guid to an article of out topic.
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {    
        $person = $_MIDCOM->auth->get_user($guid);
        if (   !$person
            || !$person->guid)
        {
            return null;
        }
        
        if (!$_MIDCOM->auth->is_group_member('group:' . $config->get('group'), $person))
        {
            return null;
        }
        
        if ($person->username)
        {
            return "{$person->username}.html";
        }
        return "{$person->guid}.html";
    }    
}

?>