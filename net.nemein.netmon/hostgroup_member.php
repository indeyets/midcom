<?php
/**
 * @package net.nemein.netmon
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for hostgroup member objects
 *
 * @package net.nemein.netmon
 */
class net_nemein_netmon_hostgroup_member_dba extends __net_nemein_netmon_hostgroup_member_dba
{

    function net_nemein_netmon_hostgroup_member_dba($id = null)
    {
        return parent::__net_nemein_netmon_hostgroup_member_dba($id);
    }

    function _on_creating()
    {
        if (   !$this->host
            || !$this->hostgroup)
        {
            return false;
        }
        return true;
    }

    function _on_updating()
    {
        if (   !$this->host
            || !$this->hostgroup)
        {
            return false;
        }
        return true;
    }

}
?>