<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <h1><?php echo sprintf($view_data['l10n']->get('import subscribers to "%s"'), $view_data['campaign']->title); ?></h1>
    
    <ul>
        <li><a href="&(prefix);campaign/import/vcards/<?php echo $view_data['campaign']->guid; ?>.html"><?php echo $view_data['l10n']->get('import vcards'); ?></a></li>
        <li><a href="&(prefix);campaign/import/simpleemails/<?php echo $view_data['campaign']->guid; ?>.html"><?php echo $view_data['l10n']->get('import email addresses'); ?></a></li>
    </ul>
</div>