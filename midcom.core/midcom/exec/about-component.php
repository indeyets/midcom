<?php
//TODO: add bells and whistles
echo '<?'.'xml version="1.0" encoding="UTF-8"?'.">\n";

$component = $_REQUEST['c'];
$manifest = $_MIDCOM->componentloader->manifests[$component];

print("This is the barebone version of <b>About Component</b> showing the properties of <i>$component</i>");
print("<pre>");
print_r($manifest);
print("</pre>");
?>
