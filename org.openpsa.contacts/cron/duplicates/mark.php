<?php
/**
 * @package org.openpsa.contacts
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: cleartokens.php,v 1.1 2006/03/27 14:10:29 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for clearing tokens from old send receipts
 * @package org.openpsa.contacts
 */
class org_openpsa_contacts_cron_duplicates_mark extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    /**
     * Find possible duplicates and mark them
     */
    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');

        $_MIDCOM->auth->request_sudo('org.openpsa.contacts');
        ignore_user_abort();

        $dfinder = new org_openpsa_contacts_duplicates();
        $dfinder->config =& $this->_config;
        $dfinder->mark_all(false);

        $_MIDCOM->auth->drop_sudo();

        debug_add('Done');
        debug_pop();
    }
}
?>