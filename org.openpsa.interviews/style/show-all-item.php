<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$contact = new org_openpsa_contactwidget($view_data['person']);
?>
<tr>
    <td>
        <?php echo $contact->show(); ?>
    </td>

    <td>
        <?php $view_data['datamanager']->display_view(); ?>
    </td>
</tr>