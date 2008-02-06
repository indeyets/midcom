<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>

<h2><?php echo $data['l10n']->get('create featured'); ?></h2>

<?php $data['controller']->display_form (); ?>

<?php
$prefix = $_MIDCOM->get_host_name() . $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
echo $data['l10n']->get('get bookmarklet') . ': ';
echo "<a href=\"javascript:location.href='{$prefix}manage?defaults[object_location]='+encodeURIComponent(location.href)+'&defaults[title]='+encodeURIComponent(document.title)\">{$data['l10n']->get('add to featured')}</a>\n";
?>