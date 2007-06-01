<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$languages = $data['l10n']->_language_db;
?>
<h1><?php echo sprintf($data['l10n']->get('translation status for language %s'), $languages[$data['language']]['enname']); ?></h1>

<table class="midcom_admin_babel_languages">
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('component'); ?></th>
            <th><?php echo $data['l10n']->get('translated strings'); ?></th>
            <th><?php echo $data['l10n']->get('strings total'); ?></th>
            <th><?php echo $data['l10n']->get('percentage'); ?></th>
        </tr>
    </thead>
    <tbody>