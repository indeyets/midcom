<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<h2><?php echo $_MIDCOM->i18n->get_string('hour reports', 'org.openpsa.projects') ?></h2>

<table>
    <thead>
        <tr>
            <th><?php echo $_MIDCOM->i18n->get_string('task', 'org.openpsa.projects') ?></th>
            <th><?php echo $_MIDCOM->i18n->get_string('hours', 'org.openpsa.projects') ?></th>
            <th><?php echo $_MIDCOM->i18n->get_string('invoiceable', 'org.openpsa.projects') ?></th>
            <th><?php echo $data['l10n_midcom']->get('description'); ?></th>
        </tr>
    </thead>
    <tbody>