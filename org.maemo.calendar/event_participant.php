<?php
/**
 * @package org.maemo.calendar
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: entry.php 6159 2007-06-03 00:17:12Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

$_MIDCOM->componentloader->load_graceful('net.nehmer.buddylist');
$_MIDCOM->componentloader->load('org.openpsa.calendar');

/**
 * @package org.maemo.calendar
 */
 //midcom_db_person
 // extends net_nehmer_buddylist_entry
class org_maemo_calendar_eventparticipant
{
    var $name = null;
    var $id = null;
        
    function org_maemo_calendar_eventparticipant($id = null)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $user = new midcom_db_person($id);
        $current_user = $_MIDCOM->auth->user->get_storage();
        
        $this->name =& $user->name;
        
        if (! $user)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to get person object with id '{$id}'");
        }
        
        debug_add("Comparing {$user->id} with {$current_user->id}!");
        if ($user->id == $current_user->id)
        {
            debug_add("Participant {$user->id} is us!");
            return $user;
        }

        $qb = net_nehmer_buddylist_entry::new_query_builder();
        $qb->add_constraint('account', '=', $current_user->guid);
        //$qb->add_constraint('isapproved', '=', true);
        $qb->add_constraint('blacklisted', '=', false);
        $buddies_qb = $qb->execute();

        foreach ($buddies_qb as $buddy)
        {
            $person = $buddy->get_buddy_user()->get_storage();
            debug_print_r('person: ',$person);
            if ($person)
            {
                debug_add("Comparing {$user->id} with {$person->id}!");
                if ($user->id == $person->id)
                {
                    debug_add("Participant {$user->id} is our buddy!");
                    return $person;
                }
            }
        }
            
        debug_add("Participant {$user->id} is not us nor our buddy!");            
        
        debug_pop();
    }
    
    function new_query_builder()
    {
        return $_MIDCOM->dbfactory->new_query_builder('midcom_db_person');
    }    
    
}

?>