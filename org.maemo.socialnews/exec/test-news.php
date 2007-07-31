<?php
$_MIDCOM->auth->require_valid_user();

$calculator = new org_maemo_socialnews_calculator();

$qb = midcom_db_article::new_query_builder();
$qb->add_constraint('topic.component', '=', 'net.nehmer.blog');
$qb->add_order('metadata.published', 'DESC');
$qb->set_limit(8);

$articles = $qb->execute();
$articles_array = array();

foreach ($articles as $article)
{
    $scores = $calculator->calculate_article($article);
    $score_string = '';
    foreach ($scores as $source => $score)
    {
        $score_string .= " {$source}: {$score}";
    }
    $score_string = trim($score_string);
    $article->extra3 = $score_string;
    $articles_array[sprintf('%003d', $scores['total'])."_{$article->guid}"] = $article;
}
krsort($articles_array);
echo "<ul>\n";
foreach ($articles_array as $article)
{
    echo "<li><a href=\"{$article->url}\">{$article->title}</a> ({$article->extra3})</li>\n";
}
echo "</ul>\n";
?>