<?php
//$data =& $_MIDCOM->get_custom_context_data('request_data');

$published = sprintf($data['l10n']->get('posted on %s.'), "<abbr title=\"" . strftime('%Y-%m-%dT%H:%M:%S%z', $data['item']->metadata->published) . "\">" . strftime('%x %X', $data['item']->metadata->published) . "</abbr>");
$view = $data['item'];
$title = $data['item']->title;
$url = $data['permalinks']->create_permalink($view->guid);
$topic_counter = $data['counters']['topic'];
$item_counter = $data['counters']['topic_item'];
$items_counter = $data['counters']['items'];
?>
<div class="hentry topic_item_counter_&(item_counter);" style="clear: left;">
    <h2 class="entry-title"><a href="&(url);" rel="bookmark">&(title:h);</a></h2>
    <p class="published">
        &(published:h);
    </p>

</div>