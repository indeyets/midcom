<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $view_data['message_dm'];
$nap = new midcom_helper_nav();

$node = $nap->get_node($nap->get_current_node());
$contacts_node = midcom_helper_find_node_by_component('org.openpsa.contacts');
?>
<div class="main">
    <?php $view->display_view(); ?>
</div>
<div class="sidebar">
    <div class="area">
        <h2><?php echo $view_data['l10n']->get("recipients"); ?></h2>
        <dl>
            <dt><?php echo "<a href=\"{$node[MIDCOM_NAV_FULLURL]}campaign/{$view_data['campaign']->guid}/\">{$view_data['campaign']->title}</a>"; ?></dt>
            <!--<dd>
                TODO: List recipients
            </dd>-->
        </dl>
    </div>
    <div class="area">
        <?php midcom_show_style('send-status'); ?>
    </div>
</div>
