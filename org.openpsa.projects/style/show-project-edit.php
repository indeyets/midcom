<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view = $view_data['project_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php $view->display_form(); ?>
</div>
<div class="sidebar">
    <?php 
    $GLOBALS["midcom"]->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."task/list/project/{$view_data['project']->guid}/"); 
    
    if ($view_data['project']->newsTopic)
    {
        $news_node = $nap->get_node($view_data['project']->newsTopic);
        if ($news_node)
        {
            echo "<div class=\"area\">\n";
            $GLOBALS["midcom"]->dynamic_load($news_node[MIDCOM_NAV_RELATIVEURL]."latest/4"); 
            echo "<p><a href=\"{$news_node[MIDCOM_NAV_RELATIVEURL]}\">".$view_data['l10n']->get('news area')."</a></p>\n";
            echo "</div>\n";
        }
    }
    if ($view_data['project']->forumTopic)
    {
        $forum_node = $nap->get_node($view_data['project']->forumTopic);
        if ($forum_node)
        {
            echo "<div class=\"area\">\n";
            $GLOBALS["midcom"]->dynamic_load($forum_node[MIDCOM_NAV_RELATIVEURL]."latest/4"); 
            echo "</div>\n";
        }
    }    
    ?>
</div>