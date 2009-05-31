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
    
    private function request_user(&$data, $args)
    {
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

        if ($data['user']->id == $this->user->id)
        {
            $qb = new midgard_query_builder('org_gnome_tomboy_sync');
            $qb->add_constraint('person', '=', $data['user']->id);
            $qb->add_order('metadata.created', 'DESC');
            $qb->set_limit(1);
            $syncs = $qb->execute();
            if (count($syncs) > 0)
            {
                $sync = $syncs[0];
                if ($sync->metadata->revision == 0)
                {
                    $sync->update();
                }
            }
            else
            {
                $sync = new org_gnome_tomboy_sync();
                $sync->person = $data['user']->id;
                $sync->create();
                $sync->update();
            }
            $data['latest-sync-revision'] = $sync->metadata->revision;
            $data['latest-sync-guid'] = $sync->guid;
        }

    }

    private function note2data(org_gnome_tomboy_note $note, $full_note = false)
    {
        $noteinfo = array();
        $noteinfo['guid'] = $note->guid;
        $noteinfo['title'] = $note->title;
        
        return $noteinfo;
    }

    /**
     * Get user information
     */
    public function action_user($route_id, &$data, $args)
    {
        $this->authenticate();
        $this->request_user($data, $args);
        
        // Populate the user information array
        $data['userinfo'] = array();
        $data['userinfo']['first-name'] = $data['user']->firstname;
        $data['userinfo']['last-name'] = $data['user']->lastname;
        
        $data['userinfo']['notes-ref'] = array();
        $data['userinfo']['notes-ref']['api-ref'] = "{$args['user']}/notes/"; // "http://{$_MIDCOM->context->host->name}" . $_MIDCOM->dispatcher->generate_url('api_usernotes', array('user' => $args['user']), $_MIDCOM->context->page);
        // TODO: Web display URL when we have one

        //$data['userinfo']['friends-ref'] = array();
        //$data['userinfo']['friends-ref']['api-ref'] = "{$args['user']}/friends/"; // "http:////{$_MIDCOM->context->host->name}" . $_MIDCOM->dispatcher->generate_url('api_userfriends', array('user' => $args['user']), $_MIDCOM->context->page);
        
        if ($data['user']->id == $this->user->id)
        {
            $data['userinfo']['latest-sync-revision'] = $data['latest-sync-revision'];
            $data['userinfo']['current-sync-guid'] = $data['latest-sync-guid'];
        }

        // TODO: Do via variants instead
        header('Content-type: application/json');
        echo json_encode($data['userinfo']);
        die();
    }

    /**
     * Get notes by a given user
     */
    public function action_usernotes($route_id, &$data, $args)
    {
        $this->authenticate();
        $this->request_user($data, $args);
        
        $data['notesinfo'] = array();
        
        $qb = new midgard_query_builder('org_gnome_tomboy_note');
        $qb->add_constraint('metadata.creator', '=', $data['user']->guid);
        // TODO: Access control
        
        if (isset($_MIDCOM->dispatcher->get['since']))
        {
            // Filter: return only notes revised since given sync number
            $qb->add_constraint('latestsync', '>', (int) $_MIDCOM->dispatcher->get['since']);
        }
        
        if ($_MIDCOM->dispatcher->request_method == 'PUT')
        {
            $json = '';
            $putdata = fopen('php://input', 'r');
            while ($data = fread($putdata, 1024))
            {
                $json .= $data;
            }
            fclose($putdata);
            
            $_MIDCOM->log('PUT data', $json, 'error');
            die();
        }
        
        $notes = $qb->execute();
        $data['notesinfo']['notes'] = array();
        foreach ($notes as $note)
        {
            $data['notesinfo']['notes'][] = $this->note2data($note);
        }

        if ($data['user']->id == $this->user->id)
        {
            $data['notesinfo']['latest-sync-revision'] = $data['latest-sync-revision'];
        }

        // TODO: Do via variants instead
        header('Content-type: application/json');
        echo json_encode($data['notesinfo']);
        die();
    }

    /**
     * Get friends of a given user
      TODO: Implement
    public function action_userfriends($route_id, &$data, $args)
    {
        $this->authenticate();
        $this->request_user($data, $args);
        
        $data['friendsinfo'] = array();
        $data['friendsinfo']['friends'] = array();
        //

        // TODO: Do via variants instead
        header('Content-type: application/json');
        echo json_encode($data['friendsinfo']);
        die();
    }
    */
}
?>