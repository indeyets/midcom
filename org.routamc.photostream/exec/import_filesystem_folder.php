<?php
/**
 * This script can take a long time 
 */
set_time_limit(0);

$_MIDCOM->auth->require_admin_user();
if(!isset($_GET['import_folder']))
{
	die("Set the filesystem path to ?import_folder= in your url");
}
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

$folder = org_routamc_photostream_importer::create('filesystem', $photostream_id); //function looks the $_GET
$folder->import_photos_directory();
flush();
?>