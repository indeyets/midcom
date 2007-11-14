<?php
if (! exec ('which which'))
{
    die("The 'which' utility cannot be found. It is required for configuration-testing. Aborting.");
}
?>
<html>
<head><title>MidCOM Configuration Test</title></head>
<body>

<h1>MidCOM Configuration Test</h1>

<p>This page test the MidCOM configuration for validity.</p>

<table border="1" cellspacing="0" cellpadding="3">
  <tr>
    <th>Test</th>
    <th>Result</th>
    <th>Recommendations</th>
  </tr>
<?php

define('OK', 0);
define('WARNING', 1);
define('ERROR', 2);

function println($testname, $result_code, $recommendations = '&nbsp;')
{
    echo "  <tr>\n";
    echo "    <td>{$testname}</td>\n";
    switch ($result_code)
    {
        case OK:
            echo "    <td style='color: green;'>OK</td>\n";
            break;
            
        case WARNING:
            echo "    <td style='color: orange;'>WARNING</td>\n";
            break;
            
        case ERROR:
            echo "    <td style='color: red;'>ERROR</td>\n";
            break;
            
        default:
            die ("Unknown error code {$result_code}. Aborting.");
    }
    
    echo "    <td>{$recommendations}</td>\n";
    echo "  </tr>\n";
} 

function ini_get_filesize($setting)
{
	$result = ini_get($setting);
	$last_char = substr($result, -1);
	if ($last_char == 'M')
	{
	    $result = substr($result, 0, -1) * 1024 * 1024;
	}
	else if ($last_char == 'K')
	{
	    $result = substr($result, 0, -1) * 1024;
	}
	else if ($last_char == 'G')
	{
	    $result = substr($result, 0, -1) * 1024 * 1024 * 1024;
	}
    return $result;
}

function ini_get_boolean($setting)
{
	$result = ini_get($setting);
	if ($result == false || $result == "Off" || $result == "off" || $result == "" || $result == "0")
	{
		return false;
	}
	else
	{
		return true;
	}
}

function check_for_include_file($filename)
{
    return midcom_file_exists_incpath($filename);
}

function println_check_for_include_file($filename, $testname, $fail_code, $fail_recommendations)
{
	if (check_for_include_file($filename))
	{
	    println($testname, OK);
	}
	else
	{
	    println($testname, $fail_code, $fail_recommendations);
	}
}

function check_for_utility ($name, $testname, $fail_code, $fail_recommendations)
{
    $executable = $GLOBALS['midcom_config']["utility_{$name}"];
    $testname = "External Utility: {$testname}";
    if (is_null($executable))
    {
    	println($testname, $fail_code, "The path to the utility {$name} is not configured. {$fail_recommendations}");
    }
    else
    {
        exec ("which {$executable}", $output, $exitcode);
	    if ($exitcode == 0)
	    {
	        println($testname, OK);
	    }
	    else
	    {
	        println($testname, $fail_code, "The utility {$name} is not correctly configured: File ({$executable}) not found. {$fail_recommendations}");
	    }
    }
}

function check_rcs() 
{
    $config = $GLOBALS['midcom_config'];
    if (array_key_exists('midcom_services_rcs_enable', $config) && $config['midcom_services_rcs_enable']) 
    {
        if (!is_writable($config['midcom_services_rcs_root'])) 
        {
            println("MidCOM RCS", ERROR, "You must make the directory <b>{$config['midcom_services_rcs_root']}</b> writable by your webserver!");
        }
        else if (!is_executable($config['midcom_services_rcs_bin_dir'] . "/ci"))
        {
            println("MidCOM RCS", ERROR, "You must make <b>{$config['midcom_services_rcs_bin_dir']}/ci</b> executable by your webserver!");
        } 
        else 
        {
            println("MidCOM RCS", OK);
        }
    } 
    else 
    {
            println("MidCOM RCS", WARNING, "The MidCOM RCS service is disabled.");
    }
}

// Some helpers
$i18n =& $_MIDCOM->get_service('i18n');

$version = phpversion();
if (version_compare($version, '5.1.0', '<'))
{
    println('PHP Version', ERROR, 'PHP 5.1.0 or greater is required for MidCOM, 4.3.0 or greater is recommended.');
}
else
{
    println('PHP Version', OK);
}

// Available Memory for PHP

$cur_limit = ini_get_filesize('memory_limit');
if ($cur_limit >= (20 * 1024 * 1024))
{
    println('PHP Setting: memory_limit', OK);
}
else
{
    println('PHP Setting: memory_limit', ERROR, "MidCOM requires a minimum memory limit of 20 MB to operate correctly. Smaller amounts will lead to PHP Errors. Detected limit was {$cur_limit}.");   
}

// Register Globals
if (array_key_exists('midcom_site', $GLOBALS))
{
	if (ini_get_boolean('register_globals'))
	{
	    println('PHP Setting: register_globals', OK);
	}
	else
	{
	    println('PHP Setting: register_globals', ERROR, 'register_globals is required for MidCOM-Template usage, which depends on NemeinAuthentication.');
	}
}
else
{
	if (ini_get_boolean('register_globals'))
	{
	    println('PHP Setting: register_globals', WARNING, 'register_globals is enabled, it is recommended to turn this off for security reasons (unless you rely on Nemein Authentication somewhere).');
	}
	else
	{
	    println('PHP Setting: register_globals', OK);
	}
}

// Track Errors.
if (ini_get_boolean('track_errors'))
{
    println('PHP Setting: track_errors', OK);
}
else
{
    println('PHP Setting: track_errors', WARNING, 'track_errors is disabled, it is strongly suggested to be activated as this allows the framework to handle more errors gracefully.');
}

// Upload File Size
$upload_limit = ini_get_filesize('upload_max_filesize');
if ($upload_limit >= (50 * 1024 * 1024))
{
    println('PHP Setting: upload_max_filesize', OK);
}
else
{
    println('PHP Setting: upload_max_filesize', 
        WARNING, "To make bulk uploads (for exampe in the Image Gallery) useful, you should increase the Upload limit to something above 50 MB. (Current setting: {$upload_limit})"); 
}

$post_limit = ini_get_filesize('post_max_size');
if ($post_limit >= $upload_limit)
{
    println('PHP Setting: post_max_size', OK);
}
else
{
    println('PHP Setting: post_max_size', WARNING, 'post_max_size should be larger then upload_max_filesize, as both limits apply during uploads.');
}

// Magic Quotes
if (! ini_get_boolean('magic_quotes_gpc'))
{
    println('PHP Setting: magic_quotes_gpc', OK); 
}
else
{
	println('PHP Setting: magic_quotes_gpc', ERROR, 'Magic Quotes must be turned off, Midgard/MidCOM does this explicitly where required.');
}
if (! ini_get_boolean('magic_quotes_runtime'))
{
    println('PHP Setting: magic_quotes_runtime', OK); 
}
else
{
	println('PHP Setting: magic_quotes_runtime', ERROR, 'Magic Quotes must be turned off, Midgard/MidCOM does this explicitly where required.');
}


// iconv must be available.
if (! function_exists('iconv'))
{
    println('iconv', ERROR, 'The PHP iconv module is required for MidCOM operation.');
}
else
{
    println('iconv', OK);
}

// dba with db[234] is recommended.

if (! function_exists('dba_open'))
{
    println('Simple Database functions (dbm-style abstraction)', ERROR, 
        'The dba module with support for BerkleyDB must be available for proper operation of the caching engine.');
}
else
{
    if (! function_exists('dba_handlers'))
    {
        println('Simple Database functions (dbm-style abstraction)', WARNING,
            'The dba module is available, but the available modules cannot be listed. Ensure that BerkleyDB is available and configure MidCOM accordingly.');
    }
    else
    {
        $handlers = dba_handlers();
        if (   in_array('db2', $handlers)
            || in_array('db3', $handlers)
            || in_array('db4', $handlers))
        {
            println('Simple Database functions (dbm-style abstraction)', OK);
        }
        else
        {
            $tmp = implode($handlers, ", ");
            println('Simple Database functions (dbm-style abstraction)', WARNING,
                "The dba module is available, but the BerkleyDB handlers are not supported. They are recommeded for their stability. Available handlers: {$tmp}");
        }
    }
}

// Multibyte String Functions

if (! function_exists('mb_strlen'))
{
    println('Multi-Byte String functions', ERROR, 'The Multi-Byte String functions are unavailable, they are required for MidCOM operation.');
}
else
{
    if ($i18n->get_current_charset() == 'UTF-8')
    {
        $overload = ini_get('mbstring.func_overload');
        if ($overload != '7')
        {
        	println('Multi-Byte String functions', WARNING, 'The Multi-Byte String functions are available, but this is a UTF-8 site and Function overloading is disabled, this is not recommended since string operations are erronous then.');
        }
        else
        {
        	println('Multi-Byte String functions', OK);
        }
    }
    else
    {
        println('Multi-Byte String functions', OK);
    }
}

// EXIF Reading

$have_jhead = ! is_null($GLOBALS['midcom_config']['utility_jhead']);

if (! function_exists('read_exif_data'))
{
    if (! $have_jhead)
    {
        println('EXIF reader', WARNING, 'Neither PHP-EXIF nor JHead is available. They are required for proper operation of the Image Gallery components. PHP Exif is recommended for PHP 4.2+, JHEAD for older versions.');
    }
    else
    {
    	if (version_compare($version, '4.2.0', '<'))
        {
            println('EXIF reader', OK);
            check_for_utility('jhead', 'jhead', ERROR, 'JHead is required to read the EXIF information from images.');
        }
        else
        {
            println('EXIF reader', WARNING, 'PHP-EXIF is unavailable, but JHead was found. For PHP 4.2+ PHP Exif is the recommended way of processing EXIF information due to JHeads limited Featureset.');
            check_for_utility('jhead', 'jhead', ERROR, 'JHead is required to read the EXIF information from images.');
        }
    }
}
else
{
	if (version_compare($version, '4.2.0', '<'))
    {
        if (! $have_jhead)
        {
            println('EXIF reader', WARNING, 'PHP EXIF is available, but on a pre 4.2 installations. This may cause segfaults. You should either upgrade PHP or install JHead.');
        }
        else
        {
            println('EXIF reader', WARNING, 'PHP EXIF is available, but on a pre 4.2 installations. This may cause segfaults. JHead can be used instead, but with a reduced feature set.');
            check_for_utility('jhead', 'jhead', ERROR, 'JHead is required to read the EXIF information from images.');
        }
    }
    else
    {
        println('EXIF reader', OK);
    }
}

// Date PEAR Package for Datamanager2 date type.
println_check_for_include_file('Date.php', 'PEAR Package: Date', 
    WARNING, 'The Date package is required to use the Date type made available by the midcom.helper.datamanager2 library.');

// Mail and Mail_Mime PEAR packages for the Mailtemplate interface
println_check_for_include_file('Mail.php', 'PEAR Package: Mail', 
    WARNING, 'The Mail package is required to use the Mailtemplate system used by various components with auto-mailing support (like n.n.orders).');
println_check_for_include_file('Mail/mime.php', 'PEAR Package: Mail_Mime', 
    WARNING, 'The Mail_Mime package is required to use the Mailtemplate system used by various components with auto-mailing support (like n.n.orders).');

// HTML_Quickform for Datamanager validation support
println_check_for_include_file('HTML/QuickForm/RuleRegistry.php', 'PEAR Package: HTML_QuickForm',
    WARNING, 'The HTML_QuickForm pacakge is required for the Datamanager Form Validation code. If you use more then is_empty checks you should install it.');

// HTML_Treemenu
println_check_for_include_file('HTML/TreeMenu.php', 'PEAR Package: HTML_TreeMenu',
    WARNING, 'The HTML_TreeMenu package is required for the JS TreeMenu Navigation in AIS (disabled by default). You have to install it if you want to use the new navigation.');

check_rcs();
// Text_Diff
println_check_for_include_file('Text/Diff.php', 'PEAR Package: Text_Diff', WARNING, 'The Text_Diff package is used by no.bergfald.rcs to show text diffs.');

// XML_Serilizer
println_check_for_include_file('XML/Serializer.php', 'PEAR Package: XML_Serializer', WARNING, 'The xml_serilizer package is used to read and write rcs diffs by rcs.bergfald.no.');
// ImageMagick
$cmd = "{$GLOBALS['midcom_config']['utility_imagemagick_base']}identify -version";
exec ($cmd, $output, $result);
if ($result != 0)
{
    println('External Utility: ImageMagick', ERROR, 'The existance ImageMagick toolkit could not be verified, it is required for all kinds of image processing in MidCOM.'); 
}
else
{
    println('External Utility: ImageMagick', OK);
}

// Other utilities
check_for_utility('find', 'find', WARNING, 'The find utility is required for bulk upload processing in the image galleries, you should install it if you plan to deploy Image Galleries.');
check_for_utility('file', 'file', ERROR, 'The file utility is required for all kindes of Mime-Type identifications. You have to install it for proper MidCOM operations.');
check_for_utility('unzip', 'unzip', WARNING, 'The unzip utility is required for bulk upload processing in the image galleries, you should install it if you plan to deploy Image Galleries.');
check_for_utility('tar', 'tar', WARNING, 'The tar utility is required for bulk upload processing in the image galleries, you should install it if you plan to deploy Image Galleries.');
check_for_utility('gzip', 'gzip', WARNING, 'The gzip utility is required for bulk upload processing in the image galleries, you should install it if you plan to deploy Image Galleries.');
check_for_utility('jpegtran', 'jpegtran', WARNING, 'The jpegtran utility is used for lossless JPEG operations, even though ImageMagick can do the same conversions, the lossless features provided by this utility are used where appropriate, so its installation is strongly recommended.');

check_for_utility('diff','diff',WARNING, 'diff is needed by the versioning library‥ You can also use the pear library Text_Diff');

if ($GLOBALS['midcom_config']['indexer_backend'])
{
    check_for_utility('catdoc', 'catdoc', ERROR, 'Catdoc is required to properly index Microsoft Word documents. It is strongly recommended to install it, otherwise Word documents will be indexed as binary files.');
    check_for_utility('pdftotext', 'pdftotext', ERROR, 'pdftotext is required to properly index Adobe PDF documents. It is strongly recommended to install it, otherwise PDF documents will be indexed as binary files.');
    check_for_utility('unrtf', 'unrtf', ERROR, 'unrtf is required to properly index Rich Text Format documents. It is strongly recommended to install it, otherwise RTF documents will be indexed as binary files.');
}

// Validate the Cache Base Directory.
if  (! is_dir($GLOBALS['midcom_config']['cache_base_directory']))
{
    println('MidCOM cache base directory', ERROR, "The configured MidCOM cache base directory ({$GLOBALS['midcom_config']['cache_base_directory']}) does not exist or is not a directory. You have to create it as a directory writable by the Apache user."); 
}
else if (! is_writable($GLOBALS['midcom_config']['cache_base_directory']))
{
	println('MidCOM cache base directory', ERROR, "The configured MidCOM cache base directory ({$GLOBALS['midcom_config']['cache_base_directory']}) is not writable by the Apache user. You have to create it as a directory writable by the Apache user.");
}
else
{
	println('MidCOM cache base directory', OK);
}


?>
</table>
</body>
