<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['invoice_dm'];

echo "<h1>" . $data['l10n']->get('delete invoice') . ": {$data['invoice']->invoiceNumber}</h1>\n";

?>
<form action="" method="post">
  <input type="submit" name="org_openpsa_invoices_deleteok" value="<?php echo $data['l10n_midcom']->get('delete'); ?> " />
  <input type="submit" name="org_openpsa_invoices_deletecancel" value="<?php echo $data['l10n_midcom']->get('cancel'); ?>" />
</form>

<div class="main">
    <?php $view->display_view(); ?>
</div>