<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Authentication interface for MidCOM 3
 *
 * @package midcom_core
 */
interface midcom_core_services_authentication
{
    public function get_person();
    
    public function login($username, $password);
    
    public function logout();
    
    public function handle_exception(Exception $exception);
}
?>