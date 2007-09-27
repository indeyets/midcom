<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$member =& $data['team_member'];
?>

<li>
<?php
if ($data['is_manager'])
{
?>
<div class="net_nemein_teams_team_manager">&(member.name);</div>
<?php    
}
else
{
?>
<div class="net_nemein_teams_team_player">&(member.name);</div>
<?php
}
?>
</li>