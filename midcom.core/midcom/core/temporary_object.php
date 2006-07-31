<?php
/**
 * @package midcom.db
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:temporary_object.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @version $Id:temporary_object.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM Temporary Database Object
 *
 * Controlled by the temporary object service, you should never create instances
 * of this type directly.
 *
 * @see midcom_services_tmp
 * @package midcom
 */
class midcom_core_temporary_object extends __midcom_core_temporary_object
{
    /**
     * The default constructor will create an empty object. Optionally, you can pass
     * an object ID or GUID to the object which will then initialize the object with
     * the corresponding DB instance.
     *
     * @param mixed $id A valid object ID or GUID, omit for an empty object.
     */
    function midcom_core_temporary_object($id = null)
    {
        parent::__midcom_core_temporary_object($id);
    }

    /**
     * These objects have no restrictions whatsoever directly assigned to them.
     * This allows you to assign further privileges to the temporary object during
     * data entry for example (and then copy these privileges to the live object).
     */
    function get_class_magic_default_privileges()
    {
        return Array
        (
            'EVERYONE' => Array
            (
                'midgard:owner' => MIDCOM_PRIVILEGE_ALLOW,
            ),
            'ANONYMOUS' => Array(),
            'USERS' => Array(),
        );
    }

    /**
     * Update the object timestamp.
     */
    function _on_creating()
    {
        $this->timestamp = time();
        return true;
    }

    /**
     * Update the object timestamp.
     */
    function _on_updating()
    {
        $this->timestamp = time();
        return true;
    }

    /**
     * Transfers all parametersm attachments and privileges on the current object to another
     * existing Midgard object. You need to have midgard:update, midgard:parameter,
     * midgard:privileges and midgard:attachments privileges on the target object,
     * which must be a persitant MidCOM DBA class instance. (For ease of use, it is recommended
     * to have midgard:owner rights for the target object, which includes the above
     * privileges).
     *
     * <b>Important notes:</b>
     *
     * All records in questionwill just be moved, not copied!
     * Also, there will be <i>no</i> integrity checking in terms of already existing
     * parameters etc. This feature is mainly geared towards preparing a freshly
     * created final object with the data accociated with this temporary object.
     *
     * Any invalid object / missing privlege will trigger a generate_error.
     *
     * @param midcom_dba_object $object The object to transfer the extensions to.
     */
    function move_extensions_to_object($object)
    {
        if (! $_MIDCOM->dbclassloader->is_midcom_db_object($object))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The object passed is no valid for move_extensions_to_object.');
            // This will exit.
        }

        // Validate Privileges
        $object->require_do('midgard:update');
        $object->require_do('midgard:privileges');
        $object->require_do('midgard:parameters');
        $object->require_do('midgard:attachments');

        // Parameters, we handle them directly, without using the DBA API,
        // we cannot move parameters with DBA:
        $query = new MidgardQueryBuilder('midgard_parameter');
        $query->add_constraint('tablename', '=', $this->__table__);
        $query->add_constraint('oid', '=', $this->id);
        $result = @$query->execute();
        if ($result)
        {
            foreach ($result as $parameter)
            {
                $parameter->tablename = $object->__table__;
                $parameter->oid = $object->id;
                $parameter->update();
            }
        }

        // Attachments are handled using the DBA API, we can move them there.
        $query = $this->get_attachment_qb();
        $result = $query->execute();
        if ($result)
        {
            foreach ($result as $attachment)
            {
                $attachment->ptable = $object->__table__;
                $attachment->pid = $object->id;
                $attachment->update();
            }
        }

        // Privileges are moved using the DBA API as well.
        $privileges = $this->get_privileges();
        if ($privileges)
        {
            foreach ($privileges as $privilege)
            {
                $privilege->set_object($object);
                $privilege->store();
            }
        }
    }
}
?>
