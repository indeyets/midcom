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
            $this->user = $this->user;
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
            
            $this->user = $users[0];
        }

        if ($this->user->id == $this->user->id)
        {
            $qb = new midgard_query_builder('org_gnome_tomboy_sync');
            $qb->add_constraint('person', '=', $this->user->id);
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
                $sync->person = $this->user->id;
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
    public function get_user($route_id, &$data, $args)
    {
        $this->authenticate();
        $this->request_user($data, $args);
        
        // Populate the user information array
        $data['first-name'] = $this->user->firstname;
        $data['last-name'] = $this->user->lastname;
        
        $data['notes-ref'] = array();
        $data['notes-ref']['api-ref'] = "{$args['user']}/notes/"; // "http://{$_MIDCOM->context->host->name}" . $_MIDCOM->dispatcher->generate_url('api_usernotes', array('user' => $args['user']), $_MIDCOM->context->page);
        // TODO: Web display URL when we have one

        //$data['friends-ref'] = array();
        //$data['friends-ref']['api-ref'] = "{$args['user']}/friends/"; // "http:////{$_MIDCOM->context->host->name}" . $_MIDCOM->dispatcher->generate_url('api_userfriends', array('user' => $args['user']), $_MIDCOM->context->page);
        
        if ($this->user->id == $this->user->id)
        {
            $data['latest-sync-revision'] = $data['latest-sync-revision'];
            $data['current-sync-guid'] = $data['latest-sync-guid'];
        }
    }

    /**
     * Get notes by a given user
     */
    public function get_usernotes($route_id, &$data, $args)
    {
        $this->authenticate();
        $this->request_user($data, $args);
        
        $qb = new midgard_query_builder('org_gnome_tomboy_note');
        $qb->add_constraint('metadata.creator', '=', $this->user->guid);
        // TODO: Access control
        
        if (isset($_MIDCOM->dispatcher->get['since']))
        {
            // Filter: return only notes revised since given sync number
            $qb->add_constraint('latestsync', '>', (int) $_MIDCOM->dispatcher->get['since']);
        }
        
        /*if ($_MIDCOM->dispatcher->request_method == 'PUT')
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
        }*/
        
        $notes = $qb->execute();
        $data['notes'] = array();
        foreach ($notes as $note)
        {
            $data['notes'][] = $this->note2data($note);
        }

        if ($this->user->id == $this->user->id)
        {
            $data['latest-sync-revision'] = $data['latest-sync-revision'];
        }
    }

    /**
     * Get friends of a given user
      TODO: Implement
    public function get_userfriends($route_id, &$data, $args)
    {
        $this->authenticate();
        $this->request_user($data, $args);

        $data['friends'] = array();
    }
    */
}
?>