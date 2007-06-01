<?php

/**
 * Autoconf Script which scans the system for all required utilities and prerequisits.
 * Prints all related <i>/etc/midgard/midcom.conf</i> lines for the autodetected information.
 * 
 * Requires the which-Utility for proper operation, it is used to detect the installed utility
 * programs. This also means (obviously), that only utility programs available in $PATH will
 * be found.
 * 
 * If no argument is present, the detmined configuration is only written to stdout after the
 * debugging messages. Otherwise, the filename specified on the command line will be replaced
 * with the autodetected configuration. 
 * 
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:autoconf.php 3762 2006-07-30 16:04:01 +0000 (Sun, 30 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

$GLOBALS['configfile'] = '';

if ($argc == 1)
{
    $GLOBALS['outfile'] = null;
}
else if ($argc == 2)
{
    $GLOBALS['outfile'] = $argv[1];
}
else
{
?>
Usage: php autoconf.php [ output_filename ]
<?php
exit();
}

function add_option_to_configfile($option, $value)
{
    $GLOBALS['configfile'] .= "\$GLOBALS['midcom_config_site']['{$option}'] = {$value};\n";
}
function println_red($text)
{
    // echo "<span style='color: red;'>{$text}</span>\n";
    echo "{$text}\n";
}
function println_orange($text)
{
    // echo "<span style='color: orange;'>{$text}</span>\n";
    echo "{$text}\n";
}
function scan_for_utility($name, $executable, $utilityname, $required = true, $recommended_msg = '')
{
    echo "Looking for {$name}... ";
    $path = exec("which {$executable}");
    if ($path == '')
    {
        if ($required)
        {
            println_red("Critical: {$name} not found, this is a required part of MidCOM, please install it.");
            return false;
        }
        else
        {
            println_orange("Warning: {$name} not found. {$recommended_msg}");
            add_option_to_configfile("utility_{$utilityname}", 'null');
            return false;
        }
    }
    else
    {
        echo "{$name} found at {$path}.\n";
        add_option_to_configfile("utility_{$utilityname}", "'{$path}'");
        return $path;
    }
}

if (! exec ('which which'))
{
    die("The 'which' utility cannot be found. It is required for auto-configuration. Aborting.");
}

echo "Detecting Cache Handlers... ";

if (! function_exists('dba_handlers'))
{
    println_orange('dba_handlers unavailable, cannot autodetect.');
}
else
{
    $handlers = dba_handlers();
    echo "\n    Avaliable Handlers: " . implode($handlers, ", ") . "\n";
    if (in_array('db4', $handlers))
    {
        $handler = 'db4';
        echo "    Using the 'db4' handler.\n";
    }
    else if (in_array('db3', $handlers))
    {
        $handler = 'db3';
        echo "    Using the 'db3' handler.\n";
    }
    else if (in_array('db2', $handlers))
    {
        $handler = 'db2';
        echo "    Using the 'db2' handler.\n";
    }
    else if (in_array('gdbm', $handlers))
    {
        $handler = 'gdbm';
        println_orange("    Using the 'gdbm' handler, this is not deeply tested, the installation of the BerkleyDB libraries is recommended.");
    }
    else if (in_array('flatfile', $handlers))
    {
        $handler = 'flatfile';
        println_orange("    Using the 'flatfile' handler as a fallback, this is not deeply tested, the installation of the BerkleyDB libraries is recommended.");
    }
    else
    {
        $handler = null;
        println_red("    Critical: No valid DBA-Handler has been found. Using the flatfile backend, which is incomplete and may produce strange side-effects.\n     Installation of BerkleyDB is strongly recommended.");
    }
    
    if (is_null($handler))
    {
        add_option_to_configfile('cache_module_content_backend', "Array ('driver' => 'flatfile')");
        add_option_to_configfile('cache_module_content_uncached', 'true');
        add_option_to_configfile('cache_module_nap_backend', "Array ('driver' => 'flatfile')");
    }
    else
    {
        add_option_to_configfile('cache_module_content_backend', "Array ('driver' => 'dba', 'handler' => '{$handler}')");
        add_option_to_configfile('cache_module_nap_backend', "Array ('driver' => 'dba', 'handler' => '{$handler}')");
    }
}

echo "Looking for ImageMagick... ";
$magick = `which mogrify`;
if ($magick == '')
{
    println_red("Critical: ImageMagick not found, this is a required part of MidCOM, please install it.");
}
else
{
    $magick_base = dirname($magick);
    echo "ImageMagick found in {$magick_base}.\n";
    add_option_to_configfile('utility_imagemagick_base', "'{$magick_base}/'");
}
//function scan_for_utility($name, $executable, $utilityname, $required = true, $recommended_msg = '')
scan_for_utility('find', 'find', 'find');
scan_for_utility('file', 'file', 'file');
scan_for_utility('UnZIP', 'unzip', 'unzip', false, 'UnZIP is recommended for bulk-upload processing (Windows ZIP files).');scan_for_utility('gzip', 'gzip', 'gzip', false, 'gzip is recommended for bulk-upload processing (Linux .tar.gz files).');
scan_for_utility('tar', 'tar', 'tar', false, 'tar is recommended for bulk-upload processing (Linux .tar.gz files).');
scan_for_utility('JPEGTran', 'jpegtran', 'jpegtran', false, 'JPEGTran is recommended for lossless JPEG rotation.');
scan_for_utility('JHead EXIF Reader', 'jhead', 'jhead', false, 'JHead is recommeded for pre 4.3 PHP installations, as the PHP EXIF reader is instable there.');
scan_for_utility('catdoc', 'catdoc', 'catdoc', false, 'The catdoc utility is recommended to convert Microsoft Word Documents into Plain Text for indexing with the MidCOM Indexer.');
scan_for_utility('pdftotext', 'pdftotext', 'pdftotext', false, 'The pdftotext utility is recommended to convert Adobe PDF Documents into Plain Text for indexing with the MidCOM Indexer.');
scan_for_utility('unrtf', 'unrtf', 'unrtf', false, 'The unrtf utility is recommended to convert Rich Text Format (RTF) Documents into Plain Text for indexing with the MidCOM Indexer.');

scan_for_utility('diff','diff','diff', false, 'diff is needed by the versioning library. You can also use the pear library Text_Diff');


$rcs = scan_for_utility('rcs', 'rcs','rcs',  true,  'You need the RCS utilities to have midcom save versions of your objects.');

if (!$rcs ) 
{
    echo "Could not find the RCS utilities you need to use the revision controll functions\n";
    echo "Therefore they are disabled.\n";
    add_option_to_configfile('midcom_services_rcs_use', "false");
    
} else {
    
    $prefix = dirname (exec ('which repligard'));
    if ($prefix == '/usr/bin' || $prefix == '/usr/local/bin') {
        $dir = '/var/lib/midgard/rcs';
    } else {
        $prefix = str_replace('/bin', '' , $prefix);
        $dir = $prefix . '/var/lib/midgard/rcs';
    }
    
    if (!is_dir($dir)) {
        echo "The RCS directory ($dir) is missing! You must create this directory\n";
        echo "or change the RCS root setting to another directory!\n";
    } 
    
    add_option_to_configfile('midcom_services_rcs_root', "'$dir'");
    add_option_to_configfile('midcom_services_rcs_bin_dir', "'$prefix'");
    add_option_to_configfile('midcom_services_rcs_use', "true");
    
}



// Check Memory Limit
echo "Checking Memory Limit... ";
$cur_limit = ini_get('memory_limit');
$last_char = substr($cur_limit, -1);
if ($last_char == 'M')
{
    $cur_limit = substr($cur_limit, 0, -1) * 1024 * 1024;
}
else if ($last_char == 'K')
{
    $cur_limit = substr($cur_limit, 0, -1) * 1024;
}
else if ($last_char == 'G')
{
    $cur_limit = substr($cur_limit, 0, -1) * 1024 * 1024 * 1024;
}
if ($cur_limit >= (20 * 1024 * 1024))
{
    echo "{$cur_limit} Bytes... OK\n";
}
else
{
    echo "{$cur_limit} Bytes... Memory Limit is below 20 MB, it is recommeded to set this in the php.ini file.";
    $GLOBALS['configfile'] .= "ini_set('memory_limit', '20M');\n";
}

// - Check PHP / PEAR dependencies



?>


Configuration File proposal, place into /etc/midgard/midcom.conf and 
modify as needed with other options outlined in the API documentation of
midcom_config.php:

----------------------------------------------------------------------------
<?php 
echo "<?php\n";
echo $GLOBALS['configfile'];
echo "?>\n"; 
?>
----------------------------------------------------------------------------
<?php

if (! is_null($GLOBALS['outfile']))
{
    $handle = fopen($GLOBALS['outfile'], 'w');
    if ($handle === false)
    {
        die ("Could not open output file {$GLOBALS['outfile']} for writing.\n");
    }
    fwrite ($handle, "<?php\n");
    fwrite ($handle, $GLOBALS['configfile']);
    fwrite ($handle, "?>\n");
    fclose($handle);
    
    echo("\nConfiguration written to {$GLOBALS['outfile']}\n");
}

?>
