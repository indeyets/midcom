<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for city objects
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_city_dba extends __org_routamc_positioning_city_dba
{
    function __construct($id = null)
    {
        return parent::__construct($id);
    }

    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        return $this->city;
    }

    /**
     * @return org_routamc_positioning_country_dba Country the city is in
     */
    function get_parent_guid_uncached()
    {
        if ($this->country)
        {
            $qb = org_routamc_positioning_country_dba::new_query_builder();
            $qb->add_constraint('code', '=', $this->country);
            $countries = $qb->execute();
            if (count($countries) == 0)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Could not load Country ID {$this->country} from the database, aborting.",
                    MIDCOM_LOG_INFO);
                debug_pop();
                return null;
            }
            return $countries[0]->guid;
        }

        return null;
    }

    /**
     * Don't save city if another city is in same place
     */
    function _on_creating()
    {
        $qb = org_routamc_positioning_city_dba::new_query_builder();
        $qb->add_constraint('longitude', '=', (double)$this->longitude);
        $qb->add_constraint('latitude', '=', (double)$this->latitude);
        $qb->set_limit(1);
        $matches = $qb->execute_unchecked();
        if (   !empty($matches)
               /* doublecheck */
            && $matches[0]->longitude === $this->longitude
            && $matches[0]->latitude === $this->latitude
            )
        {
            // We don't need to save duplicate entries
            mgd_set_errno(MGD_ERR_DUPLICATE);
            return false;
        }
        return parent::_on_creating();
    }

    function get_by_name($name)
    {
        // Seek by strict city name first
        $qb = org_routamc_positioning_city_dba::new_query_builder();
        $qb->add_constraint('city', 'LIKE', $name);
        $qb->set_limit(1);
        $matches = $qb->execute_unchecked();
        if (count($matches) > 0)
        {
            return $matches[0];
        }
        
        // Strict name didn't match, seek by alternate names
        $qb = org_routamc_positioning_city_dba::new_query_builder();
        $qb->add_constraint('alternatenames', 'LIKE', "%{$name}%");
        // Most likely we're interested in the biggest city that matches
        $qb->add_order('population', 'DESC');
        $qb->set_limit(1);
        $matches = $qb->execute_unchecked();
        if (count($matches) > 0)
        {
            return $matches[0];
        }

        return false;
    }
}
?>