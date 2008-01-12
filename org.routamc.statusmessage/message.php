<?php
/**
 * @package org.routamc.statusmessage
 * @author Nemein Oy, http://www.nemein.com/
 * @version $Id: message.php,v 1.4 2006/05/11 15:43:12 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * MidCOM wrapped base class, keep logic here
 *
 * @package org.routamc.statusmessage
 */
class org_routamc_statusmessage_message_dba extends __org_routamc_statusmessage_message_dba
{
    function org_routamc_statusmessage_message_dba($id = null)
    {
        return parent::__org_routamc_statusmessage_message_dba($id);
    }
    
    function get_parent_guid_uncached()
    {
        if ($this->person != 0)
        {
            $parent = new midcom_db_person($this->person);
            return $parent->guid;
        }
        else
        {
            return null;
        }
    }
    
    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        return strftime('%x', $this->metadata->published) . " {$this->status}";
    }
}
?>