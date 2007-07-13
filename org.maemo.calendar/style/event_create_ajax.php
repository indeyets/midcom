<?php
?>

<h1>Create event</h1>
<div onclick="close_create_event();">Close</div>
<div onclick="window.location='/event/create/<?php echo $data['selected_day']; ?>'"> Edit details</div>

<div class="event-form">

	<?php 
	$data['controller']->display_form(); 
	?>

</div>

<script>
//jQuery("input.cancel").bind('click',close_create_event);
</script>