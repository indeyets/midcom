<?php
// Available request keys:
// event, registration, registrar

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$event =& $data['event'];
$title = sprintf($data['l10n']->get('register for %s'), $event->title);
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<h2><?php echo $data['topic']->extra; ?>: &(title);</h2>
<h3><?php $data['l10n']->show('event registration complete');?>:</h3>

<p>
  &(event.description:h);
</p>

<p><?php $data['l10n']->show('registration is being processed, you will get a mail');?></p>

<p><a href="&(prefix);"><?php $data['l10n_midcom']->show('back'); ?></a></p>
