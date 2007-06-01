<?php
// Available request keys: controller, formmanager, datamanager, error (PEAR_Error),
// original_mail, receiver, header, receiver_mailbox
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2>&(data['heading']);</h2>

<?php if ($data['error']) { ?>
<p class="processing_msg">
    <?php $data['l10n']->show('failed to send mail') ?>:
    <?php echo $data['error']->getMessage(); ?>
    (<?php echo $data['error']->getCode(); ?>)
</p>
<?php } ?>

<?php
if (   ! $data['receiver_mailbox']->can_do('net.nehmer.mail:ignore_quota')
    && $data['receiver_mailbox']->is_over_quota())
{
?>
<p class="processing_msg">
<?php $data['l10n']->show('cannot send mail, mailbox full.'); ?>
</p>
<?php } else {?>
<?php $data['formmanager']->display_form(); ?>
<?php } ?>

<?php
if ($data['original_mail'])
{
    $original_mail = $data['original_mail'];
    $body = $original_mail->get_body_formatted();
?>
<h3><?php $data['l10n']->show('original message:'); ?></h3>
<h4>&(original_mail.subject);</h4>

&(body:h);


<?php } ?>