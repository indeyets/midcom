<?php
/**
 * @package net.nemein.netmon
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: aerodrome.php 3630 2006-06-19 10:03:59Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for host member objects
 *
 * @package net.nemein.netmon
 */
class net_nemein_netmon_host_dba extends __net_nemein_netmon_host_dba
{

    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    function _get_parent_guid_uncached()
    {
        if (!$this->parent)
        {
            return null;
        }
        $parent = new net_nemein_netmon_host_dba($this->parent);
        if (   !is_a($parent, 'net_nemein_netmon_host_dba')
            || empty($parent->guid))
        {
            return null;
        }
        return $parent->guid;
    }

    function _on_loaded()
    {
        if (empty($this->title))
        {
            // name shouldn't be empty be there might be bugs...
            if (!empty($this->name))
            {
                $this->title = $this->name;
            }
            else
            {
                $this->title = "host #{$this->id}";
            }
        }
        return true;
    }

    function _on_creating()
    {
        if (!$this->verify_name())
        {
            return false;
        }
        return true;
    }

    function _on_updating()
    {
        if (!$this->verify_name())
        {
            return false;
        }
        return true;
    }

    /**
     * Makes sure host has safe name
     *
     * @return boolean returns false if encountered unrecoverable error otherwise true
     */
    function verify_name()
    {
        if (   empty($this->name)
            && !empty($this->title))
        {
            $this->name = midcom_generate_urlname_from_string($this->title);
        }

        // PONDER: Check validity in stead of forcing to be url safe and potentially causing other issues at the same time
        $this->name = midcom_generate_urlname_from_string($this->name);

        // Must have name
        if (empty($this->name))
        {
            return false;
        }

        // Make sure name is unique
        if (!$this->name_is_unique())
        {
            return false;
        }
        return true;
    }

    /** 
     * Is the host name unique (as required)
     *
     * @return boolean indicating state
     */
    function name_is_unique()
    {
        if (empty($this->name))
        {
            return false;
        }
        $qb = net_nemein_netmon_host_dba::new_query_builder();
        $qb->add_constraint('sitegroup', '=', $_MIDGARD['sitegroup']);
        if ($this->id)
        {
            $qb->add_constraint('id', '<>', $this->id);
        }
        $qb->add_constraint('name', '=', (string)$this->name);
        $qb->set_limit(1);
        $count = $qb->count_unchecked();
        if ($count === false)
        {
            // QB error
            return false;
        }
        if ($count > 0)
        {
            return false;
        }
        return true;
    }

    /**
     * Returns a nagios2 compatible host definition
     *
     * @return string nagios2 compatible host definition
     */
    function to_nagios2()
    {
        $ret  = "define host{\n";
        $ret .= "    use generic-host\n";
        $ret .= "    host_name {$this->name}\n";
        $ret .= "    alias {$this->title}\n";
        if ($this->dnsname)
        {
            $ret .= "    address {$this->dnsname}\n";
        }
        else
        {
            $ret .= "    address {$this->ipaddress}\n";
        }
        if (   $this->parent
            && ($parent = $this->get_parent()))
        {
            $parents_str = $parent->name;
            while($parent = $parent->get_parent())
            {
                $parents_str .= ", {$parent->name}";
            }
            $ret .= "    parents {$parents_str}\n";
        }
        if ($contactgroup =& net_nemein_netmon_contactgroup_dba::get_cached())
        {
            $ret .= "    contact_groups {$contactgroup->nagiosname}\n";
        }
        if (!empty($this->nagiosextra))
        {
            // TODO:
        }
        $ret .= "}\n";
    }
}
?>