<?php
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
//$data =& $_MIDCOM->get_custom_context_data('request_data');
?>
<div class="main">
    <?php
    // Display the group information
    $data['group_dm']->display_view();
    ?>
</div>
<div class="sidebar">
    <?php
    if ($data['parent_group'])
    {
        ?>
        <div class="area parent">
            <h2><?php echo $data['l10n']->get('child group of'); ?></h2>
            <dl>
                <dt><?php echo "<a href=\"{$node[MIDCOM_NAV_FULLURL]}group/{$data['parent_group']->guid}/\">{$data['parent_group']->official}</a>"; ?></dt>
            </dl>
        </div>
        <?php
    }
    ?>
    <?php $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL] . "group/" . $data['group']->guid . "/members/"); ?>
    <?php $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL] . "group/" . $data['group']->guid . "/subgroups/"); ?>

    <!-- TODO: Add salesprojects here -->
    <!-- TODO: Projects list, Add project button -->
</div>