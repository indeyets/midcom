<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$dn_data= $data['datamanager']->get_array();

if (array_key_exists('object_type' , $data) ) {
    if ($data['object_type'] == 'article' ) {
        $title = $data['l10n']->get('view article') .': '. htmlspecialchars($data['title']);
    } elseif ($data['object_type'] == 'topic') {
        $title = $data['l10n']->get('view topic') .': '. htmlspecialchars($data['name']);
    } elseif (array_key_exists('title', $data)) {
        $title = $data['title'];
    }
} else{
    $title = "";
}
?>

<h2><?php echo $title ?></h2>

<?php $data['datamanager']->display_view (); ?>
