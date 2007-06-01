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
    function org_routamc_positioning_city_dba($id = null)
    {
        return parent::__org_routamc_positioning_city_dba($id);
    }

    /**
     * Don't save city if another city is in same place
     */
    function _on_creating()
    {
        $qb = org_routamc_positioning_city_dba::new_query_builder();
        $qb->add_constraint('longitude', '=', $this->longitude);
        $qb->add_constraint('latitude', '=', $this->latitude);
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