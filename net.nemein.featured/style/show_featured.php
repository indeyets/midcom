<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$featured_objects = $data['featured_objects'];

foreach($featured_objects['info'] as $featured)
{
    $featured->load_featured_item();
}

foreach($featured_objects['video'] as $featured)
{
    $featured->load_featured_item();
}

?>


