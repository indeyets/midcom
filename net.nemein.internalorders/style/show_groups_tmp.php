<!-- Show-own -->
<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>

<h1><?php echo $data['l10n']->get('Show-products'); ?></h1>

	<form enctype="multipart/form-data" action="" method="post" class="datamanager">
		<label for="net_nemein_internalorders_groups_upload">
			<span class="field_text"><?php echo $data['l10n']->get('file to import'); ?></span>
			<input type="file" class="fileselector" name="net_nemein_internalorders_groups_upload" id="net_nemein_internalorders_groups_upload" />
		</label>
		<input type="submit" />
	</form>
	


<br /><br />

<!-- / Show-own -->
