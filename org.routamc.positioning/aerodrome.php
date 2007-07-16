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
}
?>