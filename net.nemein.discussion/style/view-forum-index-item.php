<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$forum =& $view_data['forum'];
$latest_thread =& $view_data['latest_thread'];
$latest_post =& $view_data['latest_post'];
?>

<li class="forum">
    <h2><a href="&(prefix);&(forum.name);/">&(forum.extra);</a></h2>
    <div class="posts">
        <?php
        if ($latest_post)
        {
            ?>
            latest post is <a class="subject url" href="&(prefix);&(forum.name);/read/&(latest_post.guid);.html">&(latest_post.subject);</a> by <strong>&(latest_post.sendername);</strong> on 
            <strong><?php echo strftime('%x %X', $latest_post->created); ?></strong>
            <?php
        }
        ?>
    </div>
</li>