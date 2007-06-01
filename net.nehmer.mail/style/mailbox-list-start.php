<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');

?>
<table cellspacing='0' cellpadding='0' border='0' class='mailboxlisting' width='100%'>
  <tr>
    <th align='left'><?php $data['l10n']->show('mailbox');?></th>
    <th align='right' colspan="2"><?php $data['l10n']->show('messages');?></th>
  </tr>