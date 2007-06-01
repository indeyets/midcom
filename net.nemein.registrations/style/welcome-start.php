<?php
// Available request keys:
// events

//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<h2><?php echo $data['topic']->extra; ?></h2>

<?php if ($data['config']->get('welcome_page_list_closed')) { ?>
<h3><?echo $data['l10n']->get('upcoming events');?>:</h3>
<?php } else { ?>
<h3><?echo $data['l10n']->get('events open for registrations');?>:</h3>
<?php } ?>