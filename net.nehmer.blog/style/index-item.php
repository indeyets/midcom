<?php
// Available request keys: datamanager, article, view_url

$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['datamanager']->get_content_html();

$published = sprintf($data['l10n']->get('posted on %s.'), strftime('%Y-%m-%d %T %Z', $data['article']->created));

if (array_key_exists('comments_enable', $data))
{
    $published .= " <a href=\"{$data['view_url']}#net_nehmer_comments_{$data['article']->guid}\">" . sprintf($data['l10n']->get('%s comments'), net_nehmer_comments_comment::count_by_objectguid($data['article']->guid)) . "</a>.";
}
?>

<div class="hentry" style="clear: left;">
    <h2 class="headline"><a href="&(data['view_url']);">&(view['title']:h);</a></h2>
    <p class="published">&(published:h);</p>
    <?php if (array_key_exists('image', $view) && $view['image']) { ?>
        <div style="float: left; padding: 5px;">&(view['image']:h);</div>
    <?php } ?>
    <p class="excerpt">&(view['abstract']:h);</p>
</div>