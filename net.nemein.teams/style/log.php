<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$logs = $data['logs'];

?>
<h1><?php echo $data['l10n']->get('Captains log'); ?></h1>

Arrrr...

<ul class="net_nemein_team_log">

<?php

  foreach ($logs as $log)
  {
      echo "<li>" . $log->print_log() . "</li>";
  }

?>

</ul>

