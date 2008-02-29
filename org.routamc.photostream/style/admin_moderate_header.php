<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo sprintf($data['l10n']->get('moderate photos for folder %s'), $data['topic']->extra); ?></h1>
<ul class="org_routamc_photostream_moderate_navigation">
    <li class="first"><a href="&(prefix);moderate/"><?php echo $data['l10n']->get('view unmoderated photos'); ?></a></li>
    <li><a href="&(prefix);moderate/rejected/"><?php echo $data['l10n']->get('view rejected photos'); ?></a></li>
    <li><a href="&(prefix);moderate/all/"><?php echo $data['l10n']->get('view all photos'); ?></a></li>
</ul>
<table id="org_routamc_photostream_moderate">
    <thead>
        <tr>
            <th class="thumbnail">
                <?php echo $data['l10n']->get('thumbnail'); ?>
            </th>
            <th class="photographer">
                <?php echo $data['l10n']->get('photographer'); ?>
            </th>
            <th class="details">
                <?php echo $data['l10n']->get('photo details'); ?>
            </th>
            <th class="buttons">
            </th>
        </tr>
    </thead>
    <tbody>
