<?php
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="midcom_helper_imagepopup">
	<h1><?php echo $_MIDCOM->get_context_data(MIDCOM_CONTEXT_PAGETITLE); ?></h1>
	
	<div id="top_navigation">
		<ul>
		<?php
		if ($request_data['list_type'] == 'list_topic')
		{
			echo "<li><a href=\"../../../{$request_data['topic_guid']}/{$request_data['object_guid']}/{$request_data['schema_name']}\">Page</a></li>";
			echo "<li class=\"selected\"><a href=\"../../../folder/{$request_data['topic_guid']}/{$request_data['object_guid']}/{$request_data['schema_name']}\">Folder</a></li>";
		}
		else
		{
			echo "<li class=\"selected\"><a href=\"../../{$request_data['topic_guid']}/{$request_data['object_guid']}/{$request_data['schema_name']}\">Page</a></li>";
			echo "<li><a href=\"../../folder/{$request_data['topic_guid']}/{$request_data['object_guid']}/{$request_data['schema_name']}\">Folder</a></li>";
		}

	   ?>
	   </ul>
	</div>
	<div id="files">
		<?php 
		$request_data['form']->display_form();
		?>
	</div>
	
</div>