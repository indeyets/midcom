<?php
// Available request keys: article, datamanager, edit_url, delete_url, create_urls

$data =& $_MIDCOM->get_custom_context_data('request_data');
$view = $data['view_article'];

$published = sprintf($data['l10n']->get('posted on %s.'), strftime('%Y-%m-%d %T %Z', $data['article']->created));
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

    <p class="permalink"><a href="&(permalink);" rel="bookmark"><?php $data['l10n_midcom']->show('permalink'); ?></a></p>
    
    <?php
    if (array_key_exists('comments_url', $data))
    {
        $_MIDCOM->dynamic_load($data['comments_url']);
    }
    ?>
    <p><a href="&(prefix);"><?php $data['l10n_midcom']->show('back'); ?></a></p>
</div>
