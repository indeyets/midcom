<?php
/**
 * @package net.nemein.teams
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package net.nemein.teams
 */
class net_nemein_teams_logger
{

    function __construct()
    {

    }

    function log($message = '', $teamguid)
    {
        // TODO: create log message

        $log = new net_nemein_teams_log_dba();
        $log->message = $message;
        $log->teamguid = $teamguid;

        if (!$log->create())
        {
            // TODO: handle error
       }
    }
}

?>