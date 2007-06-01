<?php
$message_dm = $data['datamanager'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
//$contacts_node = midcom_helper_find_node_by_component('org.openpsa.contacts');
?>
<div class="main">
    <?php $message_dm->display_view(); ?>
</div>
<div class="sidebar">
    <div class="area">
        <h2><?php echo $data['l10n']->get("recipients"); ?></h2>
        <dl>
            <dt><?php echo "<a href=\"{$node[MIDCOM_NAV_FULLURL]}campaign/{$data['campaign']->guid}/\">{$data['campaign']->title}</a>"; ?></dt>
            <!--<dd>
                TODO: List recipients
            </dd>-->
        </dl>
    </div>
    <div class="area">
        <?php midcom_show_style('send-status'); ?>
    </div>
</div>