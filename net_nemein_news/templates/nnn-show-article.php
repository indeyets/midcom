<?php
/**
 * @package net_nemein_news
 */
?>
<div class="hentry">
    <h1 tal:content="net_nemein_news/article/title" class="entry-title">Headline</h1>

    <div tal:content="net_nemein_news/article/metadata/published" class="published">2007-08-01</div>

    <div tal:content="structure net_nemein_news/article/content" class="entry-content">
        Content
    </div>
</div>