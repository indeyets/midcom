<?php
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
$view = $data['post_for_tree'];
$reply_url = $prefix . 'reply/' . $view['guid'] . '/';

if($view['sender'] != '')
{
    $sender = new midcom_db_person($view['sender']);
    if($sender)
    {
        $sender_str = '<a href="' . $data['person_link_prefix'] . $sender->guid . '/">' . $sender->name . '</a>';
    }
}
else
{
    $sender_str = '<a href="' . $view['senderurl'] . '" target="_blank">' . $view['sendername'] . '</a>';
}
$post_created = strftime('%x %X', $view['created']);
?>
<li>
    <div class="times">&(post_created:h);</div>
    <div class="sender">&(sender_str:h);</div>
    <div class="subject"><a href="&(reply_url);">&(view['subject']:h);</a></a>
</li>