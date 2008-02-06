<?php
$debug = false;

$_MIDCOM->auth->require_admin_user();

// Set all posts to "NEW" status if they don't have a status
$post_qb = new midgard_query_builder('net_nemein_discussion_post');
$post_qb->add_constraint('status', '=', 0);
//$post_qb->set_limit(400);
$posts = @$post_qb->execute();
foreach ($posts as $post)
{
    $post->status = 4;
    if (!$debug)
    {
        echo "Updating #{$post->id} from {$post->sendername}<br />\n";
        $post->update();
        flush();
    }
}

// Update thread caches
$qb = new midgard_query_builder('net_nemein_discussion_thread');
$threads = $qb->execute();
foreach ($threads as $thread)
{   
    $posts = null;
    $post_qb = new midgard_query_builder('net_nemein_discussion_post');
    $post_qb->add_constraint('thread', '=', $thread->id);
    $post_qb->add_constraint('status', '>=', 3);
    $post_qb->add_order('metadata.published', 'ASC');
    // TODO: Add moderation checks
    $posts = $post_qb->execute();
    $latest_post = null;
    $first_post = null;
    if ($thread->latestpost)
    {
        $latest_post = new net_nemein_discussion_thread();
        $latest_post->get_by_id($thread->latestpost);
    }
    if ($thread->firstpost)
    {
        $first_post = new net_nemein_discussion_thread();
        $first_post->get_by_id($thread->firstpost);
    }
    foreach ($posts as $post)
    {
        if (   is_null($latest_post)
            || $post->metadata->published > $latest_post->metadata->published)
        {
            $latest_post = $post;
        }
        
        if (   is_null($first_post)
            || $post->metadata->published < $first_post->metadata->published)
        {
            $first_post = $post;
        }
    }
    
    if (   count($posts) != $thread->posts
        || (   is_object($latest_post)
            && $latest_post->id != $thread->latestpost)
        || (   is_object($latest_post)
            && $thread->latestposttime != strtotime($latest_post->metadata->published))
        || (   is_object($first_post)
            && $first_post->id != $thread->firstpost))
    {
        $thread->posts = count($posts);
        $thread->latestpost = $latest_post->id;
        
        if ($first_post)
        {
            $thread->firstpost = $first_post->id;
        }
        
        $thread->latestposttime = strtotime($latest_post->metadata->published);
        
        echo "Setting post count for thread \"{$thread->title}\" to {$thread->posts}, latest post to #{$thread->latestpost} and first post to #{$thread->firstpost}<br />\n";
        flush();
        
        if (!$debug);
        {
            $thread->update();
        }
    }
}
?>