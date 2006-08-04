<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="area">
    <?php echo "<h2><a href=\"{$node[MIDCOM_NAV_FULLURL]}project/list/{$view_data['project_list_status']}/\">".sprintf($view_data['l10n']->get('%s projects'), $view_data['l10n']->get($view_data['project_list_status']))."</a></h2>\n"; ?>
    <?php
    echo sprintf($view_data['l10n']->get('%d %s projects'), count($view_data['project_list_items']), $view_data['l10n']->get($view_data['project_list_status']));
    ?>
</div>
