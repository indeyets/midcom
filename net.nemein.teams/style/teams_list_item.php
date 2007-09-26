<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$can_apply = false;
if (   $data['is_registered']
    && !$data['is_player'])
{
    $can_apply = true;
}
$view =& $data['view_team'];
?>

<li>
<?php

echo "<a href=\"{$view['profile_url']}\">
  {$view['team_name']}</a>";
  
echo $view['team_logo'];

if ($view['is_recruiting'])
{
    if ($can_apply)
    {
        echo "<a href=\"{$view['profile_url']}application/\">{$data['l10n']->get('send application')}</a>";
    }
    else
    {
    }
}
else
{
}
?>
</li>