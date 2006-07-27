<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Positioning for a given Midgard object
 *
 * <b>Example:</b>
 * 
 * <code>
 * <?php
 * $object_position = new org_routamc_positioning_object($article);
 * $coordinates = $object_position->get_coordinates();
 * if (!is_null($coordinates))
 * {
 *     echo "<meta name=\"icbm\" value=\"{$coordinates['latitude']},{$coordinates['longitude']}\" />\n";
 * }
 * ?>
 * </code>
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_object extends midcom_baseclasses_components_purecode
{
    /**
     * The object we're looking for position of
     *
     * @var midcom_core_dbobject
     */
    var $_object = null;
    
    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_positioning_object($object)
    {
         $this->_component = 'org.routamc.positioning';
         $this->_object = $object;
         parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Get location object for the object
     *
     * @return org_routamc_positioning_location_dba
     */
    function seek_location_object()
    {
        $qb = org_routamc_positioning_location_dba::new_query_builder();
        $qb->add_constraint('parent', '=', $this->_object->guid);
        $qb->add_constraint('relation', '=', ORG_ROUTAMC_POSITIONING_RELATION_IN);
        $matches = $qb->execute();
        if (count($matches) > 0)
        {
            return $matches[0];
        }
        return null;
    }

    /**
     * Get log object based on creation time and creator of the object
     *
     * @return org_routamc_positioning_log_dba
     */
    function seek_log_object()
    {
        $qb = org_routamc_positioning_log_dba::new_query_builder();
        // TODO: Switch to metadata
        if (isset($this->_object->author))
        {
            $author = $this->_object->author;
        }
        elseif ($this->_object->creator)
        {
            $creator = $this->_object->creator;
        }
        else
        {
            return null;
        }
        $qb->add_constraint('person', '=', $author);
        $qb->add_constraint('date', '<=', $this->_object->created);
        $qb->add_order('date', 'DESC');
        $qb->set_limit(1);
        $matches = $qb->execute_unchecked();
        if (count($matches) > 0)
        {
            return $matches[0];
        }
        return null;
    }   

    /**
     * Get coordinates of the object
     *
     * @return Array
     */
    function get_coordinates($cache = true)
    {
        $coordinates = Array(
            'latitude'  => null,
            'longitude' => null,
            'altitude'  => null,
        );
    
        // Check if the object has a location set
        $location = $this->seek_location_object();
        if (is_object($location))
        {
            $coordinates['latitude'] = $location->latitude;
            $coordinates['longitude'] = $location->longitude;
            $coordinates['altitude'] = $location->altitude;

            // Consistency check
            // TODO: Use metadata.published            
            if ($location->date != $this->_object->created)
            {
                if ($location->log)
                {
                    // We are most likely pointing to wrong log. Remove this cached entry so we can recreate i
                    // again below
                    $location->delete();
                    $cache = true;
                }
                else
                {
                    // This location entry isn't coming from a log so it just needs to be rescheduled
                    $location->date = $this->_object->created;
                    $location->update();
                    return $coordinates;
                }
            }
            else
            {
                return $coordinates;
            }
        }
        
        // No location set, seek based on creator and creation time
        $log = $this->seek_log_object();
        if (is_object($log))
        {
            $coordinates['latitude'] = $log->latitude;
            $coordinates['longitude'] = $log->longitude;
            $coordinates['altitude'] = $log->altitude;
            
            if ($cache)
            {
                // Cache the object's location into a location object
                $location = new org_routamc_positioning_location_dba();
                $location->log = $log->id;
                $location->type = ORG_ROUTAMC_POSITIONING_RELATION_IN;
                // TODO: Use metadata.published
                $location->date = $this->_object->created;
                $location->parent = $this->_object->id;
                $location->parentclass = get_class($this->_object->id);
                // TODO: Save parent component
                $location->latitude = $log->latitude;
                $location->longitude = $log->longitude;
                $location->altitude = $log->altitude;
                $location->create();
            }
            
            return $coordinates;
        }
        
        // No coordinates found, return null
        return null;
    }
    
    function set_metadata()
    {
        $coordinates = $this->get_coordinates();
        if (!is_null($coordinates))
        {
            // ICBM tag as defined by http://geourl.org/
            $_MIDCOM->add_meta_head
            (
                Array
                (
                    'name' => 'icbm',
                    'content' => "{$coordinates['latitude']},{$coordinates['longitude']}",
                )
            );
        }
    }
}
