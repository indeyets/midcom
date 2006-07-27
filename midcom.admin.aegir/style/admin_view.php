<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');

$data = $view['datamanager']->get_array();

if (array_key_exists('object_type' , $data) ) {
    if ($view['object_type'] == 'article' ) {
        $title = $view['l10n']->get('view article') .': '. htmlspecialchars($data['title']);
    } elseif ($view['object_type'] == 'topic') {
        $title = $view['l10n']->get('view topic') .': '. htmlspecialchars($data['name']);
    } elseif (array_key_exists('title', $view)) {
        $title = $view['title'];
    }
} else{
    $title = "";
}
?>

<h2><?php echo $title ?></h2>

<?php $view['datamanager']->display_view (); ?>
