<?php
$_MIDCOM->auth->require_admin_user();

$qb = net_nehmer_comments_comment::new_query_builder();
$qb->add_constraint('metadata.creator', '<>', '');
$qb->begin_group('OR');
    $qb->add_constraint('metadata.authors', '=', '');
    $qb->add_constraint('author', '=', '');
$qb->end_group();

$comments = $qb->execute();
foreach ($comments as $comment)
{
    $author = $_MIDCOM->auth->get_user($comment->metadata->creator);
    if (!$author->guid)
    {
        continue;
    }
    
    $comment->metadata->authors = "|{$author->guid}|";
    
    if ($author->name)
    {
        $comment->author = $author->name;
    }
    
    echo "Updating comment {$comment->guid} to author {$author->name} (#{$author->id})... ";
    $comment->update();
    echo mgd_errstr() . "<br />\n";
}
?>