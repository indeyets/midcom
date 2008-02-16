<?php
// Available request keys: controller, schema, schemadb
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$post =& $data['parent_post'];
$post_view =& $data['view_parent_post'];
?>

<div class="description">
    &(post_view['content']:h);
</div>
<div class="net_nemein_discussion_tree_toolbar">
<?php
    if (   $post->status != NET_NEMEIN_DISCUSSION_NEW
        && $post->can_do('net.nemein.discussion:moderation'))
    {
        $logs = $post->get_logs();
        if (count($logs) > 0)
        {
            echo "<div class=\"net_nemein_discussion_moderation_history\">\n";
            echo "    <h3>".$data['l10n']->get('moderation history')."</h3>\n";
            echo "    <ol>\n";
            foreach ($logs as $time => $log)
            {
                $reported = strftime('%x %X', strtotime("{$time}Z"));
                echo "        <li class=\"{$log['action']}\">".$data['l10n']->get(sprintf('%s: %s by %s (from %s)', "<span class=\"date\">$reported</span>", "<span class=\"action\">" . $data['l10n']->get($log['action']) . "</span>", $log['reporter'], $log['ip']))."</li>\n";
            }
            echo "    </ol>\n";
            echo "</div>\n";
        }
    }
    
    echo $data['post_toolbar']->render();
    ?>
</div>