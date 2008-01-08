<?php
/**
 * @package midcom.db
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:person.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @version $Id:person.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/** @ignore */
require(MIDCOM_ROOT . '/midcom/baseclasses/database/person.php');

/**
 * MidCOM Legacy Database Abstraction Layer
 *
 * This class encapsulates a classic MidgardPerson with its original features.
 *
 * <i>Preliminary Implementation:</i>
 *
 * Be aware that this implementation is incomplete, and grows on a is-needed basis.
 *
 * @package midcom.db
 */
class midcom_db_person extends midcom_baseclasses_database_person
{

    /**
     * The default constructor will create an empty object. Optionally, you can pass
     * an object ID or GUID to the object which will then initialize the object with
     * the corresponding DB instance.
     *
     * @param mixed $id A valid object ID or GUID, omit for an empty object.
     */
    function midcom_db_person($id = null)
    {
        parent::midcom_baseclasses_database_person($id);
    }

    /**
     * Overwrite the query builder getter with a version retrieving the right type.
     * We need a better solution here in DBA core actually, but it will be difficult to
     * do this as we cannot determine the current class in a polymorphic environment without
     * having a this (this call is static).
     * 
     * @static
     */
    function new_query_builder()
    {
        return $_MIDCOM->dbfactory->new_query_builder(__CLASS__);
    }

    /**
     * Adds a user to a given Midgard Group. Caller must ensure access permissions
     * are right.
     *
     * @param string $name The name of the group we should be added to.
     * @return bool Indicating success.
     *
     * @todo Check if user is already assigned to the group.
     */
    function add_to_group($name)
    {
        $group =& $_MIDCOM->auth->get_midgard_group_by_name($name,$this->sitegroup);
        if (! $group)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to add the person {$this->id} to group {$name}, the group does not exist.", MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        $storage = $group->get_storage();
        $member = new midcom_baseclasses_database_member();
        $member->uid = $this->id;
        $member->gid = $storage->id;
        if (! $member->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to add the person {$this->id} to group {$name}, object could not be created.", MIDCOM_LOG_WARN);
            debug_add('Last Midgard error was: ' . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_print_r('Tried to create this object:', $member);
            debug_pop();
            return false;
        }
        return true;
    }


}


?>