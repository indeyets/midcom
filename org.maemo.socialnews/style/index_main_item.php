<?php
$article = $data['article'];
$node = $data['node'];

$author_string = '';
if (isset($article->metadata->authors))
{
    $authors = explode('|', $article->metadata->authors);
    foreach ($authors as $author_guid)
    {
        if (empty($author_guid))
        {
            continue;
        }
        $author = new midcom_db_person($author_guid);
        if ($author->id == 1)
        {
            continue;
        }
        $author_string = $author->name;
    }
}

$node_string = "<a href=\"{$node[MIDCOM_NAV_FULLURL]}\" rel=\"category\">${node[MIDCOM_NAV_NAME]}</a>";

$date_string = "<abbr class=\"published\" title=\"" . strftime('%Y-%m-%dT%H:%M:%S%z', $data['article']->metadata->published) . "\">" . gmdate('Y-m-d H:i e', $article->metadata->published) . "</abbr>";
?>
<div class="hentry">
    <?php
    $media_params = $data['article']->list_parameters('net.nemein.rss:media');
    if (isset($media_params['thumbnail@url']))
    {
        echo "<a href=\"{$article->url}\"><img src=\"{$media_params['thumbnail@url']}\" class=\"thumbnail\" /></a>\n";
    }
    ?>

    <h2 class="entry-title"><a href="&(article.url);" class="url" rel="bookmark">&(article.title:h);</a></h2>

    <p class="entry-summary">&(article.abstract:h);</p>

    <div class="post-info">
        <?php
        net_nemein_favourites_admin::render_add_link($data['article']->__new_class_name__, $data['article']->guid);
        if (empty($author_string))
        {
            echo sprintf($data['l10n']->get('%s to %s with score %d (%d)'), $date_string, $node_string, $data['score'], $data['score_initial']);
        }
        else
        {
            echo sprintf($data['l10n']->get('%s to %s by %s with score %d (%d)'), $date_string, $node_string, $author_string, $data['score'], $data['score_initial']);
        }
        
        if (isset($data['attention']))
        {
            echo " " . sprintf($data['l10n']->get('your attention: %s%%'), round($data['attention'] * 100));
        }
        ?>
    </div>
</div>