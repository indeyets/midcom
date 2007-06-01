<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$dn_data= $data['datamanager']->get_array();

if ($data['object_type'] == 'article' ) {
    $title = $data['l10n']->get('create article') ;
} elseif ($data['object_type'] == 'topic') {
    $title = $data['l10n']->get('create topic') ;
}
?>
<h2><?php echo $title ?></h2>


<?php $data['datamanager']->display_form (); ?>
