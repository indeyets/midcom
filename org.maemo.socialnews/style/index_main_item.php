<?php
$article = $data['article'];
$node = $data['node'];

$author_string = '';
if (isset($article->metadata->authors))
{
    $authors = explode('|', $article->metadata->authors);
    foreach ($authors as $author_guid)
    {
        $author = new midcom_db_person($author_guid);
        $author_string = $author->name;
    }
}
?>
<div class="hentry">
    <h2 class="entry-title"><a href="&(article.url);" class="url">&(article.title:h);</a></h2>

    <p class="entry-excerpt">&(article.abstract:h);</p>

    <div class="post-info">in <a href="&(node[MIDCOM_NAV_FULLURL]);">&(node[MIDCOM_NAV_NAME]:h);</a> by &(author_string);</div>
</div>