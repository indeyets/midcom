<?php
$_MIDCOM->componentloader->load('org.routamc.gallery');
$_MIDCOM->componentloader->load('net.siriux.photos');
$_MIDCOM->auth->require_admin_user();
// Get us to full live mode
$_MIDCOM->cache->content->enable_live_mode();
while(@ob_end_flush());

if (   !isset($_REQUEST['stream_node_guid'])
    || empty($_REQUEST['stream_node_guid']))
{
    $qb = new MidgardQueryBuilder('midgard_topic');
    $qb->add_constraint('component', '=', 'org.routamc.photostream');
    $nodes = $qb->execute();
    if (empty($nodes))
    {
        echo "<h2>Error</h2><p>Could not find any nodes with component set to 'org.routamc.photostream', create one first</p>\n";
        return;
    }
?>
<h2>Please select node to migrate the photos to</h2>
<form method="get">
    <select name="stream_node_guid">
        <option value="">please choose node</option>
<?php
    foreach ($nodes as $node)
    {
        if (   isset($node->title)
            && !empty($node->title))
        {
            $title = $node->title;
        }
        else
        {
            $title = $node->extra;
        }
        // TODO: Figure out node path
        $path = 'TBD: /path/to/node';
        echo "            <option value='{$node->guid}'>{$title} ({$path})</option>\n";
    }
?>
    </select>
    <input type="submit" value="start conversion" />
</form>
<?php
    return;
}
$stream_node = new midcom_db_topic($_REQUEST['stream_node_guid']);
if (!$stream_node->id)
{
    echo "<h2>Error</h2>\n<p>Could not load node '{$_REQUEST['stream_node_guid']}', aborting</p>\n";
    return;
}
$schemadb = midcom_helper_datamanager2_schema::load_database($GLOBALS['midcom_component_data']['org.routamc.photostream']['config']->get('schemadb'));
$photo_field = false;
$tags_field = false;
foreach ($schemadb['photo']->fields as $name => $field)
{
    if ($field['type'] == 'photo')
    {
        $photo_field = $name;
    }
    if ($field['type'] == 'tags')
    {
        $tags_field = $name;
    }
}
if (   !$schemadb
    || !$photo_field)
{
    echo "<h2>Error</h2>\n<p>Could not load node schemadb or determine photo filed, aborting</p>\n";
    return;
}

echo "<p>Preparing...<br/>\n";
echo "<!-- send a lot of dummy data to make some browsers (*cough*IE*cough*) happier\n";
for ($i = 1; $i < 1041; $i++)
{
    echo '.';
    if ( ($i % 80) == 0)
    {
        echo "\n";
    }
}
echo "-->\n";
flush();
// Disable limits
ini_set('memory_limit', -1);
ini_set('max_execution_time', 0);

$qb = new MidgardQueryBuilder('midgard_topic');
$qb->add_constraint('component', '=', 'net.siriux.photos-convertme-org.routamc.gallery');
$nodes = $qb->execute();
if (empty($nodes))
{
    echo "<p>No conversion candidates found (hint: set their componen to 'net.siriux.photos-convertme-org.routamc.gallery')</p>\n";
    return;
}
echo "<p>\n";

$node_photo_map = array();
foreach ($nodes as $node)
{
    if (!$node->id)
    {
        echo "&nbsp;&nbsp;&nbsp;ERROR: Could not get topic #{$param->oid}, skipping<br>\n";
        continue;
    }
    echo "&nbsp;&nbsp;&nbsp;INFO: processing node #{$node->id} ({$node->extra})...<br/>\n";
    flush();
    $node_photo_map[$node->id] = array();
    $qba = midcom_db_article::new_query_builder();
    $qba->add_constraint('topic', '=', $node->id);
    $qba->add_constraint('up', '=', 0);
    $nsphotos = $qba->execute();
    if (empty($nsphotos))
    {
        echo "&nbsp;&nbsp;&nbsp;ERROR: Could not find any articles in topic #{$node->id}, setting to 'window' mode<br/>\n";
        // Change node component and set gallery type
        $node->parameter('midcom', 'component', 'org.routamc.gallery');
        $node->component = 'org.routamc.gallery';
        $node->parameter('org.routamc.gallery', 'gallery_type', ORG_ROUTAMC_GALLERY_TYPE_WINDOW);
        $node->update();
        continue;
    }
    foreach ($nsphotos as $article)
    {
        // TODO: set the config somehow
        $original_guid = $article->guid;
        $nsphoto = new siriux_photos_Photo($article->id, null, $node);
        if (   !$nsphoto->id
            || empty($nsphoto->fullscale))
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: Could not instantiate article #{$article->id} as siriux_photos_Photo, skipping<br/>\n";
            continue;
        }
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INFO: Processing article #{$nsphoto->id}...<br/>\n";
        flush();
        $att = mgd_get_object_by_guid($nsphoto->fullscale);
        if (!$att->id)
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: Could not load attachment'{$nsphoto->fullscale}', skipping<br/>\n";
            continue;
        }
        $basename = preg_replace('/^fullscale_/i', '', $att->name);
        $tmpfile = tempnam('/tmp', 'or_ps_convert_');
        unlink($tmpfile);
        $tmpfile .= '_' . $basename;

        /*
        // DEBUG: Break here for a moment
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Would create tmpfile {$tmpfile} and then do the actual conversions, but skipping that for now (SAFETY)<br>\n";
        continue;
        */

        // Create working copy
        $dst = fopen($tmpfile, 'w');
        if (!$dst)
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: Could not open filehandle for '{$dst}', skipping<br/>\n";
            continue;
        }
        $src = mgd_open_attachment($att->id, 'r');
        if (!$src)
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: Could not open filehandle to attachent #{$att->id}, errstr: " . mgd_errstr() . "<br/>\n";
            continue;
        }
        while (!feof($src))
        {
            $buffer = fread($src, 131072); /* 128 kB */
            fwrite($dst, $buffer, 131072);
        }
        fclose($src);
        fclose($dst);

        // TODO: Figure out photographer person from the string, now we just store it for later and use the uploader
        $photo = new org_routamc_photostream_photo_dba();
        $photo->node = $stream_node->id;
        $photo->title = $nsphoto->title;
        $photo->description = $nsphoto->description;
        if ($nsphoto->taken > 3600)
        {
            $photo->taken = $nsphoto->taken;
        }
        else if ($nsphoto->article->created > 3600)
        {
            $photo->taken = $nsphoto->article->created;
        }
        else if (strtotime($nsphoto->article->created) > 3600)
        {
            $photo->taken = strtotime($nsphoto->article->created);
        }

        $photo->photographer = $nsphoto->article->author; // Technically uploader but good enough for now
        if (!$photo->create())
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: Could not create new org_routamc_photostream_photo_dba, errstr: " . mgd_errstr() . "<br/>\n";
            unlink($tmpfile);
            continue;
        }
        $photo->parameter('midcom.helper.datamanager2', 'schema_name', 'photo');
        $photo->parameter('org.routamc.photostream', 'attribution', $nsphoto->photographer);
        $photo->parameter('org.routamc.photostream', 'net.siriux.photos:guid', $original_guid);

        // Load datamanager
        $datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        if (   !$datamanager
            || !$datamanager->autoset_storage($photo))
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: Could instantiate DM2 for photo #{$photo->id}, errstr: " . mgd_errstr() . "<br/>\n";
            $photo->delete();
            unlink($tmpfile);
            continue;
        }
        if (!$datamanager->types[$photo_field]->set_image($basename, $tmpfile, $basename))
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: Could set_image in photo #{$photo->id}, call was: set_image('{$basename}', '{$tmpfile}', '{$basename}')<br/>\n";
            $photo->delete();
            unlink($tmpfile);
            continue;
        }
        if ($tags_field)
        {
            $datamanager->types[$tags_field]->value = $nsphoto->keywords;
        }
        if (!$datamanager->save())
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: DM2 failed to save photo #{$photo->id}<br/>\n";
            $photo->delete();
            continue;
        }
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INFO: Created new photo with id #{$photo->id}<br/>\n";
        $link = new org_routamc_gallery_photolink_dba();
        $link->node = $node->id;
        $link->photo = $photo->id;
        if (!$link->create())
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: could not create link between photo #{$photo->id} and gallery node #{$node->id}, errstr: " . mgd_errstr() ."<br/>\n";
        }
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INFO: Created link #{$link->id} between photo #{$photo->id} and gallery node #{$node->id}<br/>\n";
        $tmparticle = new midcom_db_article($nsphoto->article->id);
        if (!$tmparticle->delete())
        {
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR: failed to delete article #{$nsphoto->id}<br/>\n";
        }
        $node_photo_map[$node->id][$link->id] = $photo->id;
        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;INFO: Done with article #{$nsphoto->id} (converted to photo #{$photo->id} and link #{$link->id})<br/>\n";
        // Clean up if set_image did not.
        if (file_exists($tmpfile))
        {
            unlink($tmpfile);
        }
    }
    // Change node component and set gallery type
    $node->parameter('midcom', 'component', 'org.routamc.gallery');
    $node->parameter('org.routamc.gallery', 'gallery_type', ORG_ROUTAMC_GALLERY_TYPE_HANDPICKED);
    $node->component = 'org.routamc.gallery';
    $node->update();
}
echo "All done.</p>\n";

?>