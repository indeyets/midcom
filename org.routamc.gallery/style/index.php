<?php
/**
 * This is the styleelement I use to show the index
 * Use this to get variables etc from the handler:
 */
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h1><?php echo $data['l10n']->get('This is the index'); ?></h1>
<p>
    <?php echo $data['l10n']->get('You can change this to something else :-)'); ?>
    
</p>
<p>Sortorder: &(data['sort_order']);