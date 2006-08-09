<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
    <h1><?php echo sprintf($view_data['l10n']->get('interviews for "%s"'), $view_data['campaign']->title); ?></h1>
<table>
    <thead>
        <tr>
            <th><?php echo $view_data['l10n']->get('contact'); ?></th>
            <th><?php echo $view_data['l10n']->get('interview'); ?></th>
        </tr>
    </thead>
    <tbody>