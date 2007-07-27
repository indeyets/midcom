<?php
// Available request data: comments, objectguid, comment, display_datamanager
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$comment = $data['comment'];

if (version_compare(mgd_version(), '1.8', '>='))
{
    $creator = $comment->metadata->creator;
    $created = $comment->metadata->created;
}
else
{
    $creator = $comment->creator;
    $created = $comment->created;
}

$user =& $_MIDCOM->auth->get_user($creator);
if ($user)
{
    $username = "{$user->name} ({$user->username})";
}
else
{
    $username = $data['l10n_midcom']->get('anonymous');
}
$ip = $comment->ip ? $comment->ip : '?.?.?.?';
$metadata = sprintf($data['l10n']->get('creator: %s, created %s, source ip %s.'),
    $username, strftime('%x %X', $created), $ip);
?>
<form action="&(_SERVER['REQUEST_URI']);" method="post" class="net_nehmer_comment_admintoolbar">
<input type="hidden" name="net_nehmer_comment_adminsubmit" value="1" />
<input type="hidden" name="guid" value="&(comment.guid);" />

<p class="audit">
    <input type="submit" name="action_delete" value="<?php $data['l10n_midcom']->show('delete'); ?>" />
    &(metadata);
</p>
</form>

