<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2>&(data['title']);</h2>
<?php $data['qb']->show_pages(); ?>
<table>
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('applicant'); ?></th>
            <th><?php echo $data['l10n']->get('summary'); ?></th>
            <th><?php echo $data['l10n']->get('state'); ?></th>
        </tr>
    </thead>
    <tbody>

