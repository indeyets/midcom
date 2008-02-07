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
if (!preg_match('%\?|/$|midcom-.+-|/.+\..+$%', $_SERVER['REQUEST_URI'])
    && (!isset($_POST) || sizeof($_POST) < 1))
{
    header('HTTP/1.0 301 Moved Permanently');
    header("Location: {$_SERVER['REQUEST_URI']}/");
    echo "301: new location <a href='{$_SERVER['REQUEST_URI']}/'>{$_SERVER['REQUEST_URI']}/</a>";
    exit();
}
/** */

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
///////////////////////////////////
// Load first-level supporting code
// Note that the cache check hit depends on the i18n and auth code.
require('midcom/helper/misc.php');
require('midcom/helper/formatters.php');
require('midcom/services/i18n.php');

require('midcom/baseclasses/core/object.php');
require('midcom/core/user.php');
require('midcom/services/auth.php');
require('midcom/services/auth/sessionmgr.php');
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

// Base classes (keep an eye on the order)
// Note that the DB classes are spawned in midcom_application as they require the
// DB class loader to work.

require('midcom/baseclasses/core/dbobject.php');
require('midcom/baseclasses/components/cron_handler.php');
require('midcom/baseclasses/components/handler.php');
require('midcom/baseclasses/components/interface.php');
require('midcom/baseclasses/components/navigation.php');
require('midcom/baseclasses/components/purecode.php');
require('midcom/baseclasses/components/request.php');

// Note, that the legacy MidCOM base classes are loaded at the end of this file,
// you can't do this here as the referenced baseclases would still be undefined
// at this point.

// Core classes
require('midcom/core/group.php');
require('midcom/core/group_midgard.php');
require('midcom/core/group_virtual.php');
require('midcom/core/manifest.php');
require('midcom/core/privilege.php');
require('midcom/core/querybuilder.php');
require('midcom/core/collector.php');

// Helpers and First-Generation services
require('midcom/helper/_componentloader.php');
require('midcom/helper/_styleloader.php');
require('midcom/helper/_basicnav.php');
require('midcom/helper/_dbfactory.php');
require('midcom/helper/nav.php');
require('midcom/helper/metadata.php');
require('midcom/helper/configuration.php');
require('midcom/helper/serviceloader.php');
require('midcom/helper/toolbar.php');
require('midcom/helper/toolbars.php');

// Services
require('midcom/services/_i18n_l10n.php');
require('midcom/services/_sessioning.php');
require('midcom/services/session.php');
require('midcom/services/indexer.php'); // Further indexer files are included in indexer.php
require('midcom/services/dbclassloader.php');
require('midcom/services/permalinks.php');
require('midcom/services/tmp.php');
require('midcom/services/toolbars.php');
require('midcom/services/uimessages.php');
require('midcom/services/metadata.php');
require('midcom/services/rcs.php');

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
