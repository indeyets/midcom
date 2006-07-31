<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id:eventmember.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM level replacement for the Midgard Eventmember record with framework support.
 * 
 * An event member has its event as explicit parent, *not* its person.
 * 
 * Note, as with all MidCOM DB layer objects, you should not use the get_by*
 * operations directly, instead, you have to use the constructor's $id parameter.
 * 
 * Also, all QueryBuilder operations need to be done by the factory class 
 * obtainable through the statically callable new_query_builder() DBA methods.
 * 
 * This class uses a auto-generated base class provided by midcom_services_dbclassloader.
 * 
 * @package midcom.baseclasses
 * @see midcom_services_dbclassloader
 */
class midcom_baseclasses_database_eventmember extends __midcom_baseclasses_database_eventmember
{
    function midcom_baseclasses_database_eventmember($id = null)
    {
        parent::__midcom_baseclasses_database_eventmember($id);
    }

    /**
     * Returns the Parent of the Eventmmeber. This is the event it is assinged to.
     * 
     * @return MidgardObject Parent object or NULL if there is none.
     */
    function get_parent_guid_uncached()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if ($this->eid == 0)
        {
            debug_pop();
            return null;
        }
        
        $parent = new midcom_baseclasses_database_event($this->eid);
        if (! $parent)
        {
        	debug_add("Could not load Event ID {$this->eid} from the database, aborting.", 
                MIDCOM_LOG_INFO);
        	debug_pop();
            return null;
        }
        
        debug_pop();
        return $parent->guid;
    }
}

?>