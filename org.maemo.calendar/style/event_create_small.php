<?php
// Available request keys: controller, indexmode, schema, schemadb

//$data =& $_MIDCOM->get_custom_context_data('request_data');

$start_date_string = strftime("%Y-%m-%d %H:%M:%S", $data['selected_day']);
$end_date_string = strftime("%Y-%m-%d %H:%M:%S", $data['defaults']['end']);

?>

<?php 
//$data['controller']->display_form(); 
?>

<h1>Create event</h1>
<div onclick="close_create_event();">Close</div>
<div onclick="window.location='/event/create/<?php echo $data['selected_day']; ?>'"> Edit details</div>

<span style="font-size:80%; color:#ff0000;">*</span><span style="font-size:80%;">denotes required field</span>

<div class="event-form">
	<form id="org_maemo_calendar" action="/event/create/&(data['selected_day']);" method="post" name="org_maemo_calendar" target="_self" class="datamanager2" >
		<inpyt type="hidden" name="orgOpenpsaAccesstype" id="org_maemo_calendar_orgOpenpsaAccesstype" value="<?echo ORG_OPENPSA_ACCESSTYPE_PUBLIC;?>">
 
		<div class="block" id="simple">
			<div class="block-content">
				<label for='title' id='title_label' class='required'>
					<span class="field_text">Title <span class="field_required_start">*</span></span>
					<input size="40" id="org_maemo_calendar_title" name="title" type="text" value="" />
				</label>


					<label><span class="field_text">start time <span class="field_required_start">*</span></span></label>
					<input class="date" id="org_maemo_calendar_start" name="start" type="text" value="&(start_date_string);" />
					<input class="date_trigger" id="org_maemo_calendar_start_trigger" name="start_trigger" value="..." type="button" />
					<script type="text/javascript">
					    Calendar.setup(
					        {
					            ifFormat    : "%Y-%m-%d %H:%M:%S",
					            daFormat    : "%Y-%m-%d %H:%M:%S",
					            showsTime   : true,
					            align       : "Br",
					            firstDay    : 1,
					            timeFormat  : 24,
					            showOthers  : true,
					            singleClick : false,
					            range       : [0, 9999],
					            inputField  : "org_maemo_calendar_start",
					            button      : "org_maemo_calendar_start_trigger"
					        }
					    );
					</script>

				<div id='end_label'>
					<label><span class="field_text">end time <span class="field_required_start">*</span></span></label>
					<input class="date" id="org_maemo_calendar_end" name="end" type="text" value="&(end_date_string);" />
					<input class="date_trigger" id="org_maemo_calendar_end_trigger" name="end_trigger" value="..." type="button" />
					<script type="text/javascript">
					    Calendar.setup(
					        {
					            ifFormat    : "%Y-%m-%d %H:%M:%S",
					            daFormat    : "%Y-%m-%d %H:%M:%S",
					            showsTime   : true,
					            align       : "Br",
					            firstDay    : 1,
					            timeFormat  : 24,
					            showOthers  : true,
					            singleClick : false,
					            range       : [0, 9999],
					            inputField  : "org_maemo_calendar_end",
					            button      : "org_maemo_calendar_end_trigger"
					        }
					    );
					</script>
				</div>
			</div>				
		</div>
		<div class='form-toolbar'>
			<input class="save" accesskey="s" name="midcom_helper_datamanager2_save" value="Save" type="submit" />
			&nbsp;
			<input class="cancel" accesskey="c" name="midcom_helper_datamanager2_cancel" value="Cancel" type="button" onclick="close_create_event();" />
		</div>

	</form>
</div>