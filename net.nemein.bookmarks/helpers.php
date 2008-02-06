<?php
/**
 * @package net.nemein.bookmarks
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
function net_nemein_bookmarks_helper_list_tags($topic_id)
{
    $tags = array();
    $bookmarks = mgd_list_topic_articles($topic_id);
    if ($bookmarks)
    {
        while ($bookmarks->fetch())
        {
            $bookmark_tags = explode(" ",$bookmarks->content);
            foreach ($bookmark_tags as $tag)
            {
                // Store reference to the bookmark per each tag
                $tags[$tag][$bookmarks->url] = $bookmarks;
            }
        }
    }
    ksort($tags);
    reset($tags);
    return $tags;
}

function net_nemein_bookmarks_helper_list_tags_of_bookmark($bookmark)
{
    $tags = array();
    $bookmark_tags = explode(" ",$bookmark["tags"]);

    if ($bookmark_tags)
    {
        foreach ($bookmark_tags as $tag)
        {
            // Store reference to the bookmark per each tag
            $tags[] = $tag;
        }
    }
    return $tags;
}

function net_nemein_bookmarks_helper_list_bookmarks_by_tag($topic_id, $tag)
{
    $tag_bookmarks = array();
    $bookmarks = mgd_list_topic_articles($topic_id);
    if ($bookmarks)
    {
        while ($bookmarks->fetch())
        {
            $bookmark_tags = explode(" ",$bookmarks->content);
            if (in_array($tag,$bookmark_tags))
            {
                // Return all bookmarks matching the tag
                $tag_bookmarks[$bookmarks->url] = $bookmarks;
            }
        }
    }
    return $tag_bookmarks;
}

function net_nemein_bookmarks__list_articles($topic_id, $sort_order = "reverse created")
{
    $ids = mgd_fetch_to_array(mgd_list_topic_articles($topic_id));
    if (!$ids)
    {
        return FALSE;
    }
    if (count($ids) == 0)
    {
        return Array();
    }

    // get articles
    $result = Array();
    foreach ($ids as $id)
    {
        $article = mgd_get_article($id);
        if ($article)
        {
            $result[] = $id;
        }
        else
        {
        debug_add("Failed to get article, last Midgard Error: " . mgd_errstr(), MIDCOM_LOG_ERROR);
        }
    }
    if ($sort_order)
    {
        mgd_sort_id_array($result, $sort_order, "MidgardArticle");
    }
    return $result;
}

function net_nemein_bookmarks_delicius_put($url, $title, $extended, $tag, $username, $password)
{
    require_once 'Services/Delicious.php';
    $dlc = &new Services_Delicious($username, $password);
    $result = $dlc->addPost($url, $title, $extended, $tag);
    if (PEAR::isError($result))
    {
        debug_add("Failed to add " . $url . " to delicius: " . $result->getMessage());
        die($result->getMessage());
        return false;
    }
    else
    {
        return true;
    }
}

function net_nemein_bookmarks_delicius_getlist($username, $password)
{
    require_once 'Services/Delicious.php';
    $dlc = &new Services_Delicious($username, $password);
    $posts = $dlc->getAllPosts();
    return $posts;
}
?>