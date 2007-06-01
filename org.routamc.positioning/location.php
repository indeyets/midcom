<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM wrapper class for access to cached object locations
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_location_dba extends __org_routamc_positioning_location_dba
{
    function org_routamc_positioning_location_dba($id = null)
    {
        return parent::__org_routamc_positioning_location_dba($id);
    }

    /**
     * Returns the person who reported the position
     *
     * @return midcom_db_person Parent person
     */
    function get_parent_guid_uncached()
    {
        if (   $this->parent
            && $this->parentclass)
        {
            $classname = $this->parentclass;
            $parent = new $classname($this->parent);
            if (! $parent)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Could not load {$classname} {$this->parent} from the database, aborting.",
                    MIDCOM_LOG_INFO);
                debug_pop();
                return null;
            }
            return $parent->guid;
        }

        return null;
    }

    /**
     * Checks after location cache creation
     */
    function _on_created()
    {
        if (   !$this->log
            && $this->relation == ORG_ROUTAMC_POSITIONING_RELATION_IN)
        {
            // This location entry is defined as being made in a location,
            // but is stored to object directly without corresponding log,
            // create one.
            // This situation can happen for example when importing images
            // that have EXIF geo tags set
            $object = $this->get_parent();
            $log = new org_routamc_positioning_log();
            $log->date = $this->date;
            // TODO: Use 1.8 metadata authors instead?
            $log->person = $object->metadata->creator;
            $log->latitude = $this->latitude;
            $log->longitude = $this->longitude;
            $log->altitude = $this->altitude;
            $log->importer = 'objectlocation';
            // Usually the positions based on objects are manual, except in
            // case of GPS-equipped cameras etc. We still need to figure
            // out how to handle those.
            $log->accuracy = ORG_ROUTAMC_POSITIONING_ACCURACY_MANUAL;
            $log->create();
        }

        return true;
    }
}
?>