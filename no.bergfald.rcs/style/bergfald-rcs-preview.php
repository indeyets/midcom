<?php
/*
 * Created on Aug 22, 2005
 *
 */
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$preview = $request_data['preview'];

echo "<h1>{$request_data['view_title']}</h1>\n";

foreach ($preview as $attribute => $value) 
{
    echo '<div class="form_description">'.$attribute . '</div><div class="form_viewfield">'.$value. '</div>';
}
?>
