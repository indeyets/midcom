<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<form action="&(data['form_action']);" method="post">
<input type="hidden" name="return_url" value="&(data['return_url']);" />
<table cellspacing='0' cellpadding='0' border='0' class='maillisting' width='100%'>
  <tr>
    <th align='left' width='15%'><?php $data['l10n']->show('date');?></th>
    <th align='left' width='25%'><?php $data['outbox_mode'] ? $data['l10n']->show('to') : $data['l10n']->show('sender');?></th>
    <th align='left'><?php $data['l10n']->show('subject');?></th>
    <th align='center' width='10%'>&nbsp;</th>
  </tr>