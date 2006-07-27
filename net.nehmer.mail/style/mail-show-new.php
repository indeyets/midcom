<?php
// Available request keys: controller, formmanager, datamanager, error (PEAR_Error),
// original_mail, receiver, header, receiver_mailbox
$view =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2>&(view['heading']);</h2>

<?php if ($view['error']) { ?>
<p class="processing_msg">
    <?php $view['l10n']->show('failed to send mail') ?>:
    <?php echo $view['error']->getMessage(); ?>
    (<?php echo $view['error']->getCode(); ?>)
</p>
<?php } ?>

<?php
if (   ! $view['receiver_mailbox']->can_do('net.nehmer.mail:ignore_quota')
    && $view['receiver_mailbox']->is_over_quota())
{
?>
<p class="processing_msg">
<?php $view['l10n']->show('cannot send mail, mailbox full.'); ?>
</p>
<?php } else {?>
<?php $view['formmanager']->display_form(); ?>
<?php } ?>

<?php
if ($view['original_mail'])
{
    $original_mail = $view['original_mail'];
    $body = $original_mail->get_body_formatted();
?>
<h3><?php $view['l10n']->show('original message:'); ?></h3>
<h4>&(original_mail.subject);</h4>

&(body:h);


<?php } ?>