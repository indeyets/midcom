<?php
/**
 * @package net.nemein.teams
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for net.nemein.teams
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 *
 * @see midcom_baseclasses_components_handler
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

    var $_team = null;

    var $_team_group = null;

    var $_team_member = null;

    var $_teams_list = Array();

    var $_team_player_list = Array();

    var $_team_manager = null;

    var $_pending = null;

    var $_current_team = null;
    var $_current_team_group = null;
    var $_current_action = null;

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

	    if ($this->_config->get('teams_root_guid'))
        {
	        $root_group_guid = $this->_config->get('teams_root_guid');
	        $this->_root_group = new midcom_db_group($root_group_guid);
	    }

	    $this->_content_topic =& $this->_request_data['content_topic'];

	    $this->_request_data['is_registered'] = false;
        $this->_request_data['is_player'] = false;

        if ($_MIDCOM->auth->user)
	    {
	        $this->_request_data['is_registered'] = true;

            if ($this->_is_player())
	        {
                $this->_request_data['is_player'] = true;
	        }
        }
    }

    function _send_private_message($sender_id, $receiver_guid, $subject, $body)
    {
        if (! $_MIDCOM->componentloader->load_graceful('net.nehmer.mail'))
        {
             return false;
        }

        $mail = new net_nehmer_mail_mail();
        $mail->sender = $sender_id;
        $mail->subject = $subject;
        $mail->body = $body;
        $mail->received = time();
        $mail->status = NET_NEHMER_MAIL_STATUS_SENT;
        $mail->owner = $sender_id;

        if (!$mail->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to send welcome mail', MIDCOM_ERRCRIT);
            debug_pop();
        }
        else
        {
            $receiver = new midcom_db_person($receiver_guid);
            $receivers = array($receiver);
            $mail->deliver_to(&$receivers);
        }
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

    function _team_exists($team_name = '')
    {
        $qb = midcom_db_group::new_query_builder();
        $qb->add_constraint('name', '=', $team_name);

        if (!$teams = $qb->execute())
        {
            // TODO: handle this
        }
        else
        {
            if (count($teams) > 0)
            {
                return true;
            }
        }

        return false;
    }

    function _is_player($playerguid = null)
    {
        if (! $_MIDCOM->auth->user)
        {
            return false;
        }

        if (   !$this->_root_group
            || !$this->_root_group->guid)
        {
            return false;
        }

        $members = 0;

        $qb = midcom_db_group::new_query_builder();
	    $qb->add_constraint('owner', '=', $this->_root_group->id);

	    $teams = $qb->execute();

	    if (count($teams) > 0)
	    {
	        // Checking if user is a member of a team
	        foreach($teams as $team)
	        {
	            $qb = midcom_db_member::new_query_builder();
		        $qb->add_constraint('gid', '=', $team->id);

		        if(!is_null($playerguid))
		        {
		            $player = new midcom_db_person($playerguid);
		            $qb->add_constraint('uid', '=', $player->id);
		        }
		        else
		        {
		            $qb->add_constraint('uid', '=', $_MIDCOM->auth->user->_storage->id);
	            }

		        $members = $qb->execute();

		        if (count($members) > 0)
		        {
		            return $members[0]->gid;
                    //return true;
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
        $user = $_MIDCOM->auth->user;

        $_MIDCOM->auth->request_sudo('net.nemein.teams');

        $values = $controller->formmanager->form->getSubmitValues( true );

        $qb = midcom_db_group::new_query_builder();
        $qb->add_constraint('name', '=', $values['team_name']);

        if ($qb->count() > 0)
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('net.nemein.teams'),
                $this->_l10n->get('team name exists')
            );
            $_MIDCOM->relocate('create');
        }

        $this->_team_group = new midcom_db_group();
        $this->_team_group->name = $values['team_name'];
        $this->_team_group->official = $values['team_name'];
        $this->_team_group->owner = $this->_root_group->id;
        $this->_team_group->set_privilege('midgard:owner', $user);

        if (! $this->_team_group->create())
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('net.nemein.teams'),
                $this->_l10n->get('error happened during team creation (group)')
            );
            $_MIDCOM->relocate('');
	    }
	    else
	    {
            $this->_team_group->set_parameter('net.nemein.teams:preferences', 'is_recruiting', true);

	        $this->_team_member = new midcom_db_member();
	        $this->_team_member->gid = $this->_team_group->id;
	        $this->_team_member->uid = $user->_storage->id;
	        $this->_team_member->set_privilege('midgard:owner', $user);

	        if (! $this->_team_member->create())
	        {
	            // TODO: Cleanup
                $_MIDCOM->uimessages->add(
                    $this->_l10n->get('net.nemein.teams'),
                    $this->_l10n->get('error happened during team creation (member)')
                );
	            $_MIDCOM->relocate('');
	        }
	        else
	        {
	            $this->_logger->log("Team group created by " . $_MIDCOM->auth->user->username, $this->_team_group->guid);
	        }
        }

        $this->_team = new net_nemein_teams_team_dba();
        $this->_team->groupguid = $this->_team_group->guid;
        $this->_team->managerguid = $user->guid;

        $url_name = $this->_team_group->guid;
        if ($_MIDCOM->serviceloader->can_load('midcom_core_service_urlgenerator'))
        {
            $urlgenerator = $_MIDCOM->serviceloader->load('midcom_core_service_urlgenerator');
            $url_name = $urlgenerator->from_string($this->_team_group->name);
        }
        $this->_team->name = $url_name;

        $this->_team->set_privilege('midgard:owner', $user);

        if (!$this->_team->create())
        {
                $_MIDCOM->uimessages->add(
                    $this->_l10n->get('net.nemein.teams'),
                    $this->_l10n->get('error happened during team creation (team)')
                );
                $_MIDCOM->relocate('');
        }

        $this->_logger->log("Team object created by " . $user->username,  $this->_team_group->guid);

        $_MIDCOM->auth->drop_sudo();

	    return $this->_team_group;
    }

    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }


    /**
     * Creates a root group if necessary.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_rootgroup($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if (array_key_exists('teams_root_guid', $data))
        {
            // We have this already
            $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
        }

        $this->_root_group = new midcom_db_group();
        $this->_root_group->owner = 0;
        $this->_root_group->name = sprintf('__%s root team', $this->_topic->guid);
        if ($this->_root_group->create())
        {
            $this->_topic->set_parameter('net.nemein.teams', 'teams_root_guid', $this->_root_group->guid);
            $_MIDCOM->uimessages->add($this->_l10n->get('net.nemein.teams'), sprintf($this->_l10n->get('root group %s created'), $this->_root_group->guid), 'ok');

            $_MIDCOM->relocate('');
            // This will exit;
        }
        else
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create root group, reason ".mgd_errstr());
            // This will exit;
        }
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_create ($handler_id, $args, &$data)
    {
        if ($this->_config->get('system_lockdown') == 1)
        {
            $_MIDCOM->relocate('lockdown');
        }

        $_MIDCOM->auth->require_valid_user();

        $title = $this->_l10n_midcom->get('create team');
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");

        $this->_load_controller();

        if ($this->_is_player())
	    {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('net.nemein.teams'),
                $this->_l10n->get('you cannot be part of more than one team')
            );
            $_MIDCOM->relocate('');
	    }

        $this->_content_topic->require_do('midgard:create');

        switch ($this->_controller->process_form())
        {
	        case 'save':
                if ($this->_config->get('create_team_home'))
		        {
                    $_MIDCOM->relocate("team/{$this->_team->name}/create_profile");
		        }
		        else
		        {
                    $_MIDCOM->uimessages->add
                    (
                        $this->_l10n->get('net.nemein.teams'),
                        $this->_l10n->get('team created')
                    );
                    $_MIDCOM->relocate($this->_team->name);
		        }
            case 'cancel':
	             $_MIDCOM->relocate('');
	             // This will exit.
        }

	    $this->_prepare_request_data();

        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'create/',
            MIDCOM_NAV_NAME => $title,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

	    return true;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_edit($handler_id, $args, &$data)
    {
        if ($this->_config->get('system_lockdown') == 1)
        {
            $_MIDCOM->relocate('lockdown');
        }

        $_MIDCOM->auth->require_valid_user();
        $this->_current_team_group->require_do('midgard:update');

        $title = sprintf($this->_l10n_midcom->get('edit %s'), $this->_current_team_group->name);
        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");

        $this->_load_schemadb();
	    $this->_controller =& midcom_helper_datamanager2_controller::create('simple');
	    $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->set_storage($this->_current_team_group);
	    if (! $this->_controller->initialize())
	    {
	        $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
	        // This will exit.
	    }

        $this->_content_topic->require_do('midgard:create');

        switch ($this->_controller->process_form())
        {
	        case 'save':
	            $this->_current_team_group->name = $_REQUEST['team_name'];
	            $this->_current_team_group->update();
            case 'cancel':
                $_MIDCOM->relocate("team/{$this->_current_team->name}/view/");
	             // This will exit.
        }

	    $this->_prepare_request_data();

	    return true;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_application ($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if ($this->_config->get('system_lockdown') == 1)
        {
            $_MIDCOM->relocate('lockdown');
        }

        if ($this->_is_player())
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('net.nemein.teams'),
                $this->_l10n->get('you cannot be part of more than one team')
            );
            $_MIDCOM->relocate('');
        }

        // $title = $this->_l10n_midcom->get('application');
        // $_MIDCOM->set_pagetitle("{$title}");

        if (!is_object($this->_current_team_group))
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('net.nemein.teams'),
                $this->_l10n->get('team group not found')
            );
            $_MIDCOM->relocate('');
        }

        if (isset($_POST['submit_application']))
	    {
	        // Creating a pending application
	        $pending = new net_nemein_teams_pending_dba();
	        $pending->playerguid = $_MIDCOM->auth->user->guid;
	        $pending->groupguid = $this->_current_team->groupguid;
	        $pending->managerguid = $this->_current_team->managerguid;

	        if (!$pending->create())
	        {
                $_MIDCOM->uimessages->add(
                    $this->_l10n->get('net.nemein.teams'),
                    $this->_l10n->get('error submitting application'),
                    'error'
                );
	            $_MIDCOM->relocate('');
	        }

	        $pending->set_privilege('midgard:owner', "user:{$this->_current_team->managerguid}");

            if ($this->_config->get('pm_manager'))
            {
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

                if (! $_MIDCOM->componentloader->load_graceful('net.nehmer.mail'))
                {
                    return false;
                }

                $this->_logger->log("User " . $_MIDCOM->auth->user->username . " has applied to team "
                . $this->_current_team_group->name, $this->_current_team_group->guid);

                $subject = sprintf($this->_l10n->get('new application from %s'), $_MIDCOM->auth->user->username);
                $body = $this->_l10n->get('User has applied for your team') . "<br/>";
                $body . nl2br($_POST['private_application']) . "<br /><br />";
                $body .= "<a href=\"" . $prefix . "team/{$this->_current_team->name}/pending/\">"
                . $this->_config->get('private_pendings_link') . "</a>";

                $this->_send_private_message($_MIDCOM->auth->user->_storage->id, $this->_request_data['team_manager']->guid, $subject, $body);
            }

            $_MIDCOM->uimessages->add(
                $this->_l10n->get('net.nemein.teams'),
                $this->_l10n->get('application sent to teams manager')
            );

            $_MIDCOM->relocate('');
	    }

	    return true;
    }

    // function _handler_error ($handler_id, $args, &$data)
    // {
    //     return true;
    // }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_index ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('index');

        if (!$this->_root_group->guid)
        {
            return false;
        }

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$title}");

        return true;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_create_profile($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('create team home');
        $_MIDCOM->set_pagetitle("{$title}");

        if ($this->_config->get('system_lockdown') == 1)
        {
            $_MIDCOM->relocate('lockdown');
        }

        if (! is_null($this->_config->get('on_create_profile')))
        {
    	    $this->_invoke_profile_creation_callback(&$_MIDCOM->auth->user);
        }
        else
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

        return true;
    }

    /**
     * This function invokes the callback set in the component configuration upon
     * activation of an account. It will be executed at the end of the activation
     * with current user's privileges.
     *
     * Configuration syntax:
     * <pre>
     * 'on_create_profile' => Array
     * (
     *     'callback' => 'callback_function_name',
     *     'autoload_snippet' => 'snippet_name', // optional
     *     'autoload_file' => 'filename', // optional
     * ),
     * </pre>
     *
     * The callback function will receive the midcom_db_person object instance as an argument.
     *
     * @access private
     */
    function _invoke_profile_creation_callback(&$user)
    {
        $callback = $this->_config->get('on_create_profile');
        if ($callback)
        {
            // Try autoload:
            if (array_key_exists('autoload_snippet', $callback))
            {
                mgd_include_snippet_php($callback['autoload_snippet']);
            }
            if (array_key_exists('autoload_file', $callback))
            {
                require_once($callback['autoload_file']);
            }

            if (! function_exists($callback['callback']))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to load the callback {$callback['callback']} for team profile creation, the function is not defined.", MIDCOM_ERRCRIT);
                debug_pop();
                return;
            }
            $callback['callback']($user);
        }
    }

    /**
     * Populates a list of all registered teams
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $order_by = 'name';
        $order = 'ASC';

        if (isset($_REQUEST['net_nemein_teams_order']))
        {
            $order = strtoupper($_REQUEST['net_nemein_teams_order']);
        }
        if (isset($_REQUEST['net_nemein_teams_order_by']))
        {
            $order_by = $_REQUEST['net_nemein_teams_order_by'];
        }

        $qb = new org_openpsa_qbpager('net_nemein_teams_team_dba', 'net_nemein_teams_team');
        $qb->listen_parameter('net_nemein_teams_order', array('asc', 'desc'));
        $qb->listen_parameter('net_nemein_teams_order_by', array('name', 'metadata.published'));

        $qb->results_per_page = $this->_config->get('display_teams_per_page');
        $qb->display_pages = $this->_config->get('display_pages');

        if (   isset($_REQUEST['net_nemein_teams_search'])
            && isset($_REQUEST['query'])
            && !empty($_REQUEST['query']))
        {
            $qb->listen_parameter('search', $_REQUEST['query']);

            $query = $_REQUEST['query'];
            $query = str_replace("*","%", $query);
            $query = preg_replace('/%+/', '%', $query);
            if (strpos($query, '%') === false)
            {
                $query = $query.'%';
            }

            if (! preg_match('/^%+$/', $query))
            {
                $qb->add_constraint('name', 'LIKE', $query);
            }
        }

        $qb->add_order($order_by, $order);

        $data['team_qb'] =& $qb;
        $this->_teams_list = $qb->execute();

        $this->_prepare_request_data();

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        $member_count = 0;

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        midcom_show_style('teams_list_start');

        foreach($this->_teams_list as $team)
        {
            $member_count = $team->count_members();
            $team_group = new midcom_db_group($team->groupguid);
            $is_recruiting = $team_group->get_parameter('net.nemein.teams:preferences','is_recruiting');
            $location = null;

	        $this->_load_datamanager($team_group);
	        $this->_request_data['view_team'] = $this->_request_data['datamanager']->get_content_html();
	        $this->_request_data['view_team']['member_count'] = $member_count;
	        $this->_request_data['view_team']['group_guid'] = $team->groupguid;
	        $this->_request_data['view_team']['description'] = $team_group->get_parameter('midcom.helper.datamanager2','team_description');
	        $this->_request_data['view_team']['location'] = $team_group->get_parameter('midcom.helper.datamanager2','team_location');

            $_MIDCOM->load_library('org.routamc.positioning');
            $position = new org_routamc_positioning_object($team_group);

            if (isset($position->_object->guid))
            {
                $location = $position->seek_location_object();
            }
            if (! is_null($location))
            {
                 $city = new org_routamc_positioning_city_dba($location->city);
                 if ($city)
                 {
                    $city_name = $city->city;
                 }

                 $qb = org_routamc_positioning_country_dba::new_query_builder();
                 $qb->add_constraint('code', '=', $location->country);
                 $countries = $qb->execute_unchecked();
                 $country_name = $location->country;
                 if (count($countries) > 0)
                 {
                    $country_name = $countries[0]->name;
                 }

                 $this->_request_data['view_team']['location_object'] =& $location;
                 $this->_request_data['view_team']['location'] = "{$city_name}, {$country_name}";
            }

	        $this->_request_data['view_team']['is_recruiting'] = false;

	        if (   $member_count < $this->_config->get('max_players_per_team')
	            && $is_recruiting)
	        {
    	        $this->_request_data['view_team']['is_recruiting'] = true;
	        }

            $qb = midcom_db_topic::new_query_builder();
            $qb->add_constraint('up', '=', $this->_topic->id);
            $qb->add_constraint('name', '=', $team->name);

            if ($qb->count() == 0)
            {
                $this->_request_data['view_team']['profile_url'] = null;
            }
            else
            {
                $this->_request_data['view_team']['profile_url'] = "{$prefix}{$team->name}";
            }

            $this->_request_data['team'] =& $team;

            midcom_show_style('teams_list_item');
	    }

	    midcom_show_style('teams_list_end');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_pending($handler_id, $args, &$data)
    {
        if ($this->_config->get('system_lockdown') == 1)
        {
            $_MIDCOM->relocate('lockdown');
        }

        $this->_require_manager();

        // $qb = net_nemein_teams_team_dba::new_query_builder();
        // $qb->add_constraint('managerguid', '=', $_MIDCOM->auth->user->guid);
        //
        // $teams = $qb->execute();

        $max_players = $this->_config->get('max_players_per_team');

        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('gid.guid', '=', $this->_current_team->groupguid);

        $member_count = $qb->count();

        if ($member_count < $max_players)
        {
            if (isset($_POST['approve_pending']))
            {
                foreach($_POST as $key => $value)
                {
                    if ($value == "on")
                    {
                        if (!$this->_join_team($this->_current_team->groupguid, $key))
                        {
                            // TODO: handle this
                        }
                        else
                        {
                            $this->_logger->log("User " . $_MIDCOM->auth->user->_storage->username . " has approved player GUID: "
                                . $key, $this->_current_team->guid);

                            // Removing all pending applications
                            $qb = net_nemein_teams_pending_dba::new_query_builder();
                            //$qb->add_constraint('groupguid', '=', $teams[0]->groupguid);
                            $qb->add_constraint('playerguid', '=', $key);

                            $pending = $qb->execute();

                            foreach($pending as $item)
                            {
                                $item->delete();
                            }

                            $player = $_MIDCOM->auth->get_user($key);

	                        $subject = $this->_l10n->get('application was accepted by');
                            $subject .= " " . $_MIDCOM->auth->user->_storage->username;
                            $body = sprintf($this->_l10n->get('your application to team %s '), $this->_current_team->name);
                            $body .= $this->_l10n->get('has been accepted');

                            $sender_id = $_MIDCOM->auth->user->_storage->id;
                            $receiver_guid = $player->_storage->guid;
                            $this->_send_private_message($sender_id, $receiver_guid, $subject, $body);
                        }
                    }
                }
            }

            if (isset($_POST['decline_pending']))
            {
                foreach($_POST as $key => $value)
                {
                    if ($value == 'on')
                    {
                        $this->_logger->log("User " . $_MIDCOM->auth->user->_storage->username . " has declined player GUID: "
                            . $key, $this->_current_team->guid);

                        // Removing pending applications
                        $qb = net_nemein_teams_pending_dba::new_query_builder();
                        $qb->add_constraint('groupguid', '=', $this->_current_team->groupguid);
                        $qb->add_constraint('playerguid', '=', $key);

                        $pending = $qb->execute();

                        foreach($pending as $item)
                        {
                            $item->delete();
                        }

                        $player = $_MIDCOM->auth->get_user($key);

	                    $subject = $this->_l10n->get('application declined by');
                        $subject .= " " . $_MIDCOM->auth->user->_storage->username;
                        $body = $this->_l10n->get('your application to team') . " " . $this->_current_team->name;
                        $body .= $this->_l10n->get('has been declined');

                        $sender_id = $_MIDCOM->auth->user->_storage->id;
                        $receiver_guid = $player->_storage->guid;
                        $this->_send_private_message($sender_id, $receiver_guid, $subject, $body);

                    }
                }
            }
        }
        else
        {
            $this->_request_data['team_full'] = true;
        }

        if ($qb->count() > 0)
        {
            $qb = net_nemein_teams_pending_dba::new_query_builder();
            $qb->add_constraint('managerguid', '=', $this->_request_data['team_manager']->guid);

            $pending = $qb->execute();

            $this->_pending = $pending;
        }
        else
        {
            $_MIDCOM->relocate('');
        }

        return true;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_team_members($handler_id, $args, &$data)
    {

        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('gid.guid', '=', $this->_current_team_group->guid);

        $this->_team_members['manager'] = false;
        $this->_team_members['players'] = array();

        if (!$members = $qb->execute())
        {
            return false;
        }
        else
        {
            foreach ($members as $member)
            {
                $person = new midcom_db_person();
                $person->get_by_id($member->uid);

                if ($this->_current_team->managerguid == $person->guid)
                {
                    $this->_team_members['manager'] =& $_MIDCOM->auth->get_user($member->uid);
                }
                else
                {
                    $this->_team_members['players'][] =& $_MIDCOM->auth->get_user($member->uid);
                }
            }
        }

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_team_members($handler_id, &$data)
    {
        midcom_show_style('team-members-list-start');

        if ($this->_team_members['manager'])
        {
            $this->_request_data['team_member'] = $this->_team_members['manager'];
            midcom_show_style('team-members-manager-list-item');
        }

        foreach ($this->_team_members['players'] as $member)
        {
            $this->_request_data['team_member'] = $member;
            midcom_show_style('team-members-player-list-item');
        }

        midcom_show_style('team-members-list-end');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_quit($handler_id, $args, &$data)
    {
        $_MIDCOM->set_pagetitle("Quit");

        if ($this->_config->get('system_lockdown') == 1)
        {
            $_MIDCOM->relocate('lockdown');
        }

        if (isset($_POST['confirm_quit']))
        {
            $_MIDCOM->relocate('quit/confirm/');
        }
        elseif (isset($_POST['cancel']))
        {
            $_MIDCOM->relocate('');
        }

        return true;
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_quit_confirm($handler_id, $args, &$data)
    {
        $_MIDCOM->set_pagetitle("Confirm");

        if ($this->_config->get('system_lockdown') == 1)
        {
            $_MIDCOM->relocate('lockdown');
        }

        if ($_MIDCOM->auth->user)
        {
            if ($group_id = $this->_is_player())
            {
                $team_group = new midcom_db_group();
                $team_group->get_by_id($group_id);

                $this->_logger->log("User " . $_MIDCOM->auth->user->_storage->username . " has quit team GUID: "
                . $team_group->guid, $team_group->guid);

                // Removing team membership
                $qb = midcom_db_member::new_query_builder();
                $qb->add_constraint('gid', '=', $team_group->id);
                $qb->add_constraint('uid', '', $_MIDCOM->auth->user->_storage->id);

                if (!$members = $qb->execute())
                {
                    return false;
                }
                else
                {
                    foreach($members as $member)
                    {
                        $member->delete();
                    }
                }
            }
        }
        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_quit_confirm($handler_id, &$data)
    {
        midcom_show_style('team_quit_confirm');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_lockdown($handler_id, $args, &$data)
    {
        $_MIDCOM->set_pagetitle(":: Lockdown");

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_lockdown($handler_id, &$data)
    {
        midcom_show_style('teams_lockdown');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_quit($handler_id, &$data)
    {
        if ($this->_is_player())
        {
            midcom_show_style('team_quit');
        }
        else
        {
            $_MIDCOM->relocate('');
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_pending($handler_id, &$data)
    {
        midcom_show_style('teams_pending_list_start');

        $this->_request_data['total_pending'] = 0;

        if (isset($this->_request_data['team_full']) && $this->_request_data['team_full'])
        {
            echo $this->_l10n->get('team is full');
        }
        else
        {
            $this->_request_data['total_pending'] = count($this->_pending);

            foreach($this->_pending as $pending)
            {
                $player = new midcom_db_person($pending->playerguid);

                $this->_request_data['pending'] = $pending;
                $this->_request_data['player_username'] = $player->username;
                midcom_show_style('teams_pending_list_item');
            }
        }

        midcom_show_style('teams_pending_list_end');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create($handler_id, &$data)
    {
        $this->_request_data['controller'] = $this->_controller;

        midcom_show_style('team_creation_form');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_edit($handler_id, &$data)
    {
        $this->_request_data['controller'] = $this->_controller;

        midcom_show_style('team_edit_form');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create_profile($handler_id, &$data)
    {
        $_MIDCOM->relocate('');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_application($handler_id, &$data)
    {
        midcom_show_style('application');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_index($handler_id, &$data)
    {
         midcom_show_style('index');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_view($handler_id, $args, &$data)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        $member_count = $this->_current_team->count_members();
        $is_recruiting = $this->_current_team_group->get_parameter('net.nemein.teams:preferences','is_recruiting');
        $location = null;

        $this->_load_datamanager($this->_current_team_group);
        $this->_request_data['datamanager'] =& $this->_datamanager;

        $this->_request_data['team_logo'] = false;
        $logo = $this->_request_data['datamanager']->types['team_logo']->attachments_info;
        if ($logo)
        {
            $this->_request_data['team_logo'] =& $logo;
        }

        $this->_request_data['view_team'] = $this->_request_data['datamanager']->get_content_html();
        $this->_request_data['view_team']['member_count'] = $member_count;
        $this->_request_data['view_team']['group_guid'] = $this->_current_team_group->guid;
        $this->_request_data['view_team']['description'] = $this->_current_team_group->get_parameter('midcom.helper.datamanager2','team_description');
        $this->_request_data['view_team']['location'] = $this->_current_team_group->get_parameter('midcom.helper.datamanager2','team_location');

        $_MIDCOM->load_library('org.routamc.positioning');
        $position = new org_routamc_positioning_object($this->_current_team_group);
        if (isset($position->_object->guid))
        {
            $location = $position->seek_location_object();
        }
        if (! is_null($location))
        {
             $city = new org_routamc_positioning_city_dba($location->city);
             if ($city)
             {
                $city_name = $city->city;
             }

             $qb = org_routamc_positioning_country_dba::new_query_builder();
             $qb->add_constraint('code', '=', $location->country);
             $countries = $qb->execute_unchecked();
             $country_name = $location->country;
             if (count($countries) > 0)
             {
                $country_name = $countries[0]->name;
             }

             $this->_request_data['view_team']['location_object'] =& $location;
             $this->_request_data['view_team']['location'] = "{$city_name}, {$country_name}";
        }


        $this->_request_data['view_team']['is_recruiting'] = false;

        if (   $member_count < $this->_config->get('max_players_per_team')
            && $is_recruiting)
        {
	        $this->_request_data['view_team']['is_recruiting'] = true;
        }

        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $this->_current_team->name);

        if ($qb->count() == 0)
        {
            $this->_request_data['view_team']['profile_url'] = null;
        }
        else
        {
            $this->_request_data['view_team']['profile_url'] = "{$prefix}{$this->_current_team->name}/";
        }

        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('gid.guid', '=', $this->_current_team_group->guid);
        $member_count = $qb->count_unchecked();
        if ($member_count === false)
        {
            $member_count = 0;
        }
        $this->_request_data['view_team']['member_count'] = $member_count;

        $_MIDCOM->bind_view_to_object($this->_current_team, $this->_request_data['datamanager']->schema->name);
        $_MIDCOM->set_26_request_metadata($this->_current_team->metadata->revised, $this->_current_team->guid);
        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_view($handler_id, &$data)
    {
        midcom_show_style('show-team');
    }

	/**
	 * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
	 */
    function _handler_action($handler_id, $args, &$data)
    {
        if (count($args) < 2)
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('net.nemein.teams'),
                $this->_l10n->get('action not found')
            );
            $_MIDCOM->relocate('');
        }

        if (!$this->_get_team_by_name($args[0]))
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('net.nemein.teams'),
                $this->_l10n->get('team not found')
            );
            $_MIDCOM->relocate('');
        }

        $tmp = Array();

        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$this->_current_team->name}/",
            MIDCOM_NAV_NAME => $this->_current_team_group->name,
        );

        switch ($args[1])
        {
            case 'application':
                $this->_current_action = 'application';
                $this->_handler_application($handler_id, $args, &$data);

                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "team/{$this->_current_team->name}/application/",
                    MIDCOM_NAV_NAME => sprintf($this->_l10n->get('apply to %s'), $this->_current_team_group->name),
                );

                break;
            case 'pending':
                $this->_current_action = 'pending';
                $this->_handler_pending($handler_id, $args, &$data);

                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "team/{$this->_current_team->name}/pending/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('process pending applications'),
                );

                break;
            case 'create_profile':
                $this->_current_action = 'create_profile';
                $this->_handler_create_profile($handler_id, $args, &$data);

                break;
            case 'members':
                $this->_current_action = 'members';
                $this->_handler_team_members($handler_id, $args, &$data);

                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "team/{$this->_current_team->name}/members/",
                    MIDCOM_NAV_NAME => $this->_l10n->get('members'),
                );

                break;
            case 'edit':
                $this->_current_action = 'edit';
                $this->_handler_edit($handler_id, $args, &$data);

                $tmp[] = Array
                (
                    MIDCOM_NAV_URL => "team/{$this->_current_team->name}/edit/",
                    MIDCOM_NAV_NAME => $this->_l10n_midcom->get('edit'),
                );

                break;
            case 'view':
                $this->_current_action = 'view';
                $this->_handler_view($handler_id, $args, &$data);
                break;
            default:
                //TODO: Notify user with growl. (Action not found)
                $_MIDCOM->relocate('');
                //This will exit
        }

        if ($this->_current_team->can_do('midgard:update'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "team/{$this->_current_team->name}/edit/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                )
            );
        }

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_action($handler_id, &$data)
    {
        switch ($this->_current_action)
        {
            case 'application':
                $this->_show_application($handler_id, &$data);
                break;
            case 'pending':
                $this->_show_pending($handler_id, &$data);
                break;
            case 'create_profile':
                $this->_show_create_profile($handler_id, &$data);
                break;
            case 'members':
                $this->_show_team_members($handler_id, &$data);
                break;
            case 'edit':
                $this->_show_edit($handler_id, &$data);
                break;
            case 'view':
                $this->_show_view($handler_id, &$data);
                break;
        }
    }

    function _get_team_by_name($name)
    {

        $qb = net_nemein_teams_team_dba::new_query_builder();
        $qb->add_constraint('name', '=', $name);

        $results = $qb->execute();

        if (count($results) > 0)
        {
            $this->_current_team = $results[0];
            $this->_request_data['team'] = $this->_current_team;
            $this->_current_team_group = new midcom_db_group($this->_current_team->groupguid);
            $this->_request_data['team_group'] = $this->_current_team_group;
            $this->_request_data['team_name'] = $this->_current_team_group->official || $this->_current_team_group->name;
            $this->_request_data['team_manager'] =& $_MIDCOM->auth->get_user($this->_current_team->managerguid);
            return true;
        }

        return false;
    }

    function _require_manager()
    {
        $_MIDCOM->auth->require_valid_user();

        $qb = net_nemein_teams_team_dba::new_query_builder();
        $qb->add_constraint('managerguid', '=', $_MIDCOM->auth->user->guid);

        $found = $qb->count();

        if ($found < 1)
        {
            $_MIDCOM->uimessages->add(
                $this->_l10n->get('net.nemein.teams'),
                $this->_l10n->get('action not found')
            );
            $_MIDCOM->relocate('');
        }
    }
}
?>
