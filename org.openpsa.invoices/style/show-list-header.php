<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');

if (!array_key_exists('header-size', $view_data))
{
    $view_data['header-size'] = 1;
}
?>
<div class="org_openpsa_invoices">
    <?php 
    echo "<h{$view_data['header-size']}>{$view_data['list_label']}</h{$view_data['header-size']}>\n";
    ?>

    <table class="invoices <?php echo $view_data['list_type']; ?>">
        <thead>
            <tr>
                <th class="id"><?php echo $view_data['l10n']->get('invoice'); ?></th>
                <th><?php echo $view_data['l10n']->get('customer'); ?></th>
                <th class="contact"><?php echo $view_data['l10n']->get('customer contact'); ?></th>                
                <th class="sum"><?php echo $view_data['l10n']->get('sum'); ?></th>
                <th><?php echo $view_data['l10n']->get('due'); ?></th>
                <?php 
                if ($view_data['list_type'] != 'open')
                {
                    ?>
                    <th><?php echo $view_data['l10n']->get('paid'); ?></th>
                    <?php
                }
                ?>
            </tr>
        </thead>
        <tbody>