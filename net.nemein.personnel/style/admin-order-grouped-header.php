<?php
$title = $data['root_group']->official;

if (   !$title
    || $title === '')
{
    $title = $data['root_group']->name;
}
?>
<h1><?php echo $data['l10n']->get('sort personnel into sub groups'); ?></h1>
<div id="net_nemein_personnel_new_group">
    <input id="net_nemein_personnel_new_group_name" type="text" />
    <input type="button" onclick="javascript:create_group();" value="<?php echo $data['l10n']->get('create a new group'); ?>" />
</div>
<form method="post" action="&(_MIDGARD['uri']);" id="net_nemein_personnel_group_order" class="datamanager2">
    <div class="groups_list">
    <h2>&(title);</h2>
    <ul id="net_nemein_personnel_groups" class="net_nemein_personnel_group_container">
