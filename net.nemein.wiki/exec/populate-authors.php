<?php
$_MIDCOM->auth->require_admin_user();

$rcs =& $_MIDCOM->get_service('rcs');    
$qb = midcom_db_article::new_query_builder();
// TODO: Add this when wiki inserts all contributors to authors array
// $qb->add_constraint('metadata.authors', 'LIKE', "%|{$guid}|%");
$qb->add_constraint('topic.component', '=', 'net.nemein.wiki');
$pages = $qb->execute_unchecked();
foreach ($pages as $page)
{
    echo "Wiki page {$page->title}... ";
    $changed = false;
    $authors = explode('|', substr($page->metadata->authors, 1, -1));    
    $object_rcs = $rcs->load_handler($page);
    $history = $object_rcs->list_history();
    foreach ($history as $rev => $data) 
    {
        $user_guid = substr($data['user'], 5);
        if (!in_array($user_guid, $authors))
        {
            $authors[] = $user_guid;
            $changed = true;
        }
    }

    if ($changed)
    {
        $page->metadata->authors = '|' . implode('|', $authors) . '|';
        $page->update();
        echo "Authors added. ";
    }
    echo mgd_errstr();
    echo "<br />\n";
}
?>