<?php
/**
 * @package org.openpsa.notifications
 * @author Henri Bergius, http://bergie.iki.fi
 * @version $Id: main.php,v 1.1 2006/05/24 16:01:00 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/** 
 * Class for notifying users of different events
 * @package org.openpsa.notifications 
 */
class org_openpsa_notifications extends midcom_baseclasses_components_purecode
{    

    function org_openpsa_notifications()
    {
        $this->_component = 'org.openpsa.notifications';
    
        parent::midcom_baseclasses_components_purecode();
    }
    
    /**
     * Sends a notice to a selected person
     *
     * @param string $key Key of the event in format component:event
     * @param string $recipient GUID of the receiving person
     * @param Array $message Notification message in array format understood by message renderer
     */
    function notify($key, $recipient, $message)
    {
        // Find in which ways to notify the user
        $notification_types = org_openpsa_notifications::merge_notification_prefences($key, $recipient);
        
        if (   count($notification_types) == 0
            || in_array('none', $notification_types))
        {
            // User doesn't wish to be notified
            return true;
        }
        
        // Figure out notification rendering handler
        // TODO: Support component-specific renderers via class_exists() or handler-like autoloading
        // For example: if (class_exists('org_openpsa_calendar_notifications_new_event'))
        $notifier = new org_openpsa_notifications_notifier($recipient);
        
        // Send all types requested by user
        foreach ($notification_types as $type)
        {
            $method = "send_{$type}";        
            if (method_exists($notifier, $method))
            {
                $notifier->$method($message);
            }
        }
        return true;
    }

    /**
     * Find out how a person prefers to get the event notification
     *
     * @param string $key Key of the event in format component:event
     * @param string $recipient GUID of the receiving person
     * @return Array options supported by user
     */    
    function merge_notification_prefences($key, $recipient)
    {
        // TODO: Should we sudo here to ensure getting correct prefs regardless of ACLs?
        $preferences = Array();
        $recipient = new midcom_db_person($recipient);
        
        if (!$recipient)
        {
            return $preferences;
        }
        
        // If user has preference for this message, we use only that
        $personal_preferences = $recipient->list_parameters('org.openpsa.notifications');
        if (   count($personal_preferences) > 0
            && array_key_exists($key, $personal_preferences))
        {
            $preferences[] = $personal_preferences[$key];
            return $preferences;
        }
        
        // If groups have preferences, use all of those
        $qb = new MidgardQueryBuilder('midgard_parameter');
        $qb->add_constraint('tablename', '=', 'grp');
        $qb->add_constraint('domain', '=', 'org.openpsa.notifications');  
        $qb->add_constraint('name', '=', $key);          

        // Seek user's groups        
        $member_qb = midcom_db_member::new_query_builder();
        $member_qb->add_constraint('uid', '=', $recipient->id);
        $memberships = $member_qb->execute();
        $qb->begin_group('OR');
        foreach ($memberships as $member)
        {
            $qb->add_constraint('oid', '=', $member->gid);
        }
        $qb->end_group();
        
        $group_preferences = @$qb->execute();
        if (count($group_preferences) > 0)
        {
            foreach ($group_preferences as $preference)
            {
                $preferences[] = $preference->value;
            }
        }
        
        return $preferences;
    }
}
?>