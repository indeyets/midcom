<?php
/*
 * Created on Aug 22, 2005
 *
 */
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$preview = $request_data['preview'];

echo "<h1>{$request_data['view_title']}</h1>\n";

echo "<dl>\n";
foreach ($preview as $attribute => $value) 
{
    if ($value == '')
    {
        continue;
    }
    
    if (!no_bergfald_rcs_handler::is_field_showable($attribute))
    {
        continue;
    }
    
    echo "<dt>{$attribute}</dt>\n";
    echo "    <dd>" . nl2br($value) . "</dd>\n";
}
echo "</dl>\n";
?>
