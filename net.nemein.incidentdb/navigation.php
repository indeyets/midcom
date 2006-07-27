<?php
/**
 * @package net.nemein.incidentdb
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * IncidentDB NAP interface class
 * 
 * @todo document
 * 
 * @package net.nemein.incidentdb
 */
class net_nemein_incidentdb_navigation 
{

    var $_object;
    var $_config;

    function net_nemein_incidentdb_navigation() 
    {
        $this->_object = NULL;
        $this->_config = $GLOBALS['midcom_component_data']['net.nemein.incidentdb']['config'];
    }

    function get_leaves() 
    {
        // This components Topics have no leaves
        return Array ();
    }

    function get_node() 
    {
        $topic = &$this->_object;

        return array (
            MIDCOM_NAV_URL => "",
            MIDCOM_NAV_NAME => $topic->extra,
            MIDCOM_META_CREATOR => $topic->creator,
            MIDCOM_META_EDITOR => $topic->revisor,
            MIDCOM_META_CREATED => $topic->created,
            MIDCOM_META_EDITED => $topic->revised
        );
    }

    function set_object($object) 
    {
        $this->_object = $object;
        $this->_config->store_from_object($object, "net.nemein.incidentdb");
        return TRUE;
    }

}

?>
