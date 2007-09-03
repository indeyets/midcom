<?php

class net_nemein_teams_log_dba extends __net_nemein_teams_log_dba
{

    function net_nemein_teams_log_dba($src = null)
    {
        parent::__net_nemein_teams_log_dba($src);
    }

    function print_log()
    {
        echo  strftime("%D - %T" , $this->metadata->created) . " - " . $this->message;
    }
}

?>
