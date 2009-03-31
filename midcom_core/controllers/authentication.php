<?php
/**
 * @package midcom_core
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comet listeners controller
 *
 * @package midcom_core
 */
class midcom_core_controllers_authentication
{
    public function __construct($instance)
    {
        $this->configuration = $_MIDCOM->configuration;
    }
    
    public function action_logout($route_id, &$data, $args)
    {
        $_MIDCOM->authentication->logout();
        header('location: /');
        exit();
    }
    
    public function action_login($route_id, &$data, $args)
    {
        // TODO: Fix some more intelligent way to determine login method
        if (   isset($_POST['username']) 
            && isset($_POST['password']))
        {
            if ($_MIDCOM->authentication->login($_POST['username'], $_POST['password']))
            {
                header('location: /'); // TODO: $_MIDCOM->relocate required later
                exit();
            }
        }
    }
}
?>