<?php
// Bind the view data, remember the reference assignment:
echo "<h1>" .$data['form']->error() . "</h1>\n";

echo "<div class=\"description\">\n";
echo $data['form']->description();
echo "</div>\n";

$data['form']->display_form(); 
?>