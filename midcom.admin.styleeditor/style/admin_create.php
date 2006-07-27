<?php
// Bind the view data, remember the reference assignment:
$request_data =& $_MIDCOM->get_custom_context_data('request_data');
$data = $request_data['datamanager']->get_array();

if ($request_data['object_type'] == 'article' ) {
    $title = $request_data['l10n']->get('create article') ;
} elseif ($request_data['object_type'] == 'topic') {
    $title = $request_data['l10n']->get('create topic') ;
}
?>
<h2><?php echo $title ?></h2>


<?php $request_data['datamanager']->display_form (); ?>
