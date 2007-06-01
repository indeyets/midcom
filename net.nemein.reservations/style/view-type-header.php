<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<table class="resources &(data['resource_type']:h);">
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get($data['schemadb_resource'][$data['resource_type']]->description); ?></th>
            <th><?php echo $data['l10n']->get('capacity'); ?></th>
            <th><?php echo $data['l10n']->get('location'); ?></th>
        </tr>
    </thead>
    <tbody>