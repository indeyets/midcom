<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:application.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

////////////////////////////////////////////////////////
// First, block all Link prefetching requests as long as
// MidCOM isn't bulletproofed against this "feature".
// Ultimately, this is also a matter of performance...
if (   array_key_exists('HTTP_X_MOZ', $_SERVER)
    && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
{
    header('Cache-Control: no-cache');
    header('Pragma: no-cache');
    header('HTTP/1.0 403 Forbidden');
    echo '403: Forbidden<br><br>Prefetching not allowed.';
    exit;
}

/**
 * Second, make sure the URLs not having query string (or midcom-xxx- -method signature)
 * have trailing slash or some extension in the "filename".
 *
 * This makes life much, much better when making static copies for whatever reason
 */
if (   !preg_match('%\?|/$|midcom-.+-|/.+\..+$%', $_SERVER['REQUEST_URI'])
    && (   !isset($_POST)
        || empty($_POST))
    )
{
    header('HTTP/1.0 301 Moved Permanently');
    header("Location: {$_SERVER['REQUEST_URI']}/");
    echo "301: new location <a href='{$_SERVER['REQUEST_URI']}/'>{$_SERVER['REQUEST_URI']}/</a>";
    exit();
}
/** */

// Advertise the fact that this is a Midgard server
header('X-Powered-By: Midgard/' . mgd_version());

///////////////////////////////////////////////////////////
// Ease debugging and make sure the code actually works(tm)
error_reporting(E_ALL);

///////////////////////////////////
// Try to be smart about the paths:
// Define default constants
if (! defined('MIDCOM_ROOT'))
{
    define('MIDCOM_ROOT', dirname(__FILE__));
}
if (! defined('MIDCOM_STATIC_ROOT'))
{
    $pos = strrpos(MIDCOM_ROOT, '/');
    if ($pos === false)
    {
        // No slash, this is strange
        die ('MIDCOM_ROOT did not contain a slash, this should not happen and is most probably the cause of a configuration error.');
    }
    define('MIDCOM_STATIC_ROOT', substr(MIDCOM_ROOT,0,$pos) . '/static');
}
if (! defined('MIDCOM_STATIC_URL'))
{
    define('MIDCOM_STATIC_URL', '/midcom-static');
}
if (! defined('MIDCOM_CONFIG_FILE_BEFORE'))
{
    define('MIDCOM_CONFIG_FILE_BEFORE', '/etc/midgard/midcom.conf');
}
if (! defined('MIDCOM_CONFIG_FILE_AFTER'))
{
    define('MIDCOM_CONFIG_FILE_AFTER', '/etc/midgard/midcom-after.conf');
}

///////////////////////////////////////
//Constants, Globals and Configuration
require('constants.php');
require('globals.php');
require('midcom/config/midcom_config.php');
ini_set('track_errors', '1');

//////////////////////////////////////////////////////////////
// Set the MIDCOM_XDEBUG constant accordingly, if not yet set.

if (! defined('MIDCOM_XDEBUG'))
{
    if (function_exists('xdebug_start_profiling'))
    {
        define('MIDCOM_XDEBUG', 1);
    }
    else if (function_exists('xdebug_break'))
    {
        define('MIDCOM_XDEBUG', 2);
    }
    else
    {
        define('MIDCOM_XDEBUG', 0);
    }
}

/////////////////////
// Start the Debugger
require('midcom/debug.php');

debug_add("Start of MidCOM run: {$_SERVER['REQUEST_URI']}", MIDCOM_LOG_INFO);

/**
 * Automatically load missing class files
 *
 * @param string $class_name Name of a missing PHP class
 */
function midcom_autoload($class_name)
{
    static $autoloaded = 0;
    
    if (substr($class_name, 0, 15) == 'midcom_service_')
    {
        // Some service classes are named midcom_service_ and not midcom_services_
        $class_name = str_replace('midcom_service_', 'midcom_services_', $class_name);
    }
    
    $path = MIDCOM_ROOT . '/' . str_replace('_', '/', $class_name) . '.php';
    $path = str_replace('//', '/_', $path);
    
    if (   basename($path) == 'dba.php'
        || basename($path) == 'db.php')
    {
        // DBA object files are named objectname.php
        debug_add("Autoloader got '{$path}' which is DBA class, going one above");
        
        // Ensure we have the component loaded
        $_MIDCOM->dbclassloader->load_component_for_class($class_name);
        if (class_exists($class_name))
        {
            return;
        }
        
        $path = dirname($path) . ".php";
    }
    
    if (   basename($path) == 'interface.php'
        && $class_name != 'midcom_baseclasses_components_interface')
    {
        // MidCOM component interfaces are named midcom/interface.php
        debug_add("Autoloader got '{$path}' which is component interface class, getting it from midcom directory");
        $path = str_replace('/interface.php', '/midcom/interfaces.php', $path);

    }
    
    if (!file_exists($path))
    {
        $original_path = $path;
        $path = str_replace('.php', '/main.php', $path);
        
        if (!file_exists($path))
        {
            debug_add("Autoloader got '{$original_path}' and tried {$path} but neither was not found, aborting");
            return;
        }
    }
    
    require($path);
    $autoloaded++;
    debug_add("Autoloader got '{$path}', loading file {$autoloaded}");
}
// Register autoloader so we get all MidCOM classes loaded automatically
spl_autoload_register('midcom_autoload');

///////////////////////////////////
// Load first-level supporting code
// Note that the cache check hit depends on the i18n and auth code.
require('midcom/helper/misc.php');
require('midcom/helper/formatters.php');

$auth = new midcom_services_auth();
$auth->initialize();

//require('autoload.php');
//////////////////////////////////////
// Load and start up the cache system,
// this might already end the request
// on a content cache hit.
require('midcom/services/cache.php');
midcom_services_cache_startup();

///////////////////////////////////////////////
// Load all required MidCOM Framework Libraries

// Helpers and First-Generation services
// Services
require('midcom/services/_i18n_l10n.php');

//mgd_debug_start();
/////////////////////////////////////
// Instantiate the MidCOM main class
require('midcom/application.php');

$_MIDCOM = new midcom_application();
$_MIDCOM->auth = $auth;
//$GLOBALS['midcom'] =& $_MIDCOM;
$_MIDCOM->initialize();

if (file_exists(MIDCOM_CONFIG_FILE_AFTER))
{
    include(MIDCOM_CONFIG_FILE_AFTER);
}
?>