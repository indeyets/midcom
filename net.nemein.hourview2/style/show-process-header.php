<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$view = $data['process'];
?>
<div class="process">
    <h2>&(view.title);</h2>

    &(view.description:f);

<table>
    <thead>
        <tr>
            <th><?php echo $data['l10n_midcom']->get("date"); ?></th>
            <th><?php echo $data['l10n']->get("reporter"); ?></th>
            <th><?php echo $data['l10n_midcom']->get("description"); ?></th>
            <th class="hours"><?php echo $data['l10n']->get("hours"); ?></th>
            <th><?php echo $data['l10n']->get("approval"); ?></th>
        </tr>
    </thead>
    <tbody>