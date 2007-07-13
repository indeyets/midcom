<?php
echo "<h2>";
echo sprintf($_MIDCOM->i18n->get_string('%s in %s', 'midcom'), 
        midgard_admin_asgard_plugin::get_type_label($data['type']),
        $_MIDCOM->i18n->get_string($data['component'],$data['component']));
echo "</h2>";

?>

<p>This type belongs to &(data['component']);</p>
&(data['help']:h);