<?php
/**
 * @package net.nemein.netmon
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wrap group to get some of the helper methods we need
 *
 * @package net.nemein.netmon
 */
class net_nemein_netmon_contact_dba extends org_openpsa_contacts_person_dba
{
    var $nagiosname = '';

    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    /**
     * static method to get a cached copy of the object
     *
     * @param mixed &$id any valid identifier for instantiating object
     */
    function &get_cached($id)
    {
        if (!isset($GLOBALS['net_nemein_netmon_contact_dba__get_cached']))
        {
            $GLOBALS['net_nemein_netmon_contact_dba__get_cached'] = array();
        }
        if (isset($GLOBALS['net_nemein_netmon_contact_dba__get_cached'][$id]))
        {
            return $GLOBALS['net_nemein_netmon_contact_dba__get_cached'][$id];
        }
        $object = new net_nemein_netmon_contact_dba($id);
        if (!is_object($object))
        {
            $x = false;
            return $x;
        }
        $GLOBALS['net_nemein_netmon_contact_dba__get_cached'][$object->guid] = $object;
        $GLOBALS['net_nemein_netmon_contact_dba__get_cached'][$object->id] =& $GLOBALS['net_nemein_netmon_contact_dba__get_cached'][$object->guid];

        return $GLOBALS['net_nemein_netmon_contact_dba__get_cached'][$object->guid];
    }

    function _on_loaded()
    {
        if (!parent::_on_loaded())
        {
            return false;
        }
        if ($this->username)
        {
            $this->nagiosname = strtolower($this->username);
        }
        else
        {
            $this->nagiosname = midcom_generate_urlname_from_string($this->name);
        }
        return true;
    }
}
?>