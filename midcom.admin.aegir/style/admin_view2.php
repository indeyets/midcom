<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$title = $data['title'];
?>

<h2><?php echo $title ?></h2>

<?php

foreach ($data['datamanager']->datamanager->get_content_html() as $field) : 

?>
&(field);
<?php endforeach; ?>


