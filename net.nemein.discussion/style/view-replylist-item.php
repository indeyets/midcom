<?php
$view_data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$post =& $view_data['post'];
$thread = new net_nemein_discussion_thread_dba($post->thread);

$nav = new midcom_helper_nav();
$forum = $nav->get_node($thread->node);
?>
<li class="mfcomment">
    <a class="subject url" href="&(forum[MIDCOM_NAV_FULLURL]);read/&(post.guid);.html">&(post.subject);</a>
    <span class="commenter">&(post.sendername);</span>
    <?php
    echo "<abbr class=\"dtcommented\" title=\"".gmdate('Y-m-d\TH:i:s\Z', $post->created). "\">".strftime('%x %X', $post->created)."</abbr>\n";
    ?>
</li>