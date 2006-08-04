<?php
$view =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2><?php echo strftime('%A %x', $view['calendar']->get_day_start()); ?></h2>

<ul class="events">