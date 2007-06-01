<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1>&(data['page_title']:h);</h1>
<?php

if (array_key_exists('error', $_GET))
{
    echo "<p>".$data['l10n']->get($_GET['error'])."</p>\n";
}

$data['controller']->display_form();
?>
