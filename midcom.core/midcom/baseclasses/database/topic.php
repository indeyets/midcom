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
 * This class uses a auto-generated base class provided by midcom_services_dbclassloader.
 *
 * @package midcom.baseclasses
 * @see midcom_services_dbclassloader
 */
class midcom_baseclasses_database_topic extends __midcom_baseclasses_database_topic
{
    function midcom_baseclasses_database_topic($id = null)
    {
        parent::__midcom_baseclasses_database_topic($id);
    }

    /**
     * Returns the Parent of the Topic, which is always another topic.
     *
     * @return MidgardObject Parent topic (null if we have a root topic).
     */
    function get_parent_guid_uncached()
    {
        return midcom_baseclasses_database_topic::get_parent_guid_uncached_static($this->guid);
    }

    /**
     * Statically callable method to get parent guid when object guid is given
     * 
     * Uses midgard_collector to avoid unneccessary full object loads
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
        $parent_id = $mc_topic->get_subkey($key, 'up');
        $mc_parent = midcom_baseclasses_database_topic::new_collector('id', $parent_id);
        $mc_parent->add_value_property('guid');
        if (!$mc_parent->execute())
        {
            // Error
            return null;
        }
        $mc_parent_keys = $mc_parent->list_keys();
        list ($key2, $copy2) = each ($mc_parent_keys);
        $parent_guid = $mc_parent->get_subkey($key2, 'guid');
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
}

?>