<?php
/**
 * MidCOM wrapped access to org_openpsa_person plus some utility methods
 * @package org.openpsa.contacts
 */
class midcom_org_openpsa_person extends __midcom_org_openpsa_person
{
    var $name; //Compound of firstname, lastname and username
    var $rname; //Another compound of firstname, lastname and username

    function midcom_org_openpsa_person ($id = null)
    {
        return parent::__midcom_org_openpsa_person($id);
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
        static $srand_called = false;
        if ($plaintext)
        {
            $password = "**{$password}";
        }
        else
        {
            // Generate cryptographic seed
            if (!$srand_called)
            {
                // srand() should be called only once per request
                srand();
                $srand_called = true;
            }
            $salt = chr(rand(1,256)) . chr(rand(1,256));
            
            // Encrypt the password
            $password = crypt($password, $salt);
        }
        
        $this->username = $username;
        $this->password = $password;
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
        debug_push_class(__CLASS__, __FUNCTION__);
        $this_user = $_MIDCOM->auth->get_user($this->id);
        if (!is_object($this_user))
        {
            return false;
        }
        
        debug_add("Checking privilege midgard:update for person #{$this->id}");
        if (!$_MIDCOM->auth->can_do('midgard:update', $this, $this_user->id))
        {
            debug_add("Person #{$this->id} lacks privilege midgard:update, adding");
            $_MIDCOM->auth->request_sudo();
            $this->set_privilege('midgard:update', $this_user->id, MIDCOM_PRIVILEGE_ALLOW);
            $_MIDCOM->auth->drop_sudo();
        }
        //Could be usefull, I'm not certain if absolutely needed.
        debug_add("Checking privilege midgard:parameters for person #{$this->id}");
        if (!$_MIDCOM->auth->can_do('midgard:parameters', $this, $this_user->id))
        {
            debug_add("Person #{$this->id} lacks privilege midgard:parameters, adding");
            $_MIDCOM->auth->request_sudo();
            $this->set_privilege('midgard:parameters', $this_user->id, MIDCOM_PRIVILEGE_ALLOW);
            $_MIDCOM->auth->drop_sudo();
        }
        //Adding attachments requires both midgard:create and midgard:attchments
        debug_add("Checking privilege midgard:attachments for person #{$this->id}");
        if (!$_MIDCOM->auth->can_do('midgard:attachments', $this, $this_user->id))
        {
            debug_add("Person #{$this->id} lacks privilege midgard:attachments, adding");
            $_MIDCOM->auth->request_sudo();
            $this->set_privilege('midgard:attachments', $this_user->id, MIDCOM_PRIVILEGE_ALLOW);
            $_MIDCOM->auth->drop_sudo();
        }
        debug_add("Checking privilege midgard:create for person #{$this->id}");
        if (!$_MIDCOM->auth->can_do('midgard:create', $this, $this_user->id))
        {
            debug_add("Person #{$this->id} lacks privilege midgard:create, adding");
            $_MIDCOM->auth->request_sudo();
            $this->set_privilege('midgard:create', $this_user->id, MIDCOM_PRIVILEGE_ALLOW);
            $_MIDCOM->auth->drop_sudo();
        }

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
    
    function _on_created()
    {
        parent::_on_created();
        $this->_verify_privileges();        
        return true;
    }
    
}


/**
 * org.openpsa.contacts specific wrapper to org_openpsa_person
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_person extends midcom_org_openpsa_person
{
    function org_openpsa_contacts_person($identifier=NULL)
    {
        return parent::midcom_org_openpsa_person($identifier);
    }
}

?>