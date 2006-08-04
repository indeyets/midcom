<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>
<div class="main">
    <h1><?php echo sprintf($view_data['l10n']->get('import subscribers to "%s"'), $view_data['campaign']->title); ?></h1>
    
    <table>
        <thead>
            <tr>
                <th><?php echo $view_data['l10n']->get('status'); ?></th>
                <th><?php echo $view_data['l10n']->get('contacts'); ?></th>
            </tr>
        </thead>
        
        <tbody>
            <?php
            foreach ($view_data['import_status'] as $status => $count)
            {
                echo "<tr>\n";
                echo "<td>".$view_data['l10n']->get($status)."</td>\n";
                echo "<td>{$count}</td>\n";
                echo "</tr>\n";
            }
            ?>
        </tbody>
    </table>
</div>