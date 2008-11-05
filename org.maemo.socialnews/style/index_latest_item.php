<?php
$article = $data['article'];
$node = $data['node'];
$node_string = "<a href=\"{$node[MIDCOM_NAV_FULLURL]}\" rel=\"category\">${node[MIDCOM_NAV_NAME]}</a>";
?>
<div class="hentry">   
    <h4 class="entry-title">
        <a href="&(article.url);" class="url" rel="bookmark">&(article.title:h);</a>
        <span class="node"><?php
        echo sprintf($data['l10n']->get('in %s'), $node_string);
        ?>
        </span>
    </h4>

    <div class="post-info">
        <?php
        net_nemein_favourites_admin::render_add_link($data['article']->__mgdschema_class_name__, $data['article']->guid);
        ?>
    </div>
</div>