<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$player = $data['team_player'];
?>

<li>
<?php
    echo $player->name . " (I is a player)";
?>
</li>