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
    private $sync = null;

    public function __construct($instance)
    {
        $this->configuration = $instance->configuration;
    }

    /**
     * Temporary UUID generator from http://dk.php.net/uniqid
     */
    private function get_uuid() {
        
        $pr_bits = false;
        if (is_a ( $this, 'uuid' )) {
            if (is_resource ( $this->urand )) {
                $pr_bits .= @fread ( $this->urand, 16 );
            }
        }
        if (! $pr_bits) {
            $fp = @fopen ( '/dev/urandom', 'rb' );
            if ($fp !== false) {
                $pr_bits .= @fread ( $fp, 16 );
                @fclose ( $fp );
            } else {
                // If /dev/urandom isn't available (eg: in non-unix systems), use mt_rand().
                $pr_bits = "";
                for($cnt = 0; $cnt < 16; $cnt ++) {
                    $pr_bits .= chr ( mt_rand ( 0, 255 ) );
                }
            }
        }
        $time_low = bin2hex ( substr ( $pr_bits, 0, 4 ) );
        $time_mid = bin2hex ( substr ( $pr_bits, 4, 2 ) );
        $time_hi_and_version = bin2hex ( substr ( $pr_bits, 6, 2 ) );
        $clock_seq_hi_and_reserved = bin2hex ( substr ( $pr_bits, 8, 2 ) );
        $node = bin2hex ( substr ( $pr_bits, 10, 6 ) );
        
        /**
         * Set the four most significant bits (bits 12 through 15) of the
         * time_hi_and_version field to the 4-bit version number from
         * Section 4.1.3.
         * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
         */
        $time_hi_and_version = hexdec ( $time_hi_and_version );
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;
        
        /**
         * Set the two most significant bits (bits 6 and 7) of the
         * clock_seq_hi_and_reserved to zero and one, respectively.
         */
        $clock_seq_hi_and_reserved = hexdec ( $clock_seq_hi_and_reserved );
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;
        
        return sprintf ( '%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node );
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
                $this->sync = $syncs[0];
                if ($this->sync->metadata->revision == 0)
                {
                    $this->sync->update();
                }
            }
            else
            {
                $this->sync = new org_gnome_tomboy_sync();
                $this->sync->tomboyuuid = $this->get_uuid();
                $this->sync->person = $this->user->id;
                $this->sync->create();
                $this->sync->update();
            }
            $data['latest-sync-revision'] = $this->sync->metadata->revision;
            //$data['latest-sync-guid'] = $this->sync->tomboyuuid;
        }

    }

    private function note2data(org_gnome_tomboy_note $note, $full_note = false)
    {
        $noteinfo = array();
        $noteinfo['guid'] = $note->tomboyuuid;
        $noteinfo['title'] = $note->title;
        
        if ($full_note)
        {
            $noteinfo['note-content'] = $note->text;
            $noteinfo['last-change-date'] = $note->metadata->revised;
            $noteinfo['last-sync-revision'] = $note->latestsync;
            $noteinfo['create-date'] = $note->metadata->created;
            $noteinfo['open-on-startup'] = $note->openonstartup;
        }
        
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
            $data['latest-sync-revision'] = $this->sync->metadata->revision;
            $data['current-sync-guid'] = $this->sync->tomboyuuid;  
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
        
        $full_notes = false;
        if (   isset($_MIDCOM->dispatcher->get['include_notes'])
            && $_MIDCOM->dispatcher->get['include_notes'] == 'true')
        {
            $full_notes = true;        
        }

        $notes = $qb->execute();
        $data['notes'] = array();
        foreach ($notes as $note)
        {
            $data['notes'][] = $this->note2data($note, $full_notes);
        }

        if ($this->user->id == $this->user->id)
        {
            $data['latest-sync-revision'] = $data['latest-sync-revision'];
        }
    }

    /**
     * Update or add notes for the current user
     */
    public function put_usernotes($route_id, &$data, $args)
    {
        $this->authenticate();
        $this->request_user($data, $args);

        $json = '';
        $putdata = fopen('php://input', 'r');
        while ($data = fread($putdata, 1024))
        {
            $json .= str_replace('note-changes', 'notechanges', str_replace('note-content', 'notecontent', $data));
        }
        fclose($putdata);
        
        $json_data = json_decode($json);
        if (   !isset($json_data->notechanges)
            || !is_array($json_data->notechanges))
        {
            throw new midcom_exception_httperror("Note changes not given", 400);
        }
        
        //ob_start();
        //var_dump($json_data);
        //$_MIDCOM->log('tmp', ob_get_clean(), 'warn');
        
        // Start the sync transaction
        $this->sync->update();

        foreach ($json_data->notechanges as $note_import)
        {
            $note = null;
            $_MIDCOM->log('tmp', "Importing {$note_import->title}", 'warn');
            if (isset($note_import->guid))
            {
                // Try to instantiate with GUID
                $qb = new midgard_query_builder('org_gnome_tomboy_note');
                $qb->add_constraint('tomboyuuid', '=', $note_import->guid);                
                $qb->add_constraint('metadata.creator', '=', $this->user->guid);
                $notes = $qb->execute();
                if (count($notes) > 0)
                {
                    $note = $notes[0];
                }
            }
            else
            {
                $qb = new midgard_query_builder('org_gnome_tomboy_note');
                $qb->add_constraint('title', '=', $note_import->title);
                $qb->add_constraint('metadata.creator', '=', $this->user->guid);
                $notes = $qb->execute();
                if (count($notes) > 0)
                {
                    $note = $notes[0];
                }
            }
            
            if (!$note)
            {
                // Not found, create
                $note = new org_gnome_tomboy_note();
                $note->tomboyuuid = $note_import->guid;
                $note->person = $this->user->id;
                $note->create();
            }
            
            $note->title = $note_import->title;
            $note->text = $note_import->notecontent;
            $note->latestsync = $this->sync->metadata->revision;
            $note->update();
        }

        $qb = new midgard_query_builder('org_gnome_tomboy_note');
        $qb->add_constraint('metadata.creator', '=', $this->user->guid);
        $notes = $qb->execute();
        $data['notes'] = array();
        foreach ($notes as $note)
        {
            $data['notes'][] = $this->note2data($note);
        }
        $data['latest-sync-revision'] = $this->sync->metadata->revision;

        $_MIDCOM->context->webdav_request = false;
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