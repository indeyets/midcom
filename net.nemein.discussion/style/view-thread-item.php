<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$post =& $view_data['post'];
?>

<li class="mfcomment">
    <a name="&(post.guid);"></a>
    <?php
    if ($post->subject)
    {
        echo "<h2 class=\"subject\"><a class=\"url\" href=\"{$prefix}read/{$post->guid}.html\">{$post->subject}</a></h2>\n";
    }
    echo "<span class=\"commenter\">{$post->sendername}</span>\n";
    echo "<abbr class=\"dtcommented\" title=\"".gmdate('Y-m-d\TH:i:s\Z', $post->created). "\">".strftime('%x %X', $post->created)."</abbr>\n";
    ?>
    <div class="description">
        <?php echo Markdown($post->content); ?>
    </div>
    <?php
    if ($post->status < NET_NEMEIN_DISCUSSION_NEW)
    {
        $logs = $post->get_logs();
        if (count($logs) > 0)
        {
            echo "<h3>".$view_data['l10n']->get('moderation history')."</h3>\n";
            echo "<ul>\n";
            foreach ($logs as $time => $log)
            {
                $reported = strftime('%x %X', $time);
                echo "<li>".$view_data['l10n']->get(sprintf('%s: %s by %s (from %s)', $reported, $view_data['l10n']->get($log['action']), $log['reporter'], $log['ip']))."</li>\n";
            }
            echo "</ul>\n";
        }
    }
    
    echo $view_data['post_toolbar']->render();
    ?>
</li>