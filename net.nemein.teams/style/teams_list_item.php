<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<li>
<?php

  echo $data['view_team']['team_name'];
  echo $data['view_team']['team_logo'];
  echo "<a href=\"application/{$data['view_team']['team_group_guid']}.html\">
      {$data['view_team']['team_name']}</a>";
?>
</li>