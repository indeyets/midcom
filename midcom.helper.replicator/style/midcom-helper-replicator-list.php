<h1><?php echo $data['view_title']; ?></h1>

<?php
// Check queue directory status
if (!is_dir($data['local_config']->get('queue_root_dir')))
{
    ?>
    <p class="error">
        <?php
        echo sprintf($_MIDCOM->i18n->get_string('replication queue %s does not exist', 'midcom.helper.replicator'), "<code>" . $data['local_config']->get('queue_root_dir') . "</code>");
        ?>
    </p>
    <?php
}
elseif (!is_writable($data['local_config']->get('queue_root_dir')))
{
    ?>
    <p class="error">
        <?php
        echo sprintf($_MIDCOM->i18n->get_string('replication queue %s cannot be written by Apache user', 'midcom.helper.replicator'), "<code>" . $data['local_config']->get('queue_root_dir') . "</code>");
        ?>
    </p>
    <?php
}
else
{
    ?>
    <p>
        <?php
        echo sprintf($_MIDCOM->i18n->get_string('replication queue is stored to %s', 'midcom.helper.replicator'), "<code>" . $data['local_config']->get('queue_root_dir') . "</code>");
        ?>
    </p>
    <?php
}

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . '__mfa/replication/';
if (count($data['subscriptions']) > 0)
{
    ?>
    <table class="midcom_helper_replicator_subscriptions">
        <thead>
            <tr>
                <th><?php echo $_MIDCOM->i18n->get_string('subscription', 'midcom.helper.replicator'); ?></th>
                <th><?php echo $_MIDCOM->i18n->get_string('exporter', 'midcom.helper.replicator'); ?></th>
                <th><?php echo $_MIDCOM->i18n->get_string('transporter', 'midcom.helper.replicator'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($data['subscriptions'] as $subscription)
            {
                $transporter = midcom_helper_replicator_transporter::create($subscription);
                ?>
                <tr>
                    <td><a href="&(prefix);edit/&(subscription.guid);/">&(subscription.title);</a></th>
                    <td><?php echo $_MIDCOM->i18n->get_string($data['schemadb'][$subscription->exporter]->description, 'midcom.helper.replicator'); ?></td>
                    <td><?php echo $transporter->get_information(); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
}
?>