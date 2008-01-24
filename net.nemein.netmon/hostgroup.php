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
class net_nemein_netmon_hostgroup_dba extends __net_nemein_netmon_hostgroup_dba
{

    function __construct($id = null)
    {
        return parent::__construct($id);
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
            elseif ($this->id)
            {
                $this->title = "hostgroup #{$this->id}";
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
     * Makes sure hostgroup has safe name
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

        // PONDER: Check validity in stead of forcong to be url safe
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
     * Is the hostgroup name unique (as required)
     *
     * @return boolean indicating state
     */
    function name_is_unique()
    {
        if (empty($this->name))
        {
            return false;
        }
        $qb = net_nemein_netmon_hostgroup_dba::new_query_builder();
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

}
?>