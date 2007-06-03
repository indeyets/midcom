<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "__ais/imagepopup/";
$query = htmlspecialchars($data['query'], ENT_QUOTES);
$schema_name = $data['schema_name'];
$object_guid = $data['object']->guid;
?>
<div class="midcom_helper_imagepopup">
    <h1><?php echo $data['list_title']; ?></h1>

	<?php midcom_show_style("midcom_helper_imagepopup_navigation"); ?>
	
	<div id="search">
	
	<div class="search-form">
		<form method='GET' name='midcom_helper_imagepopup_search_form' action='&(prefix);unified/&(schema_name);/&(object_guid);' class='midcom.helper.imagepopup'>
			<label for="midcom_helper_imagepopup_query">
				<?php echo $data['l10n']->get('query');?>:
				<input type='text' size='60' name='query' id='midcom_helper_imagepopup_query' value='&(query);' />
			</label>
			<input type='submit' name='submit' value='<?php echo $data['l10n']->get('search');?>' />
		</form>
	</div>


