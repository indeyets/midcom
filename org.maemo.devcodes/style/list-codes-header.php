<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2>&(data['title']);</h2>
<?php $data['qb']->show_pages(); ?>
<table>
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('code'); ?></th>
            <th><?php echo $data['l10n']->get('area'); ?></th>
            <th><?php echo $data['l10n']->get('recipient'); ?></th>
        </tr>
    </thead>
    <tbody>

