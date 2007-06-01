<?php
// Available request keys: filename, data, publication

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();

$published = sprintf($data['l10n']->get('posted on %s.'), $data['publication']->metadata->published);
?>

<div class="hentry" style="clear: left;">
    <h2 class="headline"><a href="&(data['view_url']);">&(view['title']:h);</a></h2>
    <p class="published">&(published);</p>
    <?php if (array_key_exists('image', $view) && $view['image']) { ?>
        <div style="float: left; padding: 5px;">&(view['image']:h);</div>
    <?php } ?>
    <p class="excerpt">&(view['abstract']:h);</p>
</div>