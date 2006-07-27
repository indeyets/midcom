<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
?>
<form action="&(view['form_action']);" method="post">
<input type="hidden" name="return_url" value="&(view['return_url']);" />
<table cellspacing='0' cellpadding='0' border='0' class='maillisting' width='100%'>
  <tr>
    <th align='left' width='15%'><?php $view['l10n']->show('date');?></th>
    <th align='left' width='25%'><?php $view['outbox_mode'] ? $view['l10n']->show('to') : $view['l10n']->show('sender');?></th>
    <th align='left'><?php $view['l10n']->show('subject');?></th>
    <th align='center' width='10%'>&nbsp;</th>
  </tr>