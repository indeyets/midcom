<?php
/**
 * @package net.nemein.reservations 
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * net.nemein.reservations NAP interface class
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package net.nemein.reservations
 */
class net_nemein_reservations_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function net_nemein_reservations_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Returns a static leaf list with access to the archive.
     */
    function get_leaves()
    {
        $leaves = array();
        
        $leaves[$this->_topic->id . ':list'] = array
        (
            MIDCOM_NAV_SITE => Array
            (
                MIDCOM_NAV_URL => 'reservation/list/',
                MIDCOM_NAV_NAME => $this->_l10n->get('reservations list'),
            ),
            MIDCOM_NAV_ADMIN => null,
            MIDCOM_META_CREATOR => $this->_topic->metadata->creator,
            MIDCOM_META_EDITOR => $this->_topic->metadata->revisor,
            MIDCOM_META_CREATED => $this->_topic->metadata->created,
            MIDCOM_META_EDITED => $this->_topic->metadata->revised
        );

        return $leaves;
    }

}

?>
