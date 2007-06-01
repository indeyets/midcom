<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$thread =& $data['thread'];
$latest_post =& $data['latest_post'];
$reply_count = $thread->posts - 1;
?>

<li class="thread">
    <h2><a href="&(prefix);&(thread.name);/">&(thread.title);</a></h2>
    <div class="posts">
        <?php echo $data['l10n']->get(sprintf('%s replies, latest post by %s on %s', "<strong>{$reply_count}</strong>", "<strong>{$latest_post->sendername}</strong>", "<strong>".strftime('%x %X', $latest_post->metadata->published)."</strong>")); ?>
    </div>
</li>