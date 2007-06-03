<?php
$item =& $data['result'];
$mime_icon = null;
$item_type = "image";
?>
	<?php
		switch ($item->mimetype)
		{
			case 'image/png':
			case 'image/jpeg':
	?>
	<?php
				break;
			case 'image/gif':
	?>
	<?php
				break;
			default:
				$item_type = "attachment";
				$mime_icon = midcom_helper_get_mime_icon($item->mimetype);
	?>
	<?php
		}
	?>

<li title="&(item.guid);" class="midcom_helper_imagepopup_search_result_item" rel="&(item_type);">
<?php
if ($item_type == "image")
{
?>
	<a href='http://mfadev/midcom-serveattachmentguid-&(item.guid);/&(item.name);'>
		<img src='http://mfadev/midcom-serveattachmentguid-&(item.guid);/&(item.name);' width='75' height='54' align='texttop' />
	</a>
	<a href='http://mfadev/midcom-serveattachmentguid-&(item.guid);/&(item.name);'>
		<span title="filename">&(item.name);</span>
	</a>
<?php	
}
else
{
?>
	<img src="&(mime_icon);" alt="&(item.mimetype);" align='texttop'/>
	<a href='http://mfadev/midcom-serveattachmentguid-&(item.guid);/&(item.name);'>
		&(item.name);
	</a>
<?php	
}
?>

</li>