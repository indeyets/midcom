<?php
/**
 * @package org.routamc.positioning
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Positioning for a given person
 *
 * <b>Example:</b>
 *
 * <code>
 * <?php
 * $user_position = new org_routamc_positioning_person($user);
 * $coordinates = $user_position->get_coordinates($time);
 * if (!is_null($coordinates))
 * {
 *     echo sprintf('On %s % was in %s, %s', strftime('%x' $time), $user->name, $coordinates['latitude'], $coordinates['longitude']);
 *     // Will print "On 19.6.2006 Henri Bergius was in 60.2345, 25.00456"
 * }
 * ?>
 * </code>
 *
 * @package org.routamc.positioning
 */
class org_routamc_positioning_person extends midcom_baseclasses_components_purecode
{
    /**
     * The person we're looking for position of
     *
     * @var midcom_db_person
     */
    var $_person = null;

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function org_routamc_positioning_person($person)
    {
        $this->_component = 'org.routamc.positioning';

        if (   !is_a($person, 'midgard_person')
            && !is_a($person, 'org_openpsa_contacts_person'))
        {
            $this->_person = null;
            return false;
        }

        $this->_person = $person;

        parent::midcom_baseclasses_components_purecode();
    }

    /**
     * Get log object based on given time and the person
     *
     * @return org_routamc_positioning_log_dba
     */
    function seek_log($time = null)
    {
        /*if (!$this->_person->can_do('org.routamc.positioning:location'))
        {
            return null;
        }*/
        if (   is_null($this->_person)
            || !$this->_person->id)
        {
            return null;
        }
        if (is_null($time))
        {
            $time = time();
        }
        $qb = org_routamc_positioning_log_dba::new_query_builder();
        $qb->add_constraint('person', '=', (int) $this->_person->id);
        $qb->add_constraint('date', '<=', (int) $time);
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
    function get_coordinates($time = null)
    {
        if (is_null($time))
        {
            // Default to current time
            $time = time();
        }

        $coordinates = Array(
            'latitude'  => null,
            'longitude' => null,
            'altitude'  => null,
        );

        // No location set, seek based on creator and creation time
        $log = $this->seek_log($time);
        if (is_object($log))
        {
            $coordinates['latitude'] = $log->latitude;
            $coordinates['longitude'] = $log->longitude;
            $coordinates['altitude'] = $log->altitude;

            return $coordinates;
        }

        // No coordinates found, return null
        return null;
    }
}