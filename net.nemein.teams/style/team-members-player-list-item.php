<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$member =& $data['team_member'];
$person =& $member->get_storage(); 
?>

<li>
<div class="net_nemein_teams_team_player">&(person.name);</div>
</li>