<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<poll guid="<?php echo $data['article']->guid; ?>">
    <text><![CDATA[<?php echo $data['article']->title; ?>]]></text>
<?php
    $options = $data['qb_options']->execute();
    $i = 0;

    $qb_votes = net_nemein_quickpoll_vote_dba::new_query_builder();
    $qb_votes->add_constraint('article', '=', $data['article']->id);
    $total_votes = $qb_votes->count_unchecked();
    foreach ($options as $option)
    {
        $i++;
        $percentage = round(100 / $total_votes * $option->votes);
        echo "    <resulttext guid=\"{$option->guid}\" result=\"{$i}\" votes=\"{$option->votes}\"><![CDATA[{$percentage}% {$option->title}]]></resulttext>\n";
    }
    
    echo "    <comments>\n";

    $additional = $data['config']->get('additional_vote_keys');
    $comments = $data['qb_comments']->execute();
    foreach ($comments as $comment)
    {
        $metadata = $comment->get_metadata();
        if (!$metadata->is_approved())
        {
            // Skip
            continue;
        }

        echo "        <comment";
        echo " guid=\"{$comment->guid}\"";

        foreach ($additional as $field)
        {
            echo " {$field}=\"" .  $comment->parameter('net.nemein.quickpoll', $field) . "\"";
        }
                
        echo "><![CDATA[{$comment->comment}]]></comment>\n";
    }
    echo "    </comments>\n";
    ?>
</poll>