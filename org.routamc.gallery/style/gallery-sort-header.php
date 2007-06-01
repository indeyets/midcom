<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div id="org_routamc_gallery">
    <h1><?php echo $data['l10n']->get('sort photos'); ?></h1>
    <p>
        <?php echo $data['l10n']->get('sort photos by dragging and dropping'); ?>.
        <?php echo $data['l10n']->get('you can also create sub galleries for the current gallery'); ?>
    </p>
    <div id="group_creation_wizard">
        <label for="create_group">
            <span class="label">
                <?php echo $data['l10n']->get('create a new sub gallery'); ?>
            </span>
        </label>
        <input type="text" class="text" name="create_gallery" id="create_group" value="" />
        <input type="submit" name="group_creator" value="<?php echo $data['l10n']->get('create'); ?>" onclick="javascript:create_group();" />
    </div>
    <label for="hide_thumbnails">
        <input id="hide_thumbnails" type="checkbox" name="hide_thumbnails" value="1" onclick="javascript:hide_images(this, 'org_routamc_gallery_sort');" />
        <?php echo $data['l10n']->get('hide thumbnails'); ?>
    </label>
    <form method="post" action="&(_MIDGARD['uri']:h);">
        <ul id="org_routamc_gallery_sort">
