<?php
// Available request keys: none in addition to the defaults

//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h1><?php echo $data['page_title']; ?></h1>
<span style="font-size:0.8em"><?php echo $data['l10n']->get("last update:")." ".$data['modified']; ?></span>
