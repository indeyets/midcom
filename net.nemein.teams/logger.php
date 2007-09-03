<?php

class net_nemein_teams_logger
{

    function net_nemein_teams_logger()
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
