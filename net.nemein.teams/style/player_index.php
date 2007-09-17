<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['l10n']->get('player'); ?></h1>



<?php
    $_MIDCOM->dynamic_load("{$prefix}/list");
?>


