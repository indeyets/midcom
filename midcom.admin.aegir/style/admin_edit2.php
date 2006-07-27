<?php
// Bind the view data, remember the reference assignment:
// This one works with datamanager2 datamanagers!

$request_data =& $_MIDCOM->get_custom_context_data('request_data');

if (array_key_exists('title', $request_data)) {
    $title = $request_data['title'];
} else{
    $title = "";
}
?>
<h2><?php echo $title ?></h2>

<?php
 
$request_data['datamanager']->display_form ();
?> 