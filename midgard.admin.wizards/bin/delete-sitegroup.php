<?php
if (count($argv) != 5)
{
    die("Usage: php delete-sitegroup.php sitegroup midgardconffile username password\n");
}

$sitegroup = (int) $argv[1];
$conffile = $argv[2];

if (version_compare(mgd_version(), '1.9.0alpha', '>='))
{
    $midgard = new midgard_connection();
    $midgard->open('bergietest');
    //$midgard->set_sitegroup(1);
}
else
{
    mgd_config_init($conffile);
}

mgd_auth_midgard($argv[3], $argv[4]);

if (!$_MIDGARD['root'])
{
    die("Not a root user, aborting");
}

foreach ($_MIDGARD['schema']['types'] as $type => $null)
{
    echo "\nProcessing {$type}...\n";
    $qb = new midgard_query_builder($type);
    $qb->add_constraint('sitegroup', '=', $sitegroup);
    $qb->include_deleted();
    $objects = $qb->execute();
    $deleted = 0;
    $purged = 0;
    $failed = 0;
    foreach ($objects as $object)
    {
        if (!$object->metadata->deleted)
        {
            if (!$object->delete())
            {
                $failed++;
                continue;
            }
            $deleted++;
        }

        if (!$object->purge())
        {
            $failed++;
            continue;
        }
        $purged++;
    }
    
    echo "    purged {$purged}, deleted {$deleted} objects and failed to delete {$failed}.\n";
}

echo "\nDeleting sitegroup {$sitegroup}...";
$sg = mgd_get_sitegroup($sitegroup);
$sg->delete();
echo mgd_errstr() . "\n";
?>