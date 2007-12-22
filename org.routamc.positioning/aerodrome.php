<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for aerodrome objects
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_aerodrome_dba extends __org_routamc_positioning_aerodrome_dba
{
    function org_routamc_positioning_aerodrome_dba($id = null)
    {
        return parent::__org_routamc_positioning_aerodrome_dba($id);
    }

    /**
     * @return org_routamc_positioning_city_dba City the airport caters for
     */
    function get_parent_guid_uncached()
    {
        if ($this->city)
        {
            $parent = new org_routamc_positioning_city_dba($this->city);
            if (! $parent)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Could not load City ID {$this->city} from the database, aborting.",
                    MIDCOM_LOG_INFO);
                debug_pop();
                return null;
            }
            return $parent->guid;
        }

        return null;
    }

    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        if (!empty($this->name))
        {
            return "{$this->icao} ({$this->name})";
        }
        return $this->icao;
    }

    /**
     * Don't save aerodrome if another aerodrome is in same place or exists with same ICAO
     */
    function _on_creating()
    {
        if (   $this->longitude
            && $this->latitude)
        {
            $qb = org_routamc_positioning_aerodrome_dba::new_query_builder();
            $qb->add_constraint('longitude', '=', $this->longitude);
            $qb->add_constraint('latitude', '=', $this->latitude);
            $qb->set_limit(1);
            $matches = $qb->execute_unchecked();
            if (count($matches) > 0)
            {
                // We don't need to save duplicate entries
                return false;
            }
        }

        $qb = org_routamc_positioning_aerodrome_dba::new_query_builder();
        $qb->add_constraint('icao', '=', $this->icao);
        $qb->set_limit(1);
        $matches = $qb->execute_unchecked();
        if (count($matches) > 0)
        {
            // We don't need to save duplicate entries
            return false;
        }
        return parent::_on_creating();
    }
}
?>