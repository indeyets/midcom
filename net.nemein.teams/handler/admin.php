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
class net_nemein_teams_handler_admin  extends midcom_baseclasses_components_handler
{
    var $_teams_list = null;

    var $_logger = null;


    /**
     * Simple default constructor.
     */
    function net_nemein_teams_handler_admin()
    {
        parent::__construct();
    }

    /**
     * _on_initialize is called by midcom on creation of the handler.
     */
    function _on_initialize()
    {
        $this->_logger = new net_nemein_teams_logger();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_admin ($handler_id, $args, &$data)
    {
        $title = $this->_l10n_midcom->get('index');
        $_MIDCOM->set_pagetitle(":: {$title}");

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
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

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_manage ($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();

        $qb = net_nemein_teams_team_dba::new_query_builder();
        $this->_teams_list = $qb->execute();

        $data['title'] = $this->_l10n->get('manage teams');
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "manage/",
            MIDCOM_NAV_NAME => $data['title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle($data['title']);

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_manage_delete($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();
        $data['team'] = new net_nemein_teams_team_dba($args[0]);

        if (isset($_POST['remove']))
        {
            $team_group = new midcom_db_group($data['team']->groupguid);
            $team_topic = new midcom_db_topic($data['team']->topicguid);

            $qb = midcom_db_member::new_query_builder();
            $qb->add_constraint('gid', '=', $team_group->id);

            $members = $qb->execute();

            foreach ($members as $member)
            {
                $member->delete();
            }

            $data['team']->delete();

            $team_group->delete();

            $team_topic->delete();

            $this->_logger->log("Team (" . $team_group->name . ") was deleted by "
                . $_MIDCOM->auth->user->_storage->username, $team->guid);

            $_MIDCOM->relocate('manage');
        }
        elseif (isset($_POST['cancel']))
        {
            $_MIDCOM->relocate('manage');
        }

        $data['title'] = sprintf($this->_l10n->get('delete team %s'), $data['team']->name);
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => 'manage/',
            MIDCOM_NAV_NAME => $this->_l10n->get('manage teams'),
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "manage/delete/{$data['team']->guid}",
            MIDCOM_NAV_NAME => $data['title'],
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        $_MIDCOM->set_pagetitle($data['title']);

        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_manage_team($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();

        $qb = net_nemein_teams_team_dba::new_query_builder();
        $qb->add_constraint('groupguid', '=', $args[0]);

        $teams = $qb->execute();

        if (count($teams) > 0)
        {
            $this->_request_data['team_guid'] = $teams[0]->groupguid;
        }

        return true;
    }

    /*
    function _handler_manage_system($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_admin_user();

        return true;
    }

    function _show_manage_system($handler_id, &$data)
    {
        midcom_show_style('manage_system');
    }
    */

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_manage_delete($handler_id, &$data)
    {
        midcom_show_style('manage_team_delete');
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_manage_team($handler_id, &$data)
    {
        midcom_show_style('manage_team');
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_manage($handler_id, &$data)
    {
        midcom_show_style('manage_teams_start');

        foreach($this->_teams_list as $team)
        {
            $this->_request_data['team'] = $team;
            $team_group = new midcom_db_group($team->groupguid);
            $this->_request_data['team_group'] = $team_group;

            midcom_show_style('manage_teams_item');
        }

        midcom_show_style('manage_teams_end');
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_admin($handler_id, &$data)
    {
        midcom_show_style('index');
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_log($handler_id, &$data)
    {
        midcom_show_style('log');
    }
}
?>