<?php
/**
 * @package org.openpsa.core
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: acl_synchronizer.php,v 1.13 2006/05/03 14:09:44 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.core ACL synchronizer.
 *
 * @package org.openpsa.core
 */
class org_openpsa_core_acl_synchronizer
{

    function org_openpsa_core_acl_synchronizer()
    {

    }

    function write_acls($object, $owner_id, $accesstype)
    {
        if (   empty($owner_id)
            || empty($accesstype))
        {
            return false;
        }

        // TODO: Figure out what kind of write handler we need based on the situation (calendar/document etc)
        return $this->_write_full_midcom_acls($object, $owner_id, $accesstype);
    }

    function _write_full_midcom_acls($object, $owner_id, $accesstype)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Exit if no owner workgroup has been assigned
        if ($owner_id == '')
        {
            debug_add('Given owner ID was empty, aborting');
            debug_pop();
            return false;
        }

        $owner_object =& $_MIDCOM->auth->get_assignee($owner_id);
        if (!$owner_object)
        {
            debug_add('Given owner was invalid, aborting');
            debug_pop();
            return false;
        }

        // Clear old ACLs applying to others than current user or selected owner group
        $privileges = $object->get_privileges();
        if ($privileges)
        {
            foreach ($privileges as $privilege)
            {
                if (   $privilege->assignee != $_MIDCOM->auth->user->id
                    && $privilege->assignee != $owner_id)
                {
                    if (is_array($privilege->assignee))
                    {
                        $assignee_key = $privilege->assignee['identifier'];
                    }
                    else
                    {
                        $assignee_key = $privilege->assignee;
                    }
                    debug_add("Removing privilege {$privilege->name} from {$assignee_key}");
                    $object->unset_privilege($privilege->name, $privilege->assignee);
                }
            }
        }

        // Handle ACL storage
        switch ($accesstype)
        {
            case ORG_OPENPSA_ACCESSTYPE_PUBLIC:
            case ORG_OPENPSA_ACCESSTYPE_AGGREGATED:
                debug_add("Public object, everybody can read");
                $object->set_privilege('midgard:read', 'EVERYONE', MIDCOM_PRIVILEGE_ALLOW);
                $this->_set_attachment_permission($object, 'midgard:read', 'EVERYONE', MIDCOM_PRIVILEGE_ALLOW);
                $object->set_privilege('midgard:owner', $owner_id, MIDCOM_PRIVILEGE_ALLOW);
                break;
            case ORG_OPENPSA_ACCESSTYPE_PRIVATE:
                debug_add("Private object, only user can read and write");
                $object->set_privilege('midgard:read', 'EVERYONE', MIDCOM_PRIVILEGE_DENY);
                $object->set_privilege('midgard:owner', $_MIDCOM->auth->user->id, MIDCOM_PRIVILEGE_ALLOW);
                //TODO: Shouldn't this be midgard:owner as well ??
                $this->_set_attachment_permission($object, 'midgard:read', $_MIDCOM->auth->user->id, MIDCOM_PRIVILEGE_ALLOW);
                break;
            case ORG_OPENPSA_ACCESSTYPE_WGPRIVATE:
                debug_add("Private object, only workgroup members can read and write");
                $object->set_privilege('midgard:read', 'EVERYONE', MIDCOM_PRIVILEGE_DENY);
                $object->set_privilege('midgard:owner', $owner_id, MIDCOM_PRIVILEGE_ALLOW);
                //TODO: Shouldn't this be midgard:owner as well ??
                $this->_set_attachment_permission($object, 'midgard:read', $owner_id, MIDCOM_PRIVILEGE_ALLOW);
                break;
            case ORG_OPENPSA_ACCESSTYPE_WGRESTRICTED:
                debug_add("Restricted object, only workgroup members can read and write. Subscribers can read");
                $object->set_privilege('midgard:read', 'EVERYONE', MIDCOM_PRIVILEGE_DENY);
                $object->set_privilege('midgard:owner', $owner_id, MIDCOM_PRIVILEGE_ALLOW);
                //TODO: Shouldn't this be midgard:owner as well ??
                $this->_set_attachment_permission($object, 'midgard:read', $owner_id, MIDCOM_PRIVILEGE_ALLOW);

                // Process a possible subscribers group
                $subscriber_group = $_MIDCOM->auth->get_group($owner_id.'subscribers');
                if ($subscriber_group)
                {
                    // Allow them to read the object
                    $object->set_privilege('midgard:read', $subscriber_group->id, MIDCOM_PRIVILEGE_ALLOW);

                    // But disallow reading of possible attachments
                    $this->_set_attachment_permission($object, 'midgard:read', $subscriber_group->id, MIDCOM_PRIVILEGE_DENY);
                }
                break;
        }
        debug_pop();
        return true;
    }

    function _set_attachment_permission($object, $privilege, $assignee, $value)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $attachments = $object->list_attachments();
        if ($attachments)
        {
            foreach ($attachments as $attachment)
            {
                debug_add("Setting {$value} to privilege {$privilege} for {$assignee} to attachment #{$attachment->id}");
                $attachment->set_privilege($privilege, $assignee, $value);
            }
        }
        debug_pop();
    }
}
?>