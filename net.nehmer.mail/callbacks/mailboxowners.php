<?php
/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-Site Mail System Owner Listing Callback.
 * 
 * This class is used by the mailbox management system when it has to list users that can own
 * a Mailbox.
 * 
 * The class uses the get_user method of MidCOM for element lookups as far as possible, as that
 * function operates cached.
 * 
 * @see midcom_helper_datamanager2_type_select
 * @package net.nehmer.mail
 */
class net_nehmer_mail_callbacks_mailboxowners extends midcom_baseclasses_components_purecode
{
    function net_nehmer_mail()
    {
        $this->_component = 'net.nehmer.mail';
        parent::midcom_baseclasses_components_purecode();
    }
    
    /** We ignore set_type, as we don't need the type. */
    function set_type(&$type) {}

    function get_name_for_key($key)
    {
        $user =& $_MIDCOM->auth->get_user($key);
        return $user->rname; 
    }

    function key_exists($key) 
    { 
        $user =& $_MIDCOM->auth->get_user($key);
        if (! $user)
        {
            return false;
        }
        return true;
    }

    function list_all() 
    { 
        $qb = midcom_db_person::new_query_builder();
        $qb->add_order('lastname');
        $qb->add_order('firstname');
        $result = $qb->execute();
        
        if (! $result)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Failed to execute query. See debug level log for details.', MIDCOM_LOG_WARN);
            debug_pop();
            return Array();
        }
        
        $options = Array();
        foreach ($result as $person)
        {
            $options[$person->guid] = $person->rname;
        }
        return $options; 
    }
}

?>