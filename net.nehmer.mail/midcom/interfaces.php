<?php

/**
 * @package net.nehmer.mail
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * On-Site E-Mail System MidCOM interface class.
 *
 * @package net.nehmer.mail
 */
class net_nehmer_mail_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_mail_interface()
    {
        parent::midcom_baseclasses_components_interface();

        // NAP Leaf IDs
        define ('NET_NEHMER_MAIL_LEAFID_NEW', 0);
        
        // ERROR Codes 
        define ('NET_NEHMER_MAIL_ERROR_DENIED', 1000);
        define ('NET_NEHMER_MAIL_ERROR_MAILBOXFULL', 1001);        
        
        // STATUS codes
        define('NET_NEHMER_MAIL_STATUS_SENT', 2000);
        define('NET_NEHMER_MAIL_STATUS_READ', 2001);
        define('NET_NEHMER_MAIL_STATUS_UNREAD', 2002);
        define('NET_NEHMER_MAIL_STATUS_STARRED', 2003);
        define('NET_NEHMER_MAIL_STATUS_REPLIED', 2004);
        define('NET_NEHMER_MAIL_STATUS_SPAM', 2005);
                
        $this->_component = 'net.nehmer.mail';

        $this->_autoload_files = Array(
            'viewer.php',
            'navigation.php',
            'mail.php',
            'mailbox.php',
            'callbacks/mailboxowners.php');

        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            'net.nehmer.markdown',
            'net.nemein.tag',
            'org.openpsa.notifications',
        );
    }

    /**
     * Simple lookup method which tries to map the guid to an mailbox or mail.
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {
        $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
        if (is_a($object, 'net_nehmer_mail_mailbox'))
        {
            return "mailbox/{$object->guid}.html";
        }
        if (is_a($object, 'net_nehmer_mail_mail'))
        {
            return "mail/view/{$object->guid}.html";
        }
    }

    /**
     * The delete handler will drop all mailboxes associated with any person record that has been
     * deleted. We don't need to check for watched classes at this time, since we have no other
     * watches defined.
     */
    function _on_watched_dba_delete($object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = net_nehmer_mail_mailbox::new_query_builder();
        $qb->add_constraint('owner', '=', $object->guid);
        $result = $qb->execute();
        if ($result)
        {
            foreach ($result as $entry)
            {
                debug_add("Deleting Mailbox {$entry->name} ID {$entry->id} for user {$object->username}");
                $entry->delete();
            }
        }
        debug_pop();
    }

}
?>
