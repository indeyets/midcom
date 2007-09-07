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
class net_nemein_teams_handler_team  extends midcom_baseclasses_components_handler 
{
    var $_logger = null;
    
    var $_root_group = null;

    var $_schemadb = null;

    var $_controller = null;
    
    var $_datamanager = null;

    var $_content_topic = null;

    var $_team_group = null;
    
    var $_team_member = null;
    
    var $_teams_list = Array();
    
    var $_pending = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_teams_handler_team()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * _on_initialize is called by midcom on creation of the handler. 
     */
    function _on_initialize()
    {
        $this->_logger = new net_nemein_teams_logger();
	
	    if ($this->_config->get('teams_root_guid') != '')
        {
	        $root_group_guid = $this->_config->get('teams_root_guid');
	        $this->_root_group = new midcom_db_group($root_group_guid);
	    }

	    $this->_content_topic =& $this->_request_data['content_topic'];
    }
    
    function _join_team($groupguid, $playerguid)
    {
        if (!empty($groupguid) && !empty($playerguid))
        {
            if ($this->_is_player($playerguid))
            {
                // already in team
                return false;
            }
            else
            {
                // Joining a team
                $team = new midcom_db_group($groupguid);
                $player = new midcom_db_person($playerguid);
                
                $member = new midcom_db_member();
                $member->uid = $player->id;
                $member->gid = $team->id;
                
                if ($member->create())
                {   
                    return true;
                }
                else
                {
                    return false;
                }
            }
        }
        
        return false;
    }
   
    function _is_player($playerguid = null)
    {
        $members = 0;
    
        $qb = midcom_db_group::new_query_builder();
	    $qb->add_constraint('owner', '=', $this->_root_group->id);

	    $teams = $qb->execute();

	    if (count($teams) > 0)
	    {
	        // Checing if user is a member of a team
	        foreach($teams as $team)
	        {
	            $qb = midcom_db_member::new_query_builder();
		        $qb->add_constraint('gid.id', '=', $team->id);
		        
		        if(!is_null($playerguid))
		        {
		            $player = new midcom_db_person($playerguid);
		            $qb->add_constraint('uid.id', '=', $player->id);    	        
		        }
		        else
		        {
		            $qb->add_constraint('uid.id', '=', $_MIDCOM->auth->user->_storage->id);
	            }
	        
		        $members = $qb->execute();

		        if (count($members) > 0)
		        {
                    $members++;
		        }
	        }
	    }
	    
	    if ($members > 0)
	    {
	        return false;
	    }
	    
	    // should be false
	    return false;
    }

    /**
     * Loads and prepares the schema database.
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
    }

    function _load_controller()
    {
        $this->_load_schemadb();
	    $this->_controller =& midcom_helper_datamanager2_controller::create('create');
	    $this->_controller->schemadb =& $this->_schemadb;
	    $this->_controller->schemaname = 'team';
	    //$this->_controller->defaults = $this->_defaults;
	    $this->_controller->callback_object =& $this;
	    if (! $this->_controller->initialize())
	    {   
	        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
	        // This will exit.
	    }
    }
    
    /**
     * Internal helper, loads the datamanager for a team group. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager($team_group)
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($team_group))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 
            "Failed to create a DM2 instance for team {$team_group->id}.");
            // This will exit.
        }
    }

    function & dm2_create_callback (&$controller)
    {
        $this->_team_group = new midcom_db_group();
        $this->_team_group->owner = $this->_root_group->id;

        if (!$this->_team_group->create())
        {
            // TODO: handle error

	    }
	    else
	    {
	        $this->_team_member = new midcom_db_member();
	        $this->_team_member->gid = $this->_team_group->id;
	        $this->_team_member->uid = $_MIDCOM->auth->user->_storage->id;
	        
	        if (!$this->_team_member->create())
	        {
	            // TODO: handle error
	        }
	        else
	        {
	            $this->_logger->log("Team group created by " . $_MIDCOM->auth->user->_storage->username, 
	                $this->_team_group->guid);
	        }
        }

	    return $this->_team_group;
    }

    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }

    function _handler_create ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('create team');
        $_MIDCOM->set_pagetitle(":: {$title}");
        
        $this->_load_controller();

        if ($this->_is_player())
	    {
            // TODO: redirect somewhere
            $_MIDCOM->relocate('');
            
	    }
	    else
	    {
            $this->_content_topic->require_do('midgard:create');
  
            switch ($this->_controller->process_form())
	        {
	        case 'save':
                    
		        $team = new net_nemein_teams_team_dba();
                $team->groupguid = $this->_team_group->guid;
		        $team->managerguid = $_MIDCOM->auth->user->guid;
		        
		        if (!$team->create())
		        {
                        // TODO: Handle error
		        }
		        else
		        {
		            $this->_logger->log("Team object created by " . $_MIDCOM->auth->user->_storage->username, 
	                    $this->_team_group->guid);
		        
                    if ($this->_config->get('create_team_home'))
			        {
			            $plugin_name = $this->_config->get('create_team_home_plugin');
			        
			            if (!empty($plugin_name))
			            {
                            $_MIDCOM->relocate("plugin/{$plugin_name}");
                        }
                        else
                        {
                            $_MIDCOM->relocate('');
                        }
			        }
			        else
			        {
                        $_MIDCOM->relocate('');
			        }
                }

            case 'cancel':

	             $_MIDCOM->relocate('');
	             // This will exit.
	    }

	    $this->_prepare_request_data();
	}

	return true;
    }

    function _handler_application ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('application');
        $_MIDCOM->set_pagetitle(":: {$title}");
                
        $qb = net_nemein_teams_team_dba::new_query_builder();
        $qb->add_constraint('groupguid', '=', $args[0]);
        
        if (!$teams = $qb->execute())
        {
        
        }
        
        if (count($teams) > 1)
        {
            // TODO: this shouldn't happen...handle error
        }
        
        $team_group = new midcom_db_group($teams[0]->groupguid);
        
        if (!is_object($team_group))
        {
            // TODO: cant find group...handle this 
        }
        else
        {
            $this->_request_data['team_name'] = $team_group->name;
            $this->_request_data['team_manager'] = $teams[0]->managerguid;
        }
        
        if (isset($_POST['submit_application']))
	    {	        
	        // Creating a pending application
	        $pending = new net_nemein_teams_pending_dba();
	        $pending->playerguid = $_POST['applyee'];
	        $pending->groupguid = $args[0];
	        $pending->managerguid = $_POST['manager'];
	        
	        if (!$pending->create())
	        {
	        
	        }
	        else
	        {
	            if (! $_MIDCOM->componentloader->load_graceful('net.nehmer.mail'))
                {
                    return false;
                }

	            $subject = $this->_l10n->get('New application from');
                $subject .= " " . $_MIDCOM->auth->user->_storage->username; 
                $body = $this->_l10n->get('User has applied for your team') . "<br/>";
                $body .= "<a href=\"" . $this->_config->get('private_pendings_url') . "\">"
                    . $this->_config->get('private_pendings_link') . "</a>";

                $inbox = net_nehmer_mail_mailbox::get_inbox($_MIDCOM->auth->get_user($pending->managerguid));
                $result = $inbox->deliver_mail($_MIDCOM->auth->user, $subject, $body);	        
	        }     

            //$_MIDCOM->relocate('');
	    }

	    return true;
    }

    function _handler_index ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('index');
        $_MIDCOM->set_pagetitle(":: {$title}");


	   return true;
    }
    
    function _handler_create_team_home ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('create team home');
        $_MIDCOM->set_pagetitle(":: {$title}");

	    // TODO: sitewizard magic
    
        return true;
    }

    /**
     * Populates a lis of all registered teams
     */
    function _handler_teams_list($handler_id, $args, &$data)
    {        
        $qb = new org_openpsa_qbpager('net_nemein_teams_team_dba', 'net_nemein_teams_team');
        $qb->results_per_page = $this->_config->get('display_teams_per_page');
        $qb->display_pages = $this->_config->get('display_pages');

        $data['team_qb'] =& $qb;
        $this->_teams_list = $qb->execute();
        
        $this->_prepare_request_data();

        return true;
    }
    
    function _handler_pending($handler_id, $args, &$data)
    {
        $qb = net_nemein_teams_team_dba::new_query_builder();
        $qb->add_constraint('managerguid', '=', $_MIDCOM->auth->user->guid);
            
        if (isset($_POST['approve_pending']))
        {
            $teams = $qb->execute();
        
            foreach($_POST as $key => $value)
            {
                if ($value == "on")
                {
                    if (!$this->_join_team($teams[0]->groupguid, $key))
                    {
                        // TODO: handle this
                    }
                    else
                    {
                        // Removing from pending
                        $qb = net_nemein_teams_pending_dba::new_query_builder();
                        $qb->add_constraint('groupguid', '=', $teams[0]->groupguid);
                        $qb->add_constraint('playerguid', '=', $key);
                    
                        $pending = $qb->execute();
                        
                        foreach($pending as $item)
                        {
                            $item->delete();
                        }                          
                    }
                }
            }
        }     
        
        if ($qb->count() > 0)
        {
            $qb = net_nemein_teams_pending_dba::new_query_builder();
            $qb->add_constraint('managerguid', '=', $_MIDCOM->auth->user->guid);
            
            $pending = $qb->execute();
            
            $this->_pending = $pending;
        }
        else
        {
            $_MIDCOM->relocate('');
        }
        
        return true;
    }

    function _show_pending($handler_id, &$data)
    {
        midcom_show_style('teams_pending_list_start');
    
        foreach($this->_pending as $pending)
        {
            $player = new midcom_db_person($pending->playerguid);
        
            $this->_request_data['pending'] = $pending;  
            $this->_request_data['player_username'] = $player->username;
            midcom_show_style('teams_pending_list_item');
        }
    
        midcom_show_style('teams_pending_list_end');
    }

    function _show_teams_list($handler_id, &$data)
    {       
        $member_count = 0;
        
        midcom_show_style('teams_list_start');
        
        foreach($this->_teams_list as $team)
        {
            $member_count = $team->count_members();
            $team_group = new midcom_db_group($team->groupguid);
 
	        $this->_load_datamanager($team_group);
	        $this->_request_data['view_team'] = $this->_request_data['datamanager']->get_content_html();
	        $this->_request_data['view_team']['team_member_count'] = $member_count;
	        $this->_request_data['view_team']['team_group_guid'] = $team->groupguid;
	        
            midcom_show_style('teams_list_item');
	    }

	    midcom_show_style('teams_list_end');
    }
    
    function _show_create_team_home($handler_id, &$data)
    {

    }
    
    function _show_create($handler_id, &$data)
    {
        $this->_request_data['controller'] = $this->_controller;
        
        midcom_show_style('team_creation_form');
    }

    function _show_application($handler_id, &$data)
    {
        midcom_show_style('application');
    }

    function _show_index($handler_id, &$data)
    {
        if ($_MIDCOM->auth->user)
	    {
            if ($this->_is_player())
	        {
                midcom_show_style('player_index');
	        }
	        else
	        {
                midcom_show_style('registered_index');
	        }
        }
	    else
	    {
             midcom_show_style('index');
	    }
    }
    
    
    /**
     * Helper, updates the context so that we get a complete breadcrum line towards the current
     * location.
     *
     */
    function _update_breadcrumb_line()
    {
        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "/",
            MIDCOM_NAV_NAME => $this->_l10n->get('index'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
}
?>
