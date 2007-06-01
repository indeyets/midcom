<?php
/**
 * @package net.nemein.netmon
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * List hostgroups in system
 *
 * @return array of hostgroup objects keyed by id. Value is title.
 */
function net_nemein_netmon_hostgroup_options()
{
    static $ret = array();
    if (!empty($ret))
    {
        return $ret;
    }
    $qb = net_nemein_netmon_hostgroup_dba::new_query_builder();
    $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
    $hostgroups = $qb->execute();
    if (!is_array($hostgroups))
    {
        return $ret;
    }
    foreach($hostgroups as $hostgroup)
    {
        $ret[$hostgroup->id] = $hostgroup->title;
    }
    return $ret;
}



?>