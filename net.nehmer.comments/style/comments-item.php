<?php
// Available request data: comments, objectguid, comment, display_datamanager
//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['display_datamanager']->get_content_html();

if (version_compare(mgd_version(), '1.8', '>='))
{
    $created = $data['comment']->metadata->published;
}
else
{
    $created = $data['comment']->created;
}

$published = sprintf(
    $data['l10n']->get('published by %s on %s.'),
    $view['author'],
    strftime('%x %X', $created)
);

$rating = '';
if ($data['comment']->rating > 0)
{
    $rating = ', ' . sprintf('rated %s', $data['comment']->rating);
}
?>

<div class="net_nehmer_comment_comment">
    <h3 class="headline">&(view['title']);&(rating);</h3>
    <div class="published">&(published);</div>
    
    <div class="content">&(view['content']:h);</div>
</div>