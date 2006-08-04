<?php
$view_data =& $GLOBALS['midcom']->get_custom_context_data('request_data');
$view = $view_data['project_dm'];
$nap = new midcom_helper_nav();
$node = $nap->get_node($nap->get_current_node());

echo "<div class=\"org_openpsa_helper_box status\">\n";
echo "<h3>".$view_data['l10n']->get('status')."</h3>\n";

echo "<div class=\"current-status {$view_data['project']->status_type}\">".$view_data['l10n']->get('project status').': '.$view_data['l10n']->get($view_data['project']->status_type)."</div>\n";

if (array_key_exists($_MIDGARD['user'], $view_data['project']->resources))
{
    echo $view_data['l10n']->get('you are project participant');
}
elseif (array_key_exists($_MIDGARD['user'], $view_data['project']->contacts))
{
    echo $view_data['l10n']->get('you are project subscriber');
    echo '<form method="post" class="subscribe" action="'.$node[MIDCOM_NAV_FULLURL].'project/'.$view_data['project']->guid.'/unsubscribe/"><input type="submit" class="unsubscribe" value="'.$view_data['l10n']->get('unsubscribe').'" /></form>';
}
else
{
    echo $view_data['l10n']->get('you are not subscribed to project'); 
    echo '<form method="post" class="subscribe" action="'.$node[MIDCOM_NAV_FULLURL].'project/'.$view_data['project']->guid.'/subscribe/"><input type="submit" value="'.$view_data['l10n']->get('subscribe').'" /></form>';
}
echo "</div>\n";
?>
<div class="main">
    <?php $view->display_view(); ?>
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
            echo "<p><a href=\"{$news_node[MIDCOM_NAV_FULLURL]}\">".$view_data['l10n']->get('news area')."</a></p>\n";            
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