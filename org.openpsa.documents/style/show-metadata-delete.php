<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view = $view_data['metadata_dm'];
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <div class="area">
        <h2><?php echo $view_data['l10n']->get('confirm delete'); ?></h2>
        <p><?php echo $view_data['l10n']->get('use the buttons below or in toolbar'); ?></p>
        <form id="org_openpsa_contacts_document_deleteform" method="post">
            <input type="hidden" name="org_openpsa_documents_deleteok" value="1" />
            <input type="submit" class="button delete" value="<?php echo $view_data['l10n_midcom']->get('delete'); ?>" />
            <input type="button" class="button cancel" value="<?php echo $view_data['l10n_midcom']->get('cancel'); ?>" onClick="window.location='<?php echo $prefix . 'document_metadata/'.$view_data['metadata']->guid.'/'; ?>'" />
        </form>
    </div>
</div>
