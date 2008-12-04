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
    public $argv = array();
    public $get = array();
    protected $matched_handler = false;
    protected $parsed_arguments = array();

    public function __construct()
    {
        if (isset($_GET))
        {
            $this->get =& $_GET;
        }
    }

    public function 
}
?>