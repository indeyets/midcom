<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<p class="net_nemein_teams_lockdown">
<?php echo $data['l10n']->get('system lockdown'); ?>
</p>
<p>
<a href="<?php echo $prefix; ?>" class="net_nemein_teams_lockdown_back">
<?php echo $data['l10n']->get('back to teams'); ?>
</a>
</p>

