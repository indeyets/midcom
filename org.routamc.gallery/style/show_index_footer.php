<?php
$data = & $_MIDCOM->get_custom_context_data('request_data');
?>
</ul>
<div style="clear: left;"></div>
</div>
<?php
$data['qb']->show_pages();
?>