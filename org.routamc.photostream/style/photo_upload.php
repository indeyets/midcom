<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

// Figure out maximum upload size allowed on server
$post_max = sscanf(ini_get('post_max_size'), '%u');
$upload_max = sscanf(ini_get('upload_max_filesize'), '%u');
$max_upload_size = min($post_max[0], $upload_max[0]);
?>

<h1><?php echo $data['view_title']; ?></h1>

<?php $data['controller']->display_form(); ?>

<div class="info">
    <strong>
        <?php
        echo $data['l10n']->get('upload size limit') . ':';
        ?>
    </strong>
    &(max_upload_size); MB
</div>