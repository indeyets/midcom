<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: country.php 6148 2007-06-02 20:49:53Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for country objects
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_country_dba extends __org_routamc_positioning_country_dba
{
    function org_routamc_positioning_country_dba($id = null)
    {
        return parent::__org_routamc_positioning_country_dba($id);
    }

    /**
     * Human-readable label for cases like Asgard navigation
     */
    function get_label()
    {
        return $this->name;
    }

    /**
     * Don't save country if another country with name exists
     */
    function _on_creating()
    {
        $qb = org_routamc_positioning_country_dba::new_query_builder();
        $qb->add_constraint('name', '=', $this->name);
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