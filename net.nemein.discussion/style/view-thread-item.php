<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$post =& $data['post'];
$view =& $data['view_post'];

$posttime = $post->metadata->published;
?>

<li class="mfcomment">
    <a name="&(post.guid);"></a>
    <?php
    if ($post->subject)
    {
        echo "<h2 class=\"subject\"><a class=\"url\" href=\"{$prefix}read/{$post->guid}.html\">{$view['subject']}</a></h2>\n";
    }
    echo "<span class=\"commenter\">{$view['sendername']}</span>\n";
    echo "<abbr class=\"dtcommented\" title=\"".gmdate('Y-m-d\TH:i:s\Z', $posttime). "\">".strftime('%x %X', $posttime)."</abbr>\n";
    ?>
    <div class="description">
        <?php 
        echo $view['content']; 
        ?>
    </div>
    <?php
    if ($post->status < NET_NEMEIN_DISCUSSION_NEW)
    {
        $logs = $post->get_logs();
        if (count($logs) > 0)
        {
            echo "<h3>".$data['l10n']->get('moderation history')."</h3>\n";
            echo "<ul>\n";
            foreach ($logs as $time => $log)
            {
                $reported = strftime('%x %X', $time);
                echo "<li>".$data['l10n']->get(sprintf('%s: %s by %s (from %s)', $reported, $data['l10n']->get($log['action']), $log['reporter'], $log['ip']))."</li>\n";
            }
            echo "</ul>\n";
        }
    }
    
    echo $data['post_toolbar']->render();
    ?>
</li>