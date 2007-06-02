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