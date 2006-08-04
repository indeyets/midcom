<?php
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <?php 
    // Display the group information
    $view_data['group_dm']->display_view();
    ?>
</div>
<div class="sidebar">
    <?php
    if ($view_data['parent_group'])
    {
        ?>
        <div class="area parent">
            <h2><?php echo $view_data['l10n']->get('child group of'); ?></h2>
            <dl>
                <dt><?php echo "<a href=\"{$node[MIDCOM_NAV_FULLURL]}group/{$view_data['parent_group']->guid}/\">{$view_data['parent_group']->official}</a>"; ?></dt>
            </dl>
        </div>
        <?php
    }
    ?>
    <?php $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."group/".$view_data['group']->guid."/members/"); ?>
    <?php $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."group/".$view_data['group']->guid."/subgroups/"); ?>

    <!-- TODO: Add salesprojects here -->
    <!-- TODO: Projects list, Add project button -->
</div>
    