<?php
/**
 * @package org.openpsa.sales
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: salesproject_member.php,v 1.4 2006/05/11 15:43:12 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped base class, keep logic here
 *
 * @package org.openpsa.sales
 */
class org_openpsa_sales_salesproject_member extends __org_openpsa_sales_salesproject_member
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }
    
    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        if ($this->person)
        {
            $person = new midcom_db_person($this->person);
            return $person->name;
        }
        return "member #{$this->id}";
    }


    function _on_creating()
    {
        if (!$this->orgOpenpsaObtype)
        {
            $this->orgOpenpsaObtype = ORG_OPENPSA_OBTYPE_SALESPROJECT_MEMBER;
        }
        return true;
    }

    function _on_created()
    {
        // Check if the salesman and the contact are buddies already
        $salesproject = new org_openpsa_sales_salesproject($this->salesproject);
        $owner = new midcom_db_person($salesproject->owner);
        $person = new midcom_db_person($this->person);

        $qb = org_openpsa_contacts_buddy::new_query_builder();
        $user =& $_MIDCOM->auth->user->get_storage();
        $qb->add_constraint('account', '=', (string)$owner->guid);
        $qb->add_constraint('buddy', '=', (string)$person->guid);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies = $qb->execute();

        if (count($buddies) == 0)
        {
            // Cache the association to buddy list of the sales project owner
            $buddy = new org_openpsa_contacts_buddy();
            $buddy->account = $owner->guid;
            $buddy->buddy = $person->guid;
            $buddy->isapproved = false;
            $buddy->create();
        }
        return true;
    }

    function get_parent_guid_uncached()
    {
        if ($this->salesproject != 0)
        {
            $parent = new org_openpsa_sales_salesproject($this->salesproject);
            return $parent;
        }
        else
        {
            return null;
        }
    }

}

?>