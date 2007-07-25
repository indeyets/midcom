<?php
$_MIDCOM->auth->require_valid_user();

$calculator = new org_maemo_socialnews_calculator();

$qb = midcom_db_article::new_query_builder();
$qb->add_constraint('topic.component', '=', 'net.nehmer.blog');
$qb->add_order('metadata.published', 'DESC');
$qb->set_limit(20);

$articles = $qb->execute();
echo "<ul>\n";
foreach ($articles as $article)
{
    $score = $calculator->calculate_article($article);
    echo "<li><a href=\"{$article->url}\">{$article->title}</a> ({$score})</li>\n";
}
echo "</ul>\n";
?>