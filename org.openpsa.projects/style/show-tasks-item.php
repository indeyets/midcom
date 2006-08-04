<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view = $view_data['task_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
echo "<dt><a href=\"{$node[MIDCOM_NAV_FULLURL]}task/{$view_data['task']->guid}/\">{$view['title']}</a></dt>\n"; 
?>
<ul>
  <li><?php echo $view_data['l10n']->get('deadline').": {$view['end']['local_strdate']}"; ?></li>
  <li><?php echo sprintf($view_data['l10n']->get('%d hours reported'), $view_data['task_hours']); ?></li>
</ul>

