<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="area">
    <h2><?php echo sprintf($view_data['l10n']->get('%s projects'), $view_data['l10n']->get($view_data['view'])); ?></h2>
    <?php
        echo sprintf($view_data['l10n']->get('%d %s projects'), count($view_data['project_list_results'][$view_data['view']]), $view_data['l10n']->get($view_data['view']));
    ?>
    <table>
        <thead>
            <tr>
                <th><?php echo $view_data['l10n']->get('project'); ?></th>
                <th><?php echo $view_data['l10n']->get('manager'); ?></th>
                <th><?php echo $view_data['l10n']->get('customer'); ?></th>
                <th><?php echo $view_data['l10n']->get('start'); ?></th>
                <th><?php echo $view_data['l10n']->get('end'); ?></th>
                <th><?php echo $view_data['l10n']->get('status'); ?></th>
            </tr>
        </thead>
        <tbody>