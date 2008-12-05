<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
error_reporting(E_STRICT);

if (!defined('MIDCOM_ROOT'))
{
    define('MIDCOM_ROOT', realpath(dirname(__FILE__) . '/../'));
}

/**
 * Make sure the URLs not having query string (or midcom-xxx- -method signature)
 * have trailing slash or some extension in the "filename".
 *
 * This makes life much, much better when making static copies for whatever reason
 */
if (   isset($_SERVER['REQUEST_URI'])
    && !preg_match('%\?|/$|midcom-.+-|/.+\..+$%', $_SERVER['REQUEST_URI']) 
    && empty($_POST))
{
    header('HTTP/1.0 301 Moved Permanently');
    header("Location: {$_SERVER['REQUEST_URI']}/");

    header('Content-type: text/html; charset=utf-8'); // just to be sure, that the browser interprets fallback right
    echo "301: new location <a href='{$_SERVER['REQUEST_URI']}/'>{$_SERVER['REQUEST_URI']}/</a>";
    exit();
}

// Load the exception handler
require(MIDCOM_ROOT . '/midcom_core/exceptionhandler.php');

// Start up MidCOM
require(MIDCOM_ROOT . '/midcom_core/midcom.php');
?>