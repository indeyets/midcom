<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:topic.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM level replacement for the Midgard Topic record with framework support.
 *
 * Note, as with all MidCOM DB layer objects, you should not use the GetBy*
 * operations directly, instead, you have to use the constructor's $id parameter.
 *
 * Also, all QueryBuilder operations need to be done by the factory class
 * obtainable as midcom_application::dbfactory.
 *
 * This class uses an auto-generated base class provided by midcom_services_dbclassloader.
 *
 * @package midcom.baseclasses
 * @see midcom_services_dbclassloader
 */
class midcom_baseclasses_database_topic extends __midcom_baseclasses_database_topic
{
    function __construct($id = null)
    {
        parent::__construct($id);
    }

    /**
     * Returns the Parent of the Topic, which is always another topic.
     *
     * @return MidgardObject Parent topic (null if we have a root topic).
     */
    function get_parent_guid_uncached()
    {
        return midcom_baseclasses_database_topic::_get_parent_guid_uncached_static_topic($this->up);
    }
    
    /**
     * Statically callable method to get parent guid when object guid is given
     * 
     * Uses midgard_collector to avoid unnecessary full object loads
     *
     * @todo when 1.8.1 is released convert to use single collector with linked guid property
     * @param guid $guid guid of topic to get the parent for
     */
    function get_parent_guid_uncached_static($guid)
    {
        if (empty($guid))
        {
            return null;
        }
        /* 1.8.1 version:
        $mc_topic = midcom_baseclasses_database_topic::new_collector('guid', $guid);
        $mc_topic->add_value_property('up.guid');
        if (!$mc_topic->execute())
        {
            // Error
            return null;
        }
        $mc_topic_keys = $mc_topic->list_keys();
        list ($key, $copy) = each ($mc_topic_keys);
        $parent_guid = $mc_topic->get_subkey($key, 'guid');
        */
        $mc_topic = midcom_baseclasses_database_topic::new_collector('guid', $guid);
        $mc_topic->add_value_property('up');
        if (!$mc_topic->execute())
        {
            // Error
            return null;
        }
        $mc_topic_keys = $mc_topic->list_keys();
        list ($key, $copy) = each ($mc_topic_keys);
        $parent_id = (int) $mc_topic->get_subkey($key, 'up');
        if ($parent_id == 0)
        {
            // Root-level topic
            return null;
        }
        $mc_parent = midcom_baseclasses_database_topic::new_collector('id', $parent_id);
        $mc_parent->add_value_property('guid');
        if (!$mc_parent->execute())
        {
            // ErrorA
            return null;
        }
        $mc_parent_keys = $mc_parent->list_keys();
        $parent_guids = array_keys($mc_parent_keys);
        if (count($parent_guids) == 0)
        {
            return null;
        }
        
        $parent_guid = $parent_guids[0];
        if ($parent_guid === false)
        {
            return null;
        }
        return $parent_guid;
    }
    
    /**
     * Get topic guid statically
     *
     * used by get_parent_guid_uncached_static
     *
     * @param id $parent_id id of topic to get the guid for
     */
    function _get_parent_guid_uncached_static_topic($parent_id)
    {
        if (empty($parent_id))
        {
            return null;
        }
        $mc_parent = midcom_baseclasses_database_topic::new_collector('id', $parent_id);
        $mc_parent->add_value_property('guid');
        if (!$mc_parent->execute())
        {
            // Error
            return null;
        }
        $mc_parent_keys = $mc_parent->list_keys();
        $parent_guids = array_keys($mc_parent_keys);
        $parent_guid = $parent_guids[0];
        if ($parent_guid === false)
        {
            return null;
        }
        return $parent_guid;
    }

    function get_dba_parent_class()
    {
        return 'midcom_baseclasses_database_topic';
    }
    
    /**
     * Generates a new URL-safe name.
     *
     * @access protected
     */
    function _on_created()
    {
        if (isset($GLOBALS['midcom_baseclasses_database_topic_on_created_loop_{$this->guid}']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Detected _on_created loop on #{$this->id}", MIDCOM_LOG_ERROR);
            debug_pop();
        }
        else
        {
            $GLOBALS['midcom_baseclasses_database_topic_on_created_loop_{$this->guid}'] = true;
            midcom_baseclasses_core_dbobject::generate_urlname($this, 'extra');
            unset($GLOBALS['midcom_baseclasses_database_topic_on_created_loop_{$this->guid}']);
        }
        return true;
    }
    
    /**
     * Generates a new URL-safe name.
     *
     * @access protected
     */
    function _on_updated()
    {
        if (isset($GLOBALS['midcom_baseclasses_database_topic__on_updated_loop_{$this->guid}']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Detected _on_updated loop on #{$this->id}", MIDCOM_LOG_ERROR);
            debug_pop();
        }
        else
        {
            $GLOBALS['midcom_baseclasses_database_topic__on_updated_loop_{$this->guid}'] = true;
            midcom_baseclasses_core_dbobject::generate_urlname($this, 'extra');
            unset($GLOBALS['midcom_baseclasses_database_topic__on_updated_loop_{$this->guid}']);
        }
        return true;
    } 
}

?>