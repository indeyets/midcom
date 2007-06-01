<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="org_routamc_photostream">
<h1><?php echo $data['view_title']; ?></h1>

<?php
if (   isset($data['qb'])
    && is_object($data['qb'])
    && method_exists($data['qb'], 'show_pages'))
{
    $data['qb']->show_pages();
}
?>

<ul class="org_routamc_photostream_photos">