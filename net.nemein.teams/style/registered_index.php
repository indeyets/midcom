<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['l10n']->get('registered'); ?></h1>

<a class="net_nemein_teams_create" href="create">
  <?php echo $data['l10n']->get('create a team'); ?>
</a> 

<?php
    $_MIDCOM->dynamic_load("{$prefix}/list");
?>

