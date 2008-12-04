<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Router for MidCOM 3
 *
 * Router is the heart of the component architecture. It is responsible for mapping requests to components
 * and their specific controllers and calling those.
 *
 * @package midcom_core
 */
interface midcom_core_services_router
{
    public function __construct();
}
?>