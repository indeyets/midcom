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
    header('HTTP/1.0 403 Forbidden');
    echo '403: Forbidden<br><br>Prefetching not allowed.';
    exit;
}

///////////////////////////////////////////////////////////
// Ease debugging and make sure the code actually works(tm)
error_reporting(E_ALL);

///////////////////////////
// Load the main PEAR class
require_once 'PEAR.php';

//////////////////////////////////////////////////////////////////
// Load the PEAR Compatibility package if we are not using PHP 4.3
require_once 'PHP/Compat.php';
$components = PHP_Compat::loadVersion();

///////////////////////////////////
// Try to be smart about the pahts:
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
if (! defined('MIDCOM_CONFIG_FILE'))
{
    define('MIDCOM_CONFIG_FILE', '/etc/midgard/midcom.conf');
}

///////////////////////////////////////
//Constants, Globals and Configuration
require('version.php');
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

if (! is_null($components))
{
    debug_print_r ('PHP_Compat loaded these components:', $components);
}

///////////////////////////////////
// Load first-level supporting code
// Note that the cache check hit depends on the i18n code.
require('midcom/helper/misc.php');
require('midcom/services/i18n.php');

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
require('midcom/baseclasses/core/object.php');
require('midcom/baseclasses/core/dbobject.php');
require('midcom/baseclasses/components/cron_handler.php');
require('midcom/baseclasses/components/handler.php');
require('midcom/baseclasses/components/interface.php');
require('midcom/baseclasses/components/navigation.php');
require('midcom/baseclasses/components/purecode.php');
require('midcom/baseclasses/components/request.php');
require('midcom/baseclasses/components/request_admin.php');

// Note, that the legacy MidCOM base classes are loaded at the end of this file,
// you can't do this here as the referenced baseclases would still be undefined
// at this point.

// Core classes
require('midcom/core/user.php');
require('midcom/core/group.php');
require('midcom/core/group_midgard.php');
require('midcom/core/group_virtual.php');
require('midcom/core/manifest.php');
require('midcom/core/privilege.php');
require('midcom/core/querybuilder.php');

// Helpers and First-Generation services
require('midcom/helper/urlparser.php');
require('midcom/helper/_componentloader.php');
require('midcom/helper/_styleloader.php');
require('midcom/helper/_basicnav.php');
require('midcom/helper/_dbfactory.php');
require('midcom/helper/nav.php');
require('midcom/helper/itemlist.php');
require('midcom/helper/metadata.php');
require('midcom/helper/configuration.php');
require('midcom/helper/mailtemplate.php');
require('midcom/helper/toolbar.php');
require('midcom/helper/toolbars.php');
require('midcom/helper/list_helpers.php');

// Services
require('midcom/services/_i18n_l10n.php');
require('midcom/services/_sessioning.php');
require('midcom/services/session.php');
require('midcom/services/indexer.php'); // Further indexer files are included in indexer.php
require('midcom/services/auth.php');
require('midcom/services/auth/sessionmgr.php');
require('midcom/services/dbclassloader.php');
require('midcom/services/permalinks.php');
require('midcom/services/tmp.php');
require('midcom/services/toolbars.php');
require('midcom/services/uimessages.php');
require('midcom/services/metadata.php');

/////////////////////////////////////
// Instantinate the MidCOM main class
require('midcom/application.php');

$_MIDCOM = new midcom_application();
$GLOBALS['midcom'] =& $_MIDCOM;
$_MIDCOM->initialize();

?>
