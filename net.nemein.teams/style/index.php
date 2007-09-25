<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

$title = $data['l10n']->get('teams');

?>
<h1><?php echo $title; ?></h1>

<?php
if (   $data['is_registered']
    && !$data['is_player'])
{
?>
<a class="net_nemein_teams_create_team_link" href="&(prefix);create">
  <?php echo $data['l10n']->get('create a team'); ?>
</a>
<?php
}
?>

<?php
$_MIDCOM->dynamic_load("{$prefix}list");
?>