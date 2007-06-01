<?php
$data =& $_MIDCOM->get_custom_context_data('request_data');
$stats = $data['stats'];
?>
<table bgcolor=#EEEEEE width=100% border=0>
  <tr>
    <td>
      <small><?php echo sprintf($data['l10n']->get('search for <b>%s</b>'),$data['query']) ?></small>
    </td>
    <td>
      <small><?php echo sprintf($data['l10n']->get('docs <b>%d-%d</b> of <b>%d</b> found'), $stats['first_doc'],$stats['last_doc'],$stats['found'])?></small>
      <small><?php echo sprintf($data['l10n']->get('search time: %s seconds'),$stats['searchtime'])?></small>
    </td>
  </tr>
</table>
