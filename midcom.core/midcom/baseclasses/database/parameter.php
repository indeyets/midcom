<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:parameter.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM level replacement for the Midgard Parameter record with framework support.
 *
 * The uplink is the parentguid parameter.
 *
 * Note, as with all MidCOM DB layer objects, you should not use the get_by*
 * operations directly, instead, you have to use the constructor's $id parameter.
 *
 * Also, all QueryBuilder operations need to be done by the factory class
 * obtainable through the statically callable new_query_builder() DBA methods.
 *
 * This class uses an auto-generated base class provided by midcom_services_dbclassloader.
 *
 * @package midcom.baseclasses
 * @see midcom_services_dbclassloader
 */
class midcom_baseclasses_database_parameter extends __midcom_baseclasses_database_parameter
{
    function __construct($id = null)
    {
        $this->_use_rcs = false;
        parent::__construct($id);
    }

    /**
     * Returns the Parent of the Parameter.
     *
     * @return MidgardObject Parent object or NULL if there is none.
     */
    function get_parent_guid_uncached_static()
    {
        $mc = new midgard_collector('midgard_parameter', 'guid', $guid);
        $mc->set_key_property('parentguid');
        $mc->execute();
        $link_values = $mc->list_keys();
        if (!$link_values)
        {
            return null;
        }
        
        foreach ($link_values as $key => $value)
        {
            return $key;
        }
    }
}

?>