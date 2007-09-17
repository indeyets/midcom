<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>
<h1><?php echo $data['l10n']->get('application'); ?></h1>
<h2><?php echo $data['l10n']->get('apply to team') . " - " . $data['team_name']; ?></h2>
<p>
<form method="post" name="team_application" class="net_nmein_team_application">
    <input type="hidden" name="applyee" value="<?php echo $_MIDCOM->auth->user->guid; ?>"/>
    <input type="hidden" name="manager" value="<?php echo $data['team_manager']; ?>"/>
    Message:<br/> 
    <textarea name="private_application"></textarea>
    <br/>
    <input type="submit" name="submit_application" value="Submit application"/>
</form>    
</p>
