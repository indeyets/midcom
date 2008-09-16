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
 *     echo "<meta name=\"icbm\" content=\"{$coordinates['latitude']},{$coordinates['longitude']}\" />\n";
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
    function __construct($object)
    {
         $this->_component = 'org.routamc.positioning';
         $this->_object = $object;
         parent::__construct();
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
        $qb->begin_group('OR');
            $qb->add_constraint('relation', '=', ORG_ROUTAMC_POSITIONING_RELATION_IN);
            $qb->add_constraint('relation', '=', ORG_ROUTAMC_POSITIONING_RELATION_LOCATED);
        $qb->end_group('OR');
        $qb->add_order('metadata.published', 'DESC');
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
    function seek_log_object($person = null, $time = null)
    {
        if (   is_integer($person)
            || is_string($person))
        {
            $person_guid = $person;
        }
        elseif (is_null($person))
        {
            if (   isset($this->_object->metadata->authors)
                && $this->_object->metadata->authors)
            {
                $authors = explode('|', substr($this->_object->metadata->authors, 1, -1));
                if (!$authors)
                {
                    return null;
                }
                $person_guid = $authors[0];
            }
            elseif (   isset($this->_object->metadata->creator)
                    && $this->_object->metadata->creator)
            {
                $person_guid = $this->_object->metadata->creator;
            }
            elseif (   isset($this->_object->author)
                    && $this->_object->author)
            {
                $person_guid = $this->_object->author;
            }
            else
            {
                return null;
            }
        }
        else
        {
            $person_guid = $person->guid;
        }

        if (!$person_guid)
        {
            return null;
        }

        if (is_null($time))
        {
            $time = $this->_object->metadata->published;
        }

        $person = new midcom_db_person($person_guid);
        if (!$person->id)
        {
            return null;
        }

        $qb = org_routamc_positioning_log_dba::new_query_builder();
        $qb->add_constraint('person', '=', $person->id);
        $qb->add_constraint('date', '<=', $time);
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
    function get_coordinates($person = null, $time = null, $cache = true)
    {
        $coordinates = array
        (
            'latitude'  => null,
            'longitude' => null,
            'altitude'  => null,
        );

        if (   is_a($this->_object, 'midgard_person')
            || is_a($this->_object, 'org_openpsa_person'))
        {
            // This is a person record. Seek log
            $user_position = new org_routamc_positioning_person($this->_object);
            return $user_position->get_coordinates($time);
        }

        if (is_null($time))
        {
            if (!isset($this->_object->metadata->published))
            {
                //return null;
                $time = time();
            }
            $time = $this->_object->metadata->published;
        }

        // Check if the object has a location set
        $location = $this->seek_location_object();
        if (   is_object($location)
            && $location->guid)
        {
            $coordinates['latitude'] = $location->latitude;
            $coordinates['longitude'] = $location->longitude;
            $coordinates['altitude'] = $location->altitude;

            // Consistency check
            if ($location->date != $time)
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
                    $location->date = $time;
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
        $log = $this->seek_log_object($person, $time);
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
                $location->relation = (int) ORG_ROUTAMC_POSITIONING_RELATION_IN;
                $location->date = $time;
                $location->parent = $this->_object->guid;
                $location->parentclass = get_class($this->_object);
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
                array
                (
                    'name' => 'icbm',
                    'content' => "{$coordinates['latitude']},{$coordinates['longitude']}",
                )
            );
        }
    }
}