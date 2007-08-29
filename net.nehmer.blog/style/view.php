<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

//$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_article'];

$publish_time = $data['article']->metadata->published;
$published = sprintf($data['l10n']->get('posted on %s.'), strftime('%Y-%m-%d %T %Z', $publish_time));
$permalink = $_MIDCOM->permalinks->create_permalink($data['article']->guid);
$prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
?>

<div class="hentry">
    <h1 class="headline">&(view['title']:h);</h1>

    <p class="published">&(published);</p>
    <p class="excerpt">&(view['abstract']:h);</p>

    <div class="content">
        <?php if (array_key_exists('image', $view) && $view['image']) { ?>
            <div style="float: right; padding: 5px;">&(view['image']:h);</div>
        <?php } ?>

        &(view["content"]:h);
    </div>

    <p class="permalink" style="display: none;"><a href="&(permalink);" rel="bookmark"><?php $data['l10n_midcom']->show('permalink'); ?></a></p>
    
    <?php
    if (!empty($data['article']->extra3))
    {
        echo "<h2>{$data['l10n']->get('related stories')}</h2>\n";
        echo "<ul class=\"related\">\n";
        $relateds = explode('|', $data['article']->extra3);
        foreach ($relateds as $related)
        {
            if (empty($related))
            {
                continue;
            }

            $article = new midcom_db_article($related);
            if (   $article
                && $article->guid)
            {
                echo "<li><a href=\"{$_MIDCOM->get_host_prefix()}midcom-permalink-{$article->guid}\">{$article->title}</a></li>\n";
            }
        }
        echo "</ul>\n";
    }
    
    if (array_key_exists('comments_url', $data))
    {
        $_MIDCOM->dynamic_load($data['comments_url']);
    }
    ?>
    <p><a href="&(prefix);"><?php $data['l10n_midcom']->show('back'); ?></a></p>
</div>
