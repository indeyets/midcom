<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$member =& $data['team_member'];
$person =& $member->get_storage(); 
?>

<li>
<?php
if ($data['is_manager'])
{
?>
<div class="net_nemein_teams_team_manager">&(person.name);</div>
<?php    
}
else
{
?>
<div class="net_nemein_teams_team_player">&(person.name);</div>
<?php
}
?>
</li>