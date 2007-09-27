<?php
//Available request data keys: team, view_team, team_group, team_name, team_manager
$view =& $data['view_team'];
?>

<div class="net_nemein_teams_team">
    <h1>&(data['team_name']);</h1>
    
    
    
    <?php if ($view['profile_url']) { ?>
    <a href="&(view['profile_url']);"><?php echo $data['l10n']->get('view profile'); ?></a>
    <?php } ?>
</div>