<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$contact = new org_openpsa_contactwidget($view_data['person']);
?>
<div class="main">
    <h1><?php echo sprintf($view_data['l10n']->get('interview %s for "%s"'), $view_data['person']->name, $view_data['campaign']->title); ?></h1>

    <div class="contact">
        <?php echo $contact->show(); ?>
    </div>

    <div class="interview" style="clear: left;">
        <?php $view_data['controller']->display_form(); ?>
    </div>
</div>