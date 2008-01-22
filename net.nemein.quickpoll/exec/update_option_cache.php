<?php
$_MIDCOM->auth->require_admin_user();

$qb = net_nemein_quickpoll_option_dba::new_query_builder();
$qb->add_constraint('article', '<>', 0);
$options = $qb->execute();
foreach ($options as $option)
{
    $votes_qb = new midgard_query_builder('net_nemein_quickpoll_vote');
    $votes_qb->add_constraint('selectedoption', '=', $option->id);
    $votes_qb->add_order('article');
    $votes = $votes_qb->count();
    
    echo "Option {$option->title} of poll #{$option->article} has {$votes} votes... ";
    
    if ($option->votes != $votes)
    {
        $option->votes = $votes;
        $option->update();
        echo mgd_errstr();
    }
    
    echo "<br />\n";
}
?>