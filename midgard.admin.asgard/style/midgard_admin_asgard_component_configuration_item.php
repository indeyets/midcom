<?php
$class = ($data['even'])?' class="even"':' class="odd"';
?>
<tr&(class:h);>
<td class="key" colspan="2"><span><?php echo $_MIDCOM->i18n->get_string($data['key'], $data['name']); ?></span></td>
</tr>
<tr&(class:h);>
<td class="global"><span>&(data['global']:h);</span></td>
<td class="local"><span>&(data['local']:h);</span></td>
</tr>