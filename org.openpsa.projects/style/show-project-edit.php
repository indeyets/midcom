<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['project_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());
?>
<div class="main">
    <?php $view->display_form(); ?>
</div>
<div class="sidebar">
    <?php
    $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]."task/list/project/{$data['project']->guid}/");

    if ($data['project']->newsTopic)
    {
        $news_node = $nap->get_node($data['project']->newsTopic);
        if ($news_node)
        {
            echo "<div class=\"area\">\n";
            $_MIDCOM->dynamic_load($news_node[MIDCOM_NAV_RELATIVEURL]."latest/4");
            echo "<p><a href=\"{$news_node[MIDCOM_NAV_RELATIVEURL]}\">".$data['l10n']->get('news area')."</a></p>\n";
            echo "</div>\n";
        }
    }
    if ($data['project']->forumTopic)
    {
        $forum_node = $nap->get_node($data['project']->forumTopic);
        if ($forum_node)
        {
            echo "<div class=\"area\">\n";
            $_MIDCOM->dynamic_load($forum_node[MIDCOM_NAV_RELATIVEURL]."latest/4");
            echo "</div>\n";
        }
    }
    ?>
</div>