<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h1><?php echo $data['page_title']; ?></h1>
<table>
    <thead>
        <tr>
            <th class="title"><?php echo $data['l10n']->get('artist'); ?></th>
            <th class="exhibition"><?php echo $data['l10n']->get('exhibition'); ?></th>
            <th class="date"><?php echo $data['l10n']->get('date'); ?></th>
        </tr>
    </thead>
    <tbody>
