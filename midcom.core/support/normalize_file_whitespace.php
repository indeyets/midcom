#!/usr/bin/php
<?php
error_reporting(E_ALL);
require_once('normalize_whitespace_normalizer.php');
if ($argc < 2)
{
    $name = basename($argv[0]);
    echo "\nUsage: {$name} <files_list>\n";
    echo "  For example:\n";
    echo "  {$name} `find ~/svn/midcom/ -name '*.php'` \n\n";
    exit(1);
}
$files = array_slice($argv, 1);

if (!function_exists('file_put_contents'))
{
    function file_put_contents($file, &$data)
    {
        $fp = fopen($file, 'w');
        if (!$fp)
        {
            return false;
        }
        $ret = fwrite($fp, $data);
        fclose($fp);
        return $ret;
    }
}

$normalizer = new midcom_support_wsnormalizer();
foreach ($files as $file)
{
    $data = file_get_contents($file);
    $normalized = $normalizer->normalize($data);
    if ($data === $normalized)
    {
        unset($data, $normalized);
        continue;
    }
    file_put_contents($file, $normalized);
    unset($data, $normalized);
}

?>