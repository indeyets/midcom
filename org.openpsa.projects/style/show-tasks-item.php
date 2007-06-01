<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['task_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
echo "<dt><a href=\"{$node[MIDCOM_NAV_FULLURL]}task/{$data['task']->guid}/\">{$view['title']}</a></dt>\n";
?>
<ul>
  <li><?php echo $data['l10n']->get('deadline').": {$view['end']['local_strdate']}"; ?></li>
  <li><?php echo sprintf($data['l10n']->get('%d hours reported'), $data['task_hours']); ?></li>
</ul>