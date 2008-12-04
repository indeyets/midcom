<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
if (!defined('MIDCOM_ROOT'))
{
    define('MIDCOM_ROOT', dirname(dirname(__FILE__) . '../'));
}

// Load the exception handler
require(MIDCOM_ROOT . '/midcom_core/exceptionhandler.php');

// Start up MidCOM
require(MIDCOM_ROOT . '/midcom_core/midcom.php');
$_MIDCOM = new midcom_core_midcom();
?>