<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<li>
<?php

    echo "<a href=\"{$data['view_team']['profile_url']}\">
      {$data['view_team']['team_name']}</a>";
      
    echo $data['view_team']['team_logo'];
   
    if ($_MIDCOM->auth->user)
    {
        echo "<a href=\"{$prefix}application/{$data['view_team']['group_guid']}\">
           {$data['l10n']->get('application')}</a>";  
    }
?>
</li>