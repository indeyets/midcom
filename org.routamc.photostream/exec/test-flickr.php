<?php
$_MIDCOM->auth->require_valid_user();

$user = $_MIDCOM->auth->user->get_storage();

$photostream_id = null;
$node_qb = midcom_db_topic::new_query_builder();
$node_qb->add_constraint('component', '=', 'org.routamc.photostream');
$nodes = $node_qb->execute();
foreach ($nodes as $node)
{
    if (!$node->can_do('midgard:create'))
    {
        // Skip this one
        continue;
    }
    
    $photostream_id = $node->id;
}

if (is_null($photostream_id))
{
    die("There are no photostreams you can write to.");
}

$num = 10;
if (array_key_exists('num', $_GET))
{
    $num = $_GET['num'];
}

$flickr = org_routamc_photostream_importer::create('flickr', $photostream_id);
$photos = $flickr->get_flickr_photos($user, true, $num);

if ($photos)
{
    echo "<h1>We got these photos from Flickr</h1>\n";
    echo "<ul>\n";
    foreach ($photos as $photo)
    {
        echo "<li>\n";
        echo "<a href=\"{$photo['url']}\">{$photo['title']}</a>";
        echo " (" . strftime('%x %X', $photo['taken']) . ")";
        echo "</li>\n";
    }
    echo "</ul>\n";
}
else
{
    echo "Failed to get photos, last error is {$flickr->error}";
}
?>