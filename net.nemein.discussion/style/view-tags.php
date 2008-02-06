<div class="net_nemein_discussion_tags">
    <h1><?php echo $data['forum']->extra; ?></h1>

    <ul class="cloud">
        <?php
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        
        foreach ($data['tags'] as $tag => $count)
        {
            $times = round($count / 10);
            $tag_label = $tag;
            while ($times > 0)
            {
                $times--;
                $tag_label = "<em>{$tag_label}</em>";
            }            
            echo "<li>";
            echo "<a href=\"{$prefix}tag/{$tag}/\" class=\"tag\" rel=\"tag\">{$tag_label}</a>";
            echo "</li>\n";
        }
        ?>
    </ul>
</div>