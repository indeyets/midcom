<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$group = $data['featured_group'];
echo "<h2>" . $group['title'] . " (" . $group['description'] . ") </h2>";
?>

<ul>
