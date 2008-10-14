<?php
/**
 * @package org.openpsa.contacts
 * @author Nemein Oy http://www.nemein.com/
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */
/**
 * MidCOM wrapped class for access to stored queries
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_buddy_dba extends __org_openpsa_contacts_buddy_dba
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function get_parent_guid_uncached()
    {
        if ($this->account)
        {
            $person = new org_openpsa_contacts_person_dba($this->account);
            if ($person)
            {
                return $person;
            }
        }
        else
        {
            // Not saved buddy, return user himself
            return $_MIDCOM->auth->user->get_storage();
        }
        return null;
    }

    /**
     * Creation handler, grants owner permissions to the buddy user for this
     * buddy object, so that he can later approve / reject the request. For
     * safety reasons, the owner privilege towards the account user is also
     * created, so that there is no discrepancy later in case administrators
     * create the object.
     */
    function _on_created()
    {
        $this->set_privilege('midgard:owner', "user:{$this->buddy}");
        $this->set_privilege('midgard:owner', "user:{$this->account}");
    }

    /**
     * The pre-creation hook sets the added field to the current timestamp if and only if
     * it is unset.
     */
    function _on_creating()
    {
        if (! $this->added)
        {
            $this->added = time();
        }
        return true;
    }
}
?>