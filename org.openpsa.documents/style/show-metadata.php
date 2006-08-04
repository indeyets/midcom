<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view = $view_data['metadata_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php $view->display_view(); ?>
    
    <?php
    if (count($view_data['metadata_versions']))
    {
        ?>
        <div class="area versions">
            <h2><?php echo $view_data['l10n']->get('older versions'); ?></h2>
            <?php 
            foreach ($view_data['metadata_versions'] as $guid => $metadata)
            {
                $_MIDCOM->dynamic_load("{$node[MIDCOM_NAV_RELATIVEURL]}document_metadata/{$guid}/listview/");
            }
            ?>
        </div>
        <?php
    }
    ?>
</div>
<div class="sidebar">
    <?php midcom_show_style("show-search-form-simple"); ?>
    <?php midcom_show_style("show-directory-navigation"); ?>
</div>
