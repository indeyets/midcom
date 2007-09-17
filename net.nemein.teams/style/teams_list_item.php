<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<li>
<?php

    echo "<a href=\"team_home_{$data['view_team']['team_group_guid']}\">
      {$data['view_team']['team_name']}</a>";
      
    echo $data['view_team']['team_logo'];
   
    if ($_MIDCOM->auth->user)
    {
        echo "<a href=\"application/{$data['view_team']['team_group_guid']}\">
           {$data['l10n']->get('application')}</a>";  
    }
?>
</li>