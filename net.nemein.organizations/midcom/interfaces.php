<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: interfaces.php 4838 2006-12-28 16:18:40Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Group viewer MidCOM interface class.
 * 
 * @package net.nemein.organizations
 */
class net_nemein_organizations_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();
        
        $this->_component = 'net.nemein.organizations';
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php'
        );
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
    }
    
    /**
     * Initialize
     *
     * Initialize the basic data structures needed by the component
     */
    function _on_initialize()
    {
        return true;
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
        debug_push_class(__CLASS__, __FUNCTION__);
        $group_guid = $config->get('group');
        if (!$group_guid)
        {
            debug_add('Could not read group GUID from configuration', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        $group = new midcom_db_group($group_guid);
        if (   !$group
            || !$group->guid)
        {
            debug_add("Could not instantiate group {$group_guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        $qb = midcom_db_group::new_query_builder();
        $qb->add_constraint('owner', '=', $group->id);
        $groups = $qb->execute();

        $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
        $datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        if (! $datamanager)
        {
            debug_add('Failed to create a datamanager instance with this schemapath:' . $config->get('schemadb'),
                MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        foreach ($groups as $group)
        {   
            if (! $datamanager->autoset_storage($group))
            {
                debug_add("Warning, failed to initialize datamanager for Group {$group->id}. Skipping it.", MIDCOM_LOG_WARN);
                continue;
            }

            net_nemein_organizations_viewer::index($datamanager, $indexer, $topic);
        }

        debug_pop();
        return true;
    }

    /**
     * Simple lookup method which tries to map the guid to an article of out topic.
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {    
        $group = new midcom_db_group($guid);
        if (   !$group
            || !$group->guid)
        {
            return null;
        }
        
        $parent_group = new midcom_db_group($config->get('group'));
        if (   !$parent_group
            || !$parent_group->guid)
        {
            return null;
        }
        
        if ($group->owner != $parent_group->id)
        {
            return null;
        }
        
        return net_nemein_organizations_viewer::get_url($group);
    }    
}

?>