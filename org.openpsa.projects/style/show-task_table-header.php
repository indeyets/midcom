<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

if ($data['view_identifier'] == 'agreement')
{
    $h_level = 4;
}
else
{
    $h_level = 1;
}
?>
<h&(h_level);><?php echo $data['l10n']->get($data['table-heading']); ?></h&(h_level);>
<table class="tasks">
    <thead>
        <tr>
            <th><?php echo $data['l10n']->get('task'); ?></th>
            <th><?php echo $data['l10n']->get('project'); ?></th>
            <?php
            if ($data['view_identifier'] != 'agreement')
            {
                ?>
                <th><?php echo $data['l10n']->get('customer'); ?></th>
                <th><?php echo $data['l10n']->get('agreement'); ?></th>
                <?php
            }
            ?>
            <th><?php echo $data['l10n']->get('manager'); ?></th>
            <th><?php echo $data['l10n']->get('duration'); ?></th>
            <th class="hours"><?php echo $data['l10n']->get('invoiceable'); ?></th>
            <th class="hours"><?php echo $data['l10n']->get('invoiced'); ?></th>
            <th class="hours"><?php echo $data['l10n']->get('reported'); ?></th>
        </tr>
    </thead>
    <tbody>