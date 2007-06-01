<?php
// Available request keys: datamanager, article, view_url
$view['title'] = $data['article']->title;

?>

<div class="hentry" style="clear: left;">
    <a href="&(data['view_url']);" rel="bookmark">&(view['title']:h);</a>&nbsp;&(data['vote_count']);
</div>