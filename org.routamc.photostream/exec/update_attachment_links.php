<?php
$_MIDCOM->auth->require_admin_user();
// Get us to full live mode
$_MIDCOM->cache->content->enable_live_mode();
while(@ob_end_flush());

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

// Get photos and call update_attachment_links for each.
$qb = org_routamc_photostream_photo_dba::new_query_builder();
$qb->add_constraint('id', '>', 0);
$photos = $qb->execute();
$count = count($photos);
echo "processing {$count} photos:<br/>\n";
foreach ($photos as $photo)
{
    echo "&nbsp;&nbsp;&nbsp;Processing photo GUID {$photo->guid}... ";
    flush();
    $photo->update_attachment_links();
    echo "done<br/>\n";
}
echo "All done.</p>\n";
flush();
?>