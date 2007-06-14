<?php
/**
 * @package cc.kaktus.exhibitions
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: navigation.php 3611 2006-06-17 08:39:21Z bergius $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Exhibitions NAP interface class.
 * 
 * @package cc.kaktus.exhibitions
 */

class cc_kaktus_exhibitions_navigation extends midcom_baseclasses_components_navigation
{
    /**
     * Simple constructor, calls base class.
     */
    function cc_kaktus_exhibitions_navigation()
    {
        parent::midcom_baseclasses_components_navigation();
    }

    /**
     * Get leaves for the current content topic
     * 
     * @access public
     */
    function get_leaves()
    {
        // Initialize the leaves array
        $leaves = array ();
        
        // Show the pseudo leaves
        if ($this->_config->get('show_pseudo_leaves'))
        {
            $leaves['past'] = array
            (
                MIDCOM_NAV_SITE => array
                (
                    MIDCOM_NAV_URL => 'past/',
                    MIDCOM_NAV_NAME => $this->_l10n->get('past exhibitions'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_GUID => null,
                MIDCOM_NAV_OBJECT => null,
                MIDCOM_META_CREATOR => null,
                MIDCOM_META_EDITOR => null,
                MIDCOM_META_CREATED => null,
                MIDCOM_META_EDITED => null,
            );
            $leaves['future'] = array
            (
                MIDCOM_NAV_SITE => array
                (
                    MIDCOM_NAV_URL => 'future/',
                    MIDCOM_NAV_NAME => $this->_l10n->get('future exhibitions'),
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_GUID => null,
                MIDCOM_NAV_OBJECT => null,
                MIDCOM_META_CREATOR => null,
                MIDCOM_META_EDITOR => null,
                MIDCOM_META_CREATED => null,
                MIDCOM_META_EDITED => null,
            );
        }
        
        // If master event has not been set do not show anything
        if (!$this->_config->get('master_event'))
        {
            return $leaves;
        }
        
        // Master event
        $master_event = $this->_config->get('master_event');
        
        if (   !$master_event
            || !isset($master_event->guid))
        {
            return $leaves;
        }
        
        // Get the latest items
        $qb = midcom_db_event::new_query_builder();
        $qb->add_constraint('up', '=', $master_event->id);
        $qb->add_order('start', 'DESC');
        $qb->set_limit($this->_config->get('navigation_items'));
        
        // Set the leaf data
        foreach ($qb->execute() as $event)
        {
            $leaves[$event->guid] = array
            (
                MIDCOM_NAV_SITE => array
                (
                    MIDCOM_NAV_URL => date('Y', $event->start) . "/{$event->extra}/",
                    MIDCOM_NAV_NAME => ($event->title != '') ? $event->title : $event->extra,
                ),
                MIDCOM_NAV_ADMIN => null,
                MIDCOM_NAV_GUID => $event->guid,
                MIDCOM_NAV_OBJECT => $event,
                MIDCOM_META_CREATOR => $event->metadata->creator,
                MIDCOM_META_EDITOR => $event->metadata->revisor,
                MIDCOM_META_CREATED => $event->metadata->created,
                MIDCOM_META_EDITED => $event->metadata->revised,
            );
        }
        
        return $leaves;
    }
}
?>