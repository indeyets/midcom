<?php
// Bind the view data, remember the reference assignment:
$view =& $_MIDCOM->get_custom_context_data('request_data');
$name = $view['name_translated'];
$count_string = $view['count_string'];
$unread_string = $view['unread_string'];
$url = $view['url'];

?>
<tr class='<?php echo $view['background_class']; ?>'>
  <td class='mailboxname'><a href="&(url);">&(name);</a></td>
  <td align='right' class='unreadcount'>&(unread_string);</td>
  <td align='right' class='messagecount'>&(count_string);</td>
</tr>