<?php
/**
 * @package org_gnome_tomboy
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Tomboy REST synchronization API using Basic authentication.
 *
 * @see http://live.gnome.org/Tomboy/Synchronization/REST
 * @package org_gnome_tomboy
 */
class org_gnome_tomboy_controllers_api
{
    private $user = null;

    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }
    
    /**
     * Handle user authentication. If session is present we use that, otherwise we do Basic authentication.
     */
    public function authenticate()
    {
        if ($_MIDCOM->authentication->is_user())
        {
            // User already has a session open
            $this->user = $_MIDCOM->authentication->get_person();
        }
        else
        {
            // We use Basic authentication
            $basic_auth = new midcom_core_services_authentication_basic();
            $e = new Exception("API usage requires Basic authentication");
            $basic_auth->handle_exception($e);
            $this->user = $basic_auth->get_person();
        }
    }

    /**
     * Get user information
     */
    public function action_user($route_id, &$data, $args)
    {
        $this->authenticate();
        
        if ($args['user'] == $this->user->username)
        {
            $data['user'] = $this->user;
        }
        else
        {
            // TODO: Access control. Can anybody read user info for anybody?
            $qb = new midgard_query_builder('midgard_person');
            $qb->add_constraint('username', '=', $args['user']);
            $users = $qb->execute();
            if (count($users) == 0)
            {
                throw new midcom_exception_notfound("User {$args['user']} not found");
            }
            
            $data['user'] = $users[0];
        }
        
        // Populate the user information array
        $data['userinfo'] = array();
        $data['userinfo']['first-name'] = $data['user']->firstname;
        $data['userinfo']['last-name'] = $data['user']->lastname;
        
        $data['userinfo']['notes-ref'] = array();
        $data['userinfo']['notes-ref']['api-ref'] = "http://{$_MIDCOM->context->host->name}" . $_MIDCOM->dispatcher->generate_url('api_usernotes', array('user' => $args['user']), $_MIDCOM->context->page);
        // TODO: Web display URL when we have one

        $data['userinfo']['friends-ref'] = array();
        $data['userinfo']['friends-ref']['api-ref'] = "http://{$_MIDCOM->context->host->name}" . $_MIDCOM->dispatcher->generate_url('api_userfriends', array('user' => $args['user']), $_MIDCOM->context->page);
        
        if ($data['user']->id == $this->user->id)
        {
            $qb = new midgard_query_builder('org_gnome_tomboy_sync');
            $qb->add_constraint('person', '=', $data['user']->id);
            $qb->add_order('metadata.created', 'DESC');
            $qb->set_limit(1);
            $syncs = $qb->execute();
            if (count($syncs) > 0)
            {
                $data['userinfo']['latest-sync-revision'] = $syncs[0]->id;
                $data['userinfo']['current-sync-guid'] = $syncs[0]->guid;
            }
        }

        // TODO: Do via variants instead
        header('Content-type: application/json');
        echo json_encode($data['userinfo']);
        die();
    }
}
?>