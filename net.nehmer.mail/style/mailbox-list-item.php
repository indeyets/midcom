<?php
// Bind the view data, remember the reference assignment:
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$name = $data['name_translated'];
$count_string = $data['count_string'];
$unread_string = $data['unread_string'];
$url = $data['url'];

?>
<tr class='<?php echo $data['background_class']; ?>'>
  <td class='mailboxname'><a href="&(url);">&(name);</a></td>
  <td align='right' class='unreadcount'>&(unread_string);</td>
  <td align='right' class='messagecount'>&(count_string);</td>
</tr>