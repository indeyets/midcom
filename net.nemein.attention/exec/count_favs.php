<?php
$_MIDCOM->auth->require_valid_user();

$_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
$_MIDCOM->load_library('net.nemein.tag');
$_MIDCOM->load_library('net.nemein.rss');
$qb = net_nemein_favourites_favourite_dba::new_query_builder();
$qb->add_constraint('metadata.creator', '=', $_MIDCOM->auth->user->guid);
$favs = $qb->execute();

$concepts = array();
$authors = array();
$sources = array();

$feed_categories = array();
$feed_qb = net_nemein_rss_feed_dba::new_query_builder();
$feeds = $feed_qb->execute();
foreach ($feeds as $feed)
{
    $feed_categories['feed:' . md5($feed->url)] = $feed->url;
}

foreach ($favs as $fav)
{
    $item_concepts = array();
    $object = $_MIDCOM->dbfactory->get_object_by_guid($fav->objectGuid);
    if (   !$object
        || !$object->guid)
    {
        // This object is no more
        $fav->delete();
        continue;
    }

    $tags = net_nemein_tag_handler::get_tags_by_guid($fav->objectGuid);
    $val = 1;
    if ($fav->bury)
    {
        $val = -1;
    }
    
    foreach ($tags as $tag => $url)
    {
        if (isset($item_concepts[$tag]))
        {
            // We counted this already, skip
            continue;
        }
        $item_concepts[$tag] = true;
        
        if (!isset($concepts[$tag]))
        {
            $concepts[$tag] = 0;
        }
        
        $concepts[$tag] += $val;
    }
    
    $object_authors = explode('|', substr($object->metadata->authors, 1, -1));
    foreach ($object_authors as $author)
    {
        $author_user = $_MIDCOM->auth->get_user($author);
        if (!$author_user)
        {
            continue;
        }
        
        if (!isset($authors[$author_user->name]))
        {
            $authors[$author_user->name] = 0;
        }
        $authors[$author_user->name] += $val;
    }
    
    if (   is_a($object, 'midgard_article')
        && strpos($object->extra1, '|') !== false)
    {
        // Special handling for blog articles
        
        $categories = explode('|', substr($object->extra1, 1, -1));
        foreach ($categories as $category)
        {
            if (empty($category))
            {
                continue;
            }
            
            if (substr($category, 0, 5) == 'feed:')
            {
                // RSS feed information, can be used for "sources" handling
                $feed_url = str_replace('&', '&amp;', $feed_categories[$category]);
                if (!isset($feed_url))
                {
                    // Deleted feed, skip
                    continue;
                }
                
                if (!isset($sources[$feed_url]))
                {
                    $sources[$feed_url] = 0;
                }
                $sources[$feed_url] += $val;
                
                continue;
            }
            
            $tag = strtolower($category);
            if (isset($item_concepts[$tag]))
            {
                // We counted this already, skip
                continue;
            }
            $item_concepts[$tag] = true;
            
            if (!isset($concepts[$tag]))
            {
                $concepts[$tag] = 0;
            }
            
            $concepts[$tag] += $val;
        }
    }
}

arsort($concepts);
arsort($authors);
arsort($sources);

echo "<h2>Concepts</h2>\n";
echo "<ul>\n";
$highest = 0;
foreach ($concepts as $concept => $score)
{
    if ($score > $highest)
    {
        $highest = $score;
    }
    $score = 1 / $highest * $score;
    if ($score < -1)
    {
        $score = -1;
    }
    
    $object = net_nemein_attention_concept_dba::get_concept($concept, $_MIDGARD['user'], 'web');
    $object->source = $_SERVER['SERVER_NAME'];
    $object->value = $score;
    $object->update();
    echo "<li>{$concept}: {$score}</li>\n";
}
echo "</ul>\n";

echo "<h2>Authors</h2>\n";
echo "<ul>\n";
$highest = 0;
foreach ($authors as $author => $score)
{
    if ($score > $highest)
    {
        $highest = $score;
    }
    $score = 1 / $highest * $score;
    if ($score < -1)
    {
        $score = -1;
    }
    echo "<li>{$author}: {$score}</li>\n";
}
echo "</ul>\n";

echo "<h2>Sources</h2>\n";
echo "<ul>\n";
$highest = 0;
foreach ($sources as $source => $score)
{
    if ($score > $highest)
    {
        $highest = $score;
    }
    $score = 1 / $highest * $score;
    if ($score < -1)
    {
        $score = -1;
    }
    echo "<li>{$source}: {$score}</li>\n";
}
echo "</ul>\n";
?>