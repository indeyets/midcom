<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Midgard dispatcher for MidCOM 3
 *
 * Dispatches Midgard HTTP requests to components.
 *
 * @package midcom_core
 */
class midcom_core_services_dispatcher_midgard implements midcom_core_services_dispatcher
{
    public $authorization;
    public $configuration;

    public function __construct()
    {
        // Load the configuration loader and load core config
        require(MIDCOM_ROOT . '/midcom_core/services/configuration.php');
        require(MIDCOM_ROOT . '/midcom_core/services/configuration/yaml.php');
        $this->configuration = new midcom_core_services_configuration_yaml('midcom_core');
        
        $services_authorization_implementation = $this->configuration->get('services_authorization');
        $this->authorization = new $services_authorization_implementation();
    }
}
?>