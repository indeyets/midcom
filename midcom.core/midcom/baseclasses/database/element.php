<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:element.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM level replacement for the Midgard Style Element record with framework support.
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
class midcom_baseclasses_database_element extends __midcom_baseclasses_database_element
{
    function midcom_baseclasses_database_element($id = null)
    {
        parent::__midcom_baseclasses_database_element($id);
    }

    /**
     * Returns the Parent of the Element. This is the style the element is assigned to.
     *
     * @return MidgardObject Parent object or NULL if there is none.
     */
    function get_parent_guid_uncached()
    {
        if ($this->style == 0)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Current element is:', $this);
            debug_add("The Style Element {$this->id} has its style member set to 0, this is a critical database inconsistency.",
                MIDCOM_LOG_INFO);
            debug_pop();
            return null;
        }

        $parent = new midcom_baseclasses_database_style($this->style);
        if (! $parent)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Could not load Style ID {$this->up} from the database, aborting.",
                MIDCOM_LOG_INFO);
            debug_pop();
            return null;
        }

        return $parent->guid;
    }
}

?>