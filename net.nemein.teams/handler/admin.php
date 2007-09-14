<?php
/**
 * @package net.nemein.team
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for net.nemein.team
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package net.nemein.teams
 */
class net_nemein_teams_handler_admin  extends midcom_baseclasses_components_handler 
{
    var $_teams_list = null;


    /**
     * Simple default constructor.
     */
    function net_nemein_teams_handler_admin()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
    }

    function _handler_admin ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('index');
        $_MIDCOM->set_pagetitle(":: {$title}");
        
	    return true;
    }

    function _handler_log ($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();

        $title = $this->_l10n_midcom->get('log');
        $_MIDCOM->set_pagetitle(":: {$title}");

        $qb = net_nemein_teams_log_dba::new_query_builder();
	    //$qb->add_constraint('teamguid', '=', 'koe');
        $qb->add_order('metadata.created', 'DESC');

	    $logs = $qb->execute();

	    $this->_request_data['logs'] = $logs;
 

	    return true;
    }
    
    function _handler_manage ($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();
        
        if (isset($args[0]))
        {
            $qb = net_nemein_teams_team_dba::new_query_builder();
            $qb->add_constraint('groupguid', '=', $args[0]);
            
            $teams = $qb->execute();
            
            if (count($teams) > 0)
            {
                foreach ($teams as $team)
                {
                    $team_group = new midcom_db_group($team->groupguid);
                    $team_topic = new midcom_db_topic($team->topicguid);
                    
                    $qb = midcom_db_member::new_query_builder();
                    $qb->add_constraint('gid', '=', $team_group->id);
                    
                    $members = $qb->execute();
                    
                    foreach ($members as $member)
                    {
                        $member->delete();
                    }
                    
                    $team_group->delete();
                    
                    // Setting topic invisible at this point
                    // We might need to delete this for real
                    $team_topic->set_parameter('midcom.helper.metadata', 'nav_noentry', 1);
                                     
                    $team->delete();
                    
                    $_MIDCOM->relocate('manage');
                }
            
            }
        
        }
        
        $qb = net_nemein_teams_team_dba::new_query_builder();
        $this->_teams_list = $qb->execute();
    
        return true;
    }
    
    function _handler_manage_team($handler_id, $args, &$data)
    {
    
        $qb = net_nemein_teams_team_dba::new_query_builder();
        $qb->add_constraint('groupguid', '=', $args[0]);
        
        $teams = $qb->execute();
        
        if (count($teams) > 0)
        {
        
        }
    
        return true;
    }
    
    function _show_manage_team($handler_id, &$data)
    {
        midcom_show_style('manage_team');
    }    
    
    function _show_manage($handler_id, &$data)
    {
        midcom_show_style('manage_teams_start');
        
        foreach($this->_teams_list as $team)
        {
            $this->_request_data['group_guid'] = $team->groupguid;
            $team_group = new midcom_db_group($team->groupguid);
            $this->_request_data['team_name'] = $team_group->name;
            midcom_show_style('manage_teams_item');
        }
        
        midcom_show_style('manage_teams_end');
    } 
    
    function _show_admin($handler_id, &$data)
    {
        midcom_show_style('index');
    }

    function _show_log($handler_id, &$data)
    {
        midcom_show_style('log');
    }
}
?>
