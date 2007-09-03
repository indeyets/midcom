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

    var $_content_topic = null;

    var $_team_group = null;

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
   
    function _is_player()
    {
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
		$qb->add_constraint('uid.id', '=', $_MIDCOM->auth->user->_storage->id);
	        
		$members = $qb->execute();

		if (count($members) > 0)
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

    function & dm2_create_callback (&$controller)
    {
        $this->_team_group = new midcom_db_group();

        if (!$this->_team_group->create())
        {
            // TODO: handle error

	}

	return $this->_team_group;
    }

    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
    }

    function _handler_create ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('application');
        $_MIDCOM->set_pagetitle(":: {$title}");

        if ($this->_is_player())
	{
            // TODO: redirect somewhere
	}
	else
	{
            $this->_content_topic->require_do('midgard:create');

	    $this->_load_controller();

            switch ($this->_controller->process_form())
	    {
	        case 'save':
                    
		    $team = new net_nemein_teams_team_dba();
                    $team->group_guid = $this->_team_group->guid;
		    $team->manager_guid = $_MIDCOM->auth->user->guid;

		    if (!$team->crate())
		    {
                        // TODO: Handle error
		    }
		    else
		    {
                        if ($this->_config->get('create_team_home'))
			{
                            $_MIDCOM->relocate("/create/home/{$this->_team_group->guid}");
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

        // TODO: Private message to team manager

        if ($_POST['submit_application'])
	{

            $_MIDCOM->relocate('');
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

    function _handler_teams_list($handler_id, $args, &$data)
    {

        $this->_request_data['teams_list'] = Array();

        return true;
    }

    function _show_teams_list($handler_id, &$data)
    {
        midcom_show_style('teams_list_start');

	foreach ($this->_request_data['teams_list'] as $team)
	{
	    $this->_request_data['team'] = $team;
            midcom_show_style('teams_list_item');
	}

	midcom_show_style('teams_list_end');
    }
    
    function _show_create_team_home($handler_id, &$data)
    {

    }
    
    function _show_create($handler_id, &$data)
    {
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
