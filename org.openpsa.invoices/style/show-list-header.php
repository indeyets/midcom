<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

if (!array_key_exists('header-size', $data))
{
    $data['header-size'] = 1;
}
?>
<div class="org_openpsa_invoices">
    <?php
    echo "<h{$data['header-size']}>{$data['list_label']}</h{$data['header-size']}>\n";
    ?>

    <table class="invoices <?php echo $data['list_type']; ?>">
        <thead>
            <tr>
                <th class="id"><?php echo $data['l10n']->get('invoice'); ?></th>
                <th><?php echo $data['l10n']->get('customer'); ?></th>
                <th class="contact"><?php echo $data['l10n']->get('customer contact'); ?></th>
                <th class="sum"><?php echo $data['l10n']->get('sum'); ?></th>
                <th><?php echo $data['l10n']->get('due'); ?></th>
                <?php
                if ($data['list_type'] != 'open')
                {
                    ?>
                    <th><?php echo $data['l10n']->get('paid'); ?></th>
                    <?php
                }
                ?>
            </tr>
        </thead>
        <tbody>