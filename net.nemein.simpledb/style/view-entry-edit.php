<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view_title =& $data['view_title'];
$datamanager =& $data['datamanager'];
$view =& $data['view'];
?>

<h1>&(view_title);: <?php echo htmlspecialchars($view["title"]); ?></h1>

<?php $datamanager->display_form(); ?>