<?php
$data = & $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="org_routamc_gallery">
<h1><?php echo $data['node']->extra; ?></h1>

<?php
$data['qb']->show_pages();
?>

<ul class="org_routamc_photostream_photos">