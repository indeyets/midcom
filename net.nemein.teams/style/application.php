<h1><?php echo $data['l10n']->get('apply to team') . " - " . $data['team_name']; ?></h1>

<form method="post" name="team_application" class="net_nemein_team_application">
    Message:<br/> 
    <textarea name="private_application" rows="8" cols="40"></textarea>
    <br/>
    <input type="submit" name="submit_application" value="Submit application"/>
</form>

