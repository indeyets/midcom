<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h1><?php echo $data['l10n']->get('manage team'); ?></h1>

<?php echo "<a href=\"" . $prefix . "manage/delete/" 
. $data['team_guid'] . "\">" . $data['l10n']->get('remove team') . "</a>"; ?>

