<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<p>
<?php echo $data['l10n']->get('really remove team'); ?>?
</p>

<form method="post" class="net_nemein_teams_delete">
  <input type="submit" name="remove" value="Remove"/>
  <input type="submit" name="cancel" value="Cancel"/>
</form>