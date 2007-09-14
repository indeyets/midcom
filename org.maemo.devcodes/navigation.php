<?php
/**
 * @package org.maemo.devcodes 
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: navigation.php 4392 2006-10-22 08:39:17Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * org.maemo.devcodes NAP interface class
 *
 * See the individual member documentations about special NAP options in use.
 *
 * @package org.maemo.devcodes
 */
class org_maemo_devcodes_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function org_maemo_devcodes_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    function get_leaves()
    {
        $leaves = array();

        $qb = org_maemo_devcodes_device_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->_topic->id);
        $qb->add_order('title', 'ASC');
        $qb->add_order('codename', 'ASC');
        $result = $qb->execute();
        if (!is_array($result))
        {
            // Fatal QB error
            debug_push_class(__CLASS__, __FUNCTION);
            debug_add("QB failed fatally when listing devices", MIDCOM_LOG_ERROR);
            debug_pop();
            return $leaves;
        }

        foreach ($result as $device)
        {
            if (!$device->can_do('org.maemo.devcodes:read'))
            {
                // display in navi only to those who have full read rights to the device
                continue;
            }
            $metadata =& midcom_helper_metadata::retrieve($device);
            
            $leaves[$device->id] = array
            (
                MIDCOM_NAV_SITE => Array
                (
                    MIDCOM_NAV_URL => "{$device->guid}.html",
                    MIDCOM_NAV_NAME => $device->title,
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_GUID => $device->guid,
                MIDCOM_NAV_OBJECT => $device,
                MIDCOM_NAV_NOENTRY => (bool) $metadata->get('navnoentry'),
                MIDCOM_META_CREATOR => $metadata->get('creator'),
                MIDCOM_META_EDITOR => $metadata->get('revisor'),
                MIDCOM_META_CREATED => $metadata->get('created'),
                MIDCOM_META_EDITED => $metadata->get('edited')
            );

        }

        return $leaves;
    }
}

?>
