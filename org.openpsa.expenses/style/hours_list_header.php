<h1><?php echo $data['view_title']; ?></h1>

<table>
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('date'); ?></th>
            <th><?php echo $data['l10n']->get('hours'); ?></th>
            <th><?php echo $data['l10n']->get('invoiceable'); ?></th>
            <th><?php echo $data['l10n']->get('person'); ?></th>
            <?php if ($data['mode'] != 'simple')
            { ?>
                <th><?php echo $data['l10n']->get('task'); ?></th>
            <?php } ?>
            <th><?php echo $data['l10n']->get('description'); ?></th>
        </tr>
    </thead>
    <tbody>