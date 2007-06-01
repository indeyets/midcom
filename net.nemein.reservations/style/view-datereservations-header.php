<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<table class="net_nemein_reservations_list">
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('time'); ?></th>
            <th><?php echo $data['l10n']->get('event'); ?></th>
            <th><?php echo $data['l10n']->get('resources'); ?></th>
            <th><?php echo $data['l10n']->get('participants'); ?></th>
            <th><?php echo $data['l10n']->get('description'); ?></th>
        </tr>
    </thead>
    <tbody>
