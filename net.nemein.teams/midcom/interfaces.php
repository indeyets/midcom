<?php
/**
 * @package net.nemein.team 
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the interface class for net.nemein.teams
 * 
 * @package net.nemein.teams
 */
class net_nemein_teams_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_teams_interface()
    {
        parent::midcom_baseclasses_components_interface();
        $this->_component = 'net.nemein.teams';

        // Load all mandatory class files of the component here
        $this->_autoload_files = array
        (
            'viewer.php', 
            'navigation.php',
	        'log.php',
	        'logger.php',
	        'team.php',
	        'pending.php',
        );
        
        // Load all libraries used by component here
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2',
            'org.openpsa.qbpager',
        );
    }
    
    function _on_resolve_permalink($topic, $config, $guid)
    {
        $team = new net_nemein_teams_team_dba($guid);
        if (   ! $team
            || ! $team->guid)
        {
            return null;
        }

        return "team/{$team->name}/view/";
    }
    
    function _on_reindex($topic, $config, &$indexer)
    {
        $qb = net_nemein_teams_team_dba::new_query_builder();
        
        if ($teams = $qb->execute())
        {
            foreach($teams as $team)
            {
                $group = new midcom_db_group($team->groupguid);
                $qb = midcom_db_member::new_query_builder();
                $qb->add_constraint('gid', '=', $group->id);
                
                if ($members = $qb->execute())
                {
                    foreach($members as $member)
                    {
                        $player = new midcom_db_person();
                        $player->get_by_id($member->uid);
                        
                        $document = $indexer->new_document($player);
                        $document->title = "{$player->username}";
                        $document->abstract = "{$player->username}{$group->name}";
                        $document->content = "{$player->username}{$group->name}{$player->extra}{$document->content}";
                        $document->read_metadata_from_object($player->storage->object);
                        $indexer->index($document);
                    }
                }
            }    
        }
        return true;
    }
}
?>
