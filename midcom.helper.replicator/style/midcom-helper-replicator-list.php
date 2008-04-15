<h1><?php echo $data['view_title']; ?></h1>

<?php
// FIXME: the configs may have all kinds of dynamic parts, also should be per subscription
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

$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . '__mfa/asgard_midcom.helper.replicator/';
if (count($data['subscriptions']) > 0)
{
    ?>
    <table class="midcom_helper_replicator_subscriptions">
        <thead>
            <tr>
                <th><abbr title="<?php echo $_MIDCOM->i18n->get_string('queued / quarantined items', 'midcom.helper.replicator'); ?>"><?php echo $_MIDCOM->i18n->get_string('status', 'midcom.helper.replicator'); ?></abbr></th>
                <th><?php echo $_MIDCOM->i18n->get_string('subscription', 'midcom.helper.replicator'); ?></th>
                <th><?php echo $_MIDCOM->i18n->get_string('exporter', 'midcom.helper.replicator'); ?></th>
                <th><?php echo $_MIDCOM->i18n->get_string('transporter', 'midcom.helper.replicator'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $qm =& midcom_helper_replicator_queuemanager::get();
            foreach ($data['subscriptions'] as $subscription)
            {
                $transporter = midcom_helper_replicator_transporter::create($subscription);
                $queued_items = $qm->list_path_items($qm->get_subscription_basedir($subscription));
                $quarantined_items = $qm->list_path_items($qm->get_subscription_quarantine_basedir($subscription));
                ?>
                <tr>
                    <td><?php echo count($queued_items) . '&nbsp;/&nbsp;' . count($quarantined_items); ?></td>
                    <td><a href="&(prefix);edit/&(subscription.guid);/">&(subscription.title);</a></th>
                    <td><?php echo $_MIDCOM->i18n->get_string($data['schemadb'][$subscription->exporter]->description, 'midcom.helper.replicator'); ?></td>
                    <td class="subscription_info"><?php echo $transporter->get_information(); ?></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    <?php
}
?>