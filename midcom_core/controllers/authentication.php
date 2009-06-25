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
    public function __construct(midcom_core_component_interface $instance)
    {
        $this->configuration = $_MIDCOM->configuration;
    }
    
    public function get_logout(array $args)
    {
        $_MIDCOM->authentication->logout();
        header('location: /');
        exit();
    }
    
    public function get_login(array $args)
    {   
        $exception_data = array();
        $exception_data['message'] = $_MIDCOM->i18n->get('please enter your username and password', 'midcom_core');
        $_MIDCOM->context->set_item('midcom_core_exceptionhandler', $exception_data);
    }

    public function post_login(array $args)
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
        $this->get_login($args);
    }
}
?>