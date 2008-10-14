<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped access to org_openpsa_person plus some utility methods
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_person_dba extends __org_openpsa_contacts_person_dba
{
    var $name; //Compound of firstname, lastname and username
    var $rname; //Another compound of firstname, lastname and username

    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    /**
     * Retrieve a reference to a person object, uses in-request caching
     *
     * @param string $src GUID of device (ids work but are discouraged)
     * @return org_maemo_devcodes_device_dba reference to device object or false
     */
    function &get_cached($src)
    {
        $cache_name = '__org_openpsa_contacts_person_get_cached_objects';
        if (!isset($GLOBALS[$cache_name]))
        {
            $GLOBALS[$cache_name] = array();
        }
        $cache =& $GLOBALS[$cache_name];
        if (isset($cache[$src]))
        {
            return $cache[$src];
        }
        $object = new org_openpsa_contacts_person_dba($src);
        if (   !$object
            && empty($object->guid))
        {
            $x = false;
            return $x;
        }
        $cache[$object->guid] = $object;
        $cache[$object->id] =& $cache[$object->guid];
        return $cache[$object->guid];
    }

    function _on_loaded()
    {
        //Fill name and rname
        if (   !empty($this->firstname)
            && !empty($this->lastname))
        {
            $this->name = $this->firstname . ' ' . $this->lastname;
            $this->rname = $this->lastname . ', ' . $this->firstname;
        }
        else if (!empty($this->firstname))
        {
            $this->name = $this->firstname;
            $this->rname = $this->firstname;
        }
        else if (!empty($this->username))
        {
            $this->name = $this->username;
            $this->rname = $this->username;
        }
        else
        {
            $this->name = 'person #' . $this->id;
            $this->rname = 'person #' . $this->id;
        }

        $this->_verify_privileges();

        return true;
    }

    /**
     * Sets username and password for person
     */
    function set_account($username, $password, $plaintext = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        static $rand = false;
        if (empty($rand))
        {
            if (function_exists('mt_rand'))
            {
                $rand = 'mt_rand';
            }
            else
            {
                $rand = 'rand';
            }
        }
        if ($plaintext)
        {
            $password = "**{$password}";
        }
        else
        {
            /*
             It seems having nonprintable characters in the password breaks replication
             Here we recreate salt and hash until we have a combination where only
             printable characters exist
            */
            $crypted = false;
            while (   empty($crypted)
                   || preg_match('/[\x00-\x20\x7f-\xff]/', $crypted))
            {
                $salt = chr($rand(33,125)) . chr($rand(33,125));
                $crypted = crypt($password, $salt);
            }
            $password = $crypted;
            unset($crypted);
        }

        $this->username = $username;
        $this->password = $password;
        debug_pop();
        return $this->update();
    }

    /**
     * Removes persons account
     */
    function unset_account()
    {
        $this->username = '';
        $this->password = '';
        return $this->update();
    }
    /**
     * Make sure user has correct privileges to allow to edit themselves
     */
    function _verify_privileges()
    {
        return false;
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$this->id)
        {
            debug_pop();
            return false;
        }
        $this_user = $_MIDCOM->auth->get_user($this->id);
        if (!is_object($this_user))
        {
            debug_pop();
            return false;
        }

        if (!isset($GLOBALS['org_openpsa_contacts_person__verify_privileges']))
        {
            $GLOBALS['org_openpsa_contacts_person__verify_privileges'] = array();
        }
        if (   isset($GLOBALS['org_openpsa_contacts_person__verify_privileges'][$this->id])
            && !empty($GLOBALS['org_openpsa_contacts_person__verify_privileges'][$this->id]))
        {
            debug_add("loop detected for person #{$this->id}, aborting this check silently");
            debug_pop();
            return true;
        }
        $GLOBALS['org_openpsa_contacts_person__verify_privileges'][$this->id] = true;

        // PONDER: Can't we just use midgard:owner ???
        debug_add("Checking privilege midgard:update for person #{$this->id}");
        if (!$_MIDCOM->auth->can_do('midgard:update', $this, $this_user))
        {
            debug_add("Person #{$this->id} lacks privilege midgard:update, adding");
            $_MIDCOM->auth->request_sudo();
            if (!$this->set_privilege('midgard:update', $this_user, MIDCOM_PRIVILEGE_ALLOW))
            {
                debug_add("\$this->set_privilege('midgard:update', {$this_user->guid}, MIDCOM_PRIVILEGE_ALLOW) failed, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
            }
            else
            {
                debug_add("Added privilege 'midgard:update' for person #{$this->id}", MIDCOM_LOG_INFO);
            }
            $_MIDCOM->auth->drop_sudo();
        }
        //Could be useful, I'm not certain if absolutely needed.
        debug_add("Checking privilege midgard:parameters for person #{$this->id}");
        if (!$_MIDCOM->auth->can_do('midgard:parameters', $this, $this_user))
        {
            debug_add("Person #{$this->id} lacks privilege midgard:parameters, adding");
            $_MIDCOM->auth->request_sudo();
            if (!$this->set_privilege('midgard:parameters', $this_user, MIDCOM_PRIVILEGE_ALLOW))
            {
                debug_add("\$this->set_privilege('midgard:parameters', {$this_user->guid}, MIDCOM_PRIVILEGE_ALLOW) failed, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
            }
            else
            {
                debug_add("Added privilege 'midgard:parameters' for person #{$this->id}", MIDCOM_LOG_INFO);
            }
            $_MIDCOM->auth->drop_sudo();
        }
        //Adding attachments requires both midgard:create and midgard:attachments
        debug_add("Checking privilege midgard:create for person #{$this->id}");
        if (!$_MIDCOM->auth->can_do('midgard:create', $this, $this_user))
        {
            debug_add("Person #{$this->id} lacks privilege midgard:create, adding");
            $_MIDCOM->auth->request_sudo();
            if (!$this->set_privilege('midgard:create', $this_user, MIDCOM_PRIVILEGE_ALLOW))
            {
                debug_add("\$this->set_privilege('midgard:create', {$this_user->guid}, MIDCOM_PRIVILEGE_ALLOW) failed, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
            }
            else
            {
                debug_add("Added privilege 'midgard:create' for person #{$this->id}", MIDCOM_LOG_INFO);
            }
            $_MIDCOM->auth->drop_sudo();
        }
        debug_add("Checking privilege midgard:attachments for person #{$this->id}");
        if (!$_MIDCOM->auth->can_do('midgard:attachments', $this, $this_user))
        {
            debug_add("Person #{$this->id} lacks privilege midgard:attachments, adding");
            $_MIDCOM->auth->request_sudo();
            if (!$this->set_privilege('midgard:attachments', $this_user, MIDCOM_PRIVILEGE_ALLOW))
            {
                debug_add("\$this->set_privilege('midgard:attachments', {$this_user->guid}, MIDCOM_PRIVILEGE_ALLOW) failed, errstr: " . mgd_errstr(), MIDCOM_LOG_WARN);
            }
            else
            {
                debug_add("Added privilege 'midgard:attachments' for person #{$this->id}", MIDCOM_LOG_INFO);
            }
            $_MIDCOM->auth->drop_sudo();
        }

        $GLOBALS['org_openpsa_contacts_person__verify_privileges'][$this->id] = false;

        debug_pop();
        return true;
    }

    function _on_creating()
    {
        parent::_on_creating();

        //Make sure we have objType
        if (!$this->orgOpenpsaObtype)
        {
            $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_PERSON;
        }
        return true;
    }

    function _on_updating()
    {
        if ($this->homepage)
        {
            // This group has a homepage, register a prober
            $args = array
            (
                'person' => $this->guid,
            );
            $_MIDCOM->load_library('midcom.services.at');
            $atstat = midcom_services_at_interface::register(time() + 60, 'org.openpsa.contacts', 'check_url', $args);
        }

        return parent::_on_updating();
    }

    function _on_updated()
    {
        parent::_on_updated();
        $this->_verify_privileges();
        return true;
    }

    function _on_created()
    {
        parent::_on_created();
        $this->_verify_privileges();
        debug_pop();
        return true;
    }

    function _on_deleting()
    {
        // FIXME: Call duplicate checker's dependency handling methods
        return parent::_on_deleting();
    }

    function get_label()
    {
        if ($this->rname)
        {
            $label = $this->rname;
        }
        else
        {
            $label = $this->username;
        }

        return $label;
    }

    function get_label_property()
    {
        if ($this->rname)
        {
            $property = 'rname';
        }
        else
        {
            $property = 'username';
        }

        return $property;
    }

}

?>