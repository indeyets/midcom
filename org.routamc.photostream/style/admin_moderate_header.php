<h1><?php echo sprintf($data['l10n']->get('moderate photos for folder %s'), $data['topic']->extra); ?></h1>
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
