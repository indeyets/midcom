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
require(MIDCOM_ROOT . '/midcom_core/exception_handler.php');

// Load the configuration loader and load core config
require(MIDCOM_ROOT . '/midcom_core/services/configuration.php');
require(MIDCOM_ROOT . '/midcom_core/services/configuration/yaml.php');
$config = new midcom_core_services_configuration_yaml('midcom_core');

$services_authorization_implementation = $config->get('services_authorization');
//$authorization = new $services_authorization_implementation();
?>