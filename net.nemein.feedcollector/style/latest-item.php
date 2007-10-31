<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$published = sprintf($data['l10n']->get('posted on %s.'), "<abbr title=\"" . strftime('%Y-%m-%dT%H:%M:%S%z', $data['item']->metadata->published) . "\">" . strftime('%x %X', $data['item']->metadata->published) . "</abbr>");
$view = $data['item'];
$title = $data['item']->title;
if(strlen($data['item']->url) > 0)
{
    $url = $data['item']->url;
}
else
{
    $url = $_MIDCOM->permalinks->create_permalink($view->guid);
}
?>
<div class="hentry" style="clear: left;">
    <h2 class="entry-title"><a href="&(url);" rel="bookmark">&(title:h);</a></h2>
    <p class="published">
        &(published:h);
    </p>
</div>