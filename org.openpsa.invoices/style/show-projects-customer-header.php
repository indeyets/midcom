<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2><?php echo $data['customer_label']; ?></h2>

<form method="post">
    <table class="tasks">
        <thead>
            <tr>
                <th><?php echo $data['l10n']->get('invoice'); ?></th>
                <th><?php echo $_MIDCOM->i18n->get_string('task', 'org.openpsa.projects'); ?></th>
                <th><?php echo $_MIDCOM->i18n->get_string('hours', 'org.openpsa.projects'); ?></th>
                <th><?php echo $data['l10n']->get('price'); ?></th>
            </tr>
        </thead>
        <tbody>