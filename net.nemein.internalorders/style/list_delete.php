<!-- Show-own -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<h1><?php echo $data['l10n']->get('Show-internalorders'); ?></h1>
<h3><?php echo $data['l10n']->get('Own, not sent'); ?></h3>
<?php
foreach($order as $event)
{
	echo "<a href=\"edit/".$event->guid().".html\">".$event->title."</a><br />";
}
?>
<br /><br />

<!-- / Show-own -->