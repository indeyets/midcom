<!-- Show-own -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>
<style>

.clear_both
{
	display: block;
	clear: both;
}


label select, label input
{
	display:block;
	clear:both;
	float:left;
}

fieldset
{
	clear:both;
}

.radios label
{
	margin:0;
	padding:0;
	display:inline;
}

textarea
{
	border:1px solid #000000;
}

input
{
	border:1px solid #000000;
}

</style>


<?php
				$group = mgd_get_object_by_guid($data['config']->get('admin_group'));
				$persons_list = mgd_list_members($group->id);
				while( $persons_list->fetch() )
				{
					$tmp_person = mgd_get_person($persons_list->uid);
					if ($tmp_person->id == $_MIDGARD['user'])
					{
						?>
						<?php //<br /><a href="products/">Tuotteet</a>
						?>
						<?php
					}
				}
?>

<div style="float:right; margin:20px;"><a href="report/">Raportit</a></div>

<h1><?php echo $data['l10n']->get('Show-internalorders'); ?></h1>
<h2><?php echo $data['l10n']->get('Own, not sent'); ?></h2>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td width="250"><strong><?php echo $data['l10n']->get('number'); ?></strong></td>
		<td width="150"><strong><?php echo $data['l10n']->get('Time'); ?></strong></td>
		<td width="100"><strong><?php echo $data['l10n']->get('Receiver'); ?></strong></td>
	</tr>
<?php
foreach($data['created'] as $event)
{
	$tmp_person = mgd_get_person($event->extra);
	echo "\t<tr>\n";
	echo "\t\t<td><img src=\"/midcom-static/stock-icons/16x16/cancel.png\" alt=\"\" border=\"\">&nbsp;<a href=\"edit/".$event->guid().".html\">".$event->title."</a></td>\n";
	echo "\t\t<td>".date("d.m.Y G:i", $event->start)."</td>\n";
	echo "\t\t<td>".$tmp_person->name."</td>\n";
	echo "\t</tr>\n";
//	echo "<a href=\"edit/".$event->guid().".html\">".$event->title."</a>&nbsp;".date("d.m.Y G:i", $event->start)."<br />\n";
}
?>
</table>
<br /><br />

<input type="button" value="<?php echo $data['l10n']->get('Make new internal order'); ?>" onclick="document.location.href=document.location.href+'/create/'" />
<!-- <a href="create/"><?php echo $data['l10n']->get('Make new internal order'); ?></a>
-->

<h2><?php echo $data['l10n']->get('Own, sent, not confirmed'); ?></h2>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td width="250"><strong><?php echo $data['l10n']->get('number'); ?></strong></td>
		<td width="150"><strong><?php echo $data['l10n']->get('Time'); ?></strong></td>
		<td width="100"><strong><?php echo $data['l10n']->get('Receiver'); ?></strong></td>
	</tr>

<?php
foreach($data['sent'] as $event)
{
	$tmp_person = mgd_get_person($event->extra);
	echo "\t<tr>\n";
	if($event->type == 1)
	{
		echo "\t\t<td><img src=\"/midcom-static/stock-icons/16x16/approved_and_time_invisible.png\" alt=\"\" border=\"\">&nbsp;<a href=\"view/".$event->guid().".html\">".$event->title."</a></td>\n";
	}
	else
	{
		echo "\t\t<td><img src=\"/midcom-static/stock-icons/16x16/approved_but_modified.png\" alt=\"\" border=\"\">&nbsp;<a href=\"view/".$event->guid().".html\">".$event->title."</a></td>\n";	
	}
	echo "\t\t<td>".date("d.m.Y G:i", $event->start)."</td>\n";
	echo "\t\t<td>".$tmp_person->name."</td>\n";
	echo "\t</tr>\n";
//	echo "<a href=\"view/".$event->guid().".html\">".$event->title."</a>&nbsp;".date("d.m.Y G:i", $event->start)."<br />\n";
}
?>
</table>
<br /><br />

<h2><?php echo $data['l10n']->get('Sent to you, not confirmed'); ?></h2>
<table cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td width="250"><strong><?php echo $data['l10n']->get('number'); ?></strong></td>
		<td width="150"><strong><?php echo $data['l10n']->get('Time'); ?></strong></td>
		<td width="100"><strong><?php echo $data['l10n']->get('Sender'); ?></strong></td>
	</tr>
<?php
foreach($data['incoming'] as $event)
{
	$tmp_person = mgd_get_person($event->creator);
	echo "\t<tr>\n";
	if($event->type == 1)
	{
		echo "\t\t<td><img src=\"/midcom-static/stock-icons/16x16/approved_and_time_invisible.png\" alt=\"\" border=\"\">&nbsp;<a href=\"receive/".$event->guid().".html\">".$event->title."</a></td>\n";
	}
	else
	{
		echo "\t\t<td><img src=\"/midcom-static/stock-icons/16x16/approved_but_modified.png\" alt=\"\" border=\"\">&nbsp;<a href=\"receive/".$event->guid().".html\">".$event->title."</a></td>\n";	
	}
	echo "\t\t<td>".date("d.m.Y G:i", $event->start)."</td>\n";
	echo "\t\t<td>".$tmp_person->name."</td>\n";
	echo "\t</tr>\n";
}
?>
</table>


<!-- / Show-own -->
