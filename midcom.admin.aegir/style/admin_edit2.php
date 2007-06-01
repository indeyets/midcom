<?php
// Bind the view data, remember the reference assignment:
// This one works with datamanager2 datamanagers!

//$data =& $_MIDCOM->get_custom_context_data('request_data');

if (array_key_exists('title', $data)) {
    $title = $data['title'];
} else{
    $title = "";
}
?>
<h2><?php echo $title ?></h2>

<?php
 
$data['datamanager']->display_form ();
?> 