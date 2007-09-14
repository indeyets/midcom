<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$manager = $data['team_manager'];
?>

<h1><?php echo $data['l10n']->get('player list'); ?></h1>

<ul class="net_nemein_team_player_list">
<li>
<?php
    echo $manager->name . " (I is manager)";
?>
</li>