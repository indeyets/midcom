<?php
/**
 * @package org.openpsa.contacts
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: list.php,v 1.2 2006/06/08 14:12:38 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Buddy list handler
 *
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_handler_buddy_list extends midcom_baseclasses_components_handler
{
    function org_openpsa_contacts_handler_buddy_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _handler_add($handler_id, $args, &$data)
    {
        $user =& $_MIDCOM->auth->user->get_storage();
        $user->require_do('midgard:create');

        $target = new org_openpsa_contacts_person($args[0]);
        if (!$target)
        {
            return false;
        }

        // Check we're not buddies already
        $qb = org_openpsa_contacts_buddy::new_query_builder();
        $qb->add_constraint('account', '=', $user->guid);
        $qb->add_constraint('buddy', '=', $target->guid);
        $qb->add_constraint('isapproved', '=', true);
        $buddies = $qb->execute();
        if (count($buddies) > 0)
        {
            return false;
        }

        $buddy = new org_openpsa_contacts_buddy();
        $buddy->account = $user->guid;
        $buddy->buddy = $target->guid;
        $buddy->isapproved = true;
        if (!$buddy->create())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to add buddy, reason ".mgd_errstr());
            // This will exit
        }

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $_MIDCOM->relocate("{$prefix}person/{$target->guid}/");
    }

    function _handler_remove($handler_id, $args, &$data)
    {
        $user =& $_MIDCOM->auth->user->get_storage();
        $user->require_do('midgard:create');

        $target = new org_openpsa_contacts_person($args[0]);
        if (!$target)
        {
            return false;
        }

        // Check we're not buddies already
        $qb = org_openpsa_contacts_buddy::new_query_builder();
        $qb->add_constraint('account', '=', $user->guid);
        $qb->add_constraint('buddy', '=', $target->guid);
        $buddies = $qb->execute();
        if (count($buddies) == 0)
        {
            return false;
        }

        foreach ($buddies as $buddy)
        {
            if (!$buddy->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to add buddy, reason ".mgd_errstr());
                // This will exit
            }
        }

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $_MIDCOM->relocate("{$prefix}person/{$target->guid}/");
    }

    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $user = $_MIDCOM->auth->user->get_storage();

        $this->_request_data['buddylist'] = array();

        $qb = org_openpsa_contacts_buddy::new_query_builder();
        $qb->add_constraint('account', '=', $user->guid);
        //$qb->add_constraint('isapproved', '=', true);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies = $qb->execute();

        foreach ($buddies as $buddy)
        {
            $person = new org_openpsa_contacts_person($buddy->buddy);
            if ($person)
            {
                $this->_request_data['buddylist'][] = $person;
            }
        }
        return true;
    }

    function _show_list($handler_id, &$data)
    {
        if (count($this->_request_data['buddylist']) > 0)
        {
            midcom_show_style("show-buddylist-header");
            foreach ($this->_request_data['buddylist'] as $person)
            {
                $this->_request_data['person'] =& $person;
                midcom_show_style("show-buddylist-item");
            }
            midcom_show_style("show-buddylist-footer");
        }
    }
}
?>