<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view = $view_data['salesproject_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php 
          $view->display_view();
          echo "<div style=\"clear: both;\"></div>\n";
          //TODO: Configure whether to show in/both and reverse vs normal sorting ?
          echo "\n<!-- DL: {$node[MIDCOM_NAV_RELATIVEURL]}relatedto/render/{$view_data['salesproject']->guid}/both/normal -->\n";
          $_MIDCOM->dynamic_load("{$node[MIDCOM_NAV_RELATIVEURL]}relatedto/render/{$view_data['salesproject']->guid}/both/normal"); 
          echo "\n<!-- /DL: {$node[MIDCOM_NAV_RELATIVEURL]}relatedto/render/{$view_data['salesproject']->guid}/both/normal -->\n";
    ?>
</div>
<div class="sidebar">
</div>
