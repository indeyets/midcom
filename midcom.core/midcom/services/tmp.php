<?php
/**
 * @package midcom.services
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:tmp.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Temporary data management service.
 *
 * Currently the class supports management of temporary MidCOM objects.
 * One example where they are used is the creation mode wrapper of DM2.
 * No sessioning is done on the side of this class, you need to do that
 * yourself.
 *
 * Temporary objects are identified by their object ID, they are not
 * safely replicateable. A MidCOM CRON handler will delete all temporary
 * objects which have not been accessed for longer then one hour. Nevertheless
 * you should explicitly delete temporary objects you do no longer need using
 * the default delete call.
 *
 * This service is available as $_MIDCOM->tmp.
 *
 * <b>Temporary object privileges</b>
 *
 * All temporary objects have no privilege restrictions whatsoever. Everybody
 * has full control (that is, midgard:owner privileges) on the temporary object.
 * Newly created temporary objects have all their privileges reset, so that
 * applications can store privileges on temporary object which will be transferred
 * using the move_extensions_to_object() call on the temporary object (see there).
 *
 * @package midcom.services
 */
class midcom_services_tmp extends midcom_baseclasses_core_object
{
    /**
     * Internal status variable, set to true if the temporary object class
     * has already been loaded.
     *
     * @access private
     * @var bool
     */
    var $_tmp_object_class_loaded = false;

    /**
     * Simple constructor, calls base class.
     */
    function midcom_services_tmp()
    {
        parent::midcom_baseclasses_core_object();
    }

    /**
     * Internal helper function, ensures that the temporary object class is loaded.
     * If it has already been loaded, the call is ignored silently.
     *
     * @access protected
     */
    function _load_tmp_object_class()
    {
        if (! $this->_tmp_object_class_loaded)
        {
            $_MIDCOM->dbclassloader->load_classes('midcom', 'core_classes.inc');
            require_once(MIDCOM_ROOT . '/midcom/core/temporary_object.php');
            $this->_tmp_object_class_loaded = true;
        }
    }

    /**
     * This class creates a new temporary object for use with the application.
     * The id member of the object is used in the future to reference it in
     * request and release operations. The GUID of the object should not be used
     * for further references.
     *
     * In case the temporary object cannot be created, generate_error is called.
     *
     * All existing privileges (created by the DBA core) will be dropped, so that
     * privileges can be created at will by the application (f.x. using DM(2)
     * privilege types). Since EVERYONE owns all temporary objects using magic
     * default privileges, full access is ensured.
     *
     * @return midcom_core_temporary_object The newly created object.
     */
    function create_object()
    {
        $this->_load_tmp_object_class();
        $_MIDCOM->auth->require_user_do('midgard:create', null, 'midcom_core_temporary_object');

        $tmp = new midcom_core_temporary_object();
        if (! $tmp->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('Tried to create this object:', $tmp);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new temporary object, last Midgard error was: ' . mgd_errstr());
            // This will exit.
        }

        $tmp->unset_all_privileges();

        return $tmp;
    }

    /**
     * Loads the temporary object identified by the argument from the DB, verifies
     * ownership (using the midgard:owner privilege) and returns the instance.
     *
     * The object timestamp is updated implicitly by the temporary object class.
     *
     * Objects which have exceeded their lifetime will no longer be returned for
     * security reasons.
     *
     * @param int $id The temporary object ID to load.
     * @return midcom_core_temporary_object The accociated object or NULL in case that it
     *     is unavailable.
     */
    function request_object($id)
    {
        $this->_load_tmp_object_class();

        if (! $id)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Invalid argument, may not evaluate to false", MIDCOM_LOG_INFO);
            debug_print_r('Got this argument:', $id);
            debug_pop();
            return null;
        }

        $tmp = new midcom_core_temporary_object($id);
        if (   ! $tmp
            || ! $_MIDCOM->auth->can_do('midgard:owner', $tmp))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The current user does not have owner privileges on the temporary object {$id}, denying access.",
                MIDCOM_LOG_INFO);
            debug_print_r('Got this object:', $tmp);
            debug_pop();
            return null;
        }

        // Check if the object has timeouted already.
        $timeout = time() - $GLOBALS['midcom_config']['midcom_temporary_resource_timeout'];
        if ($tmp->timestamp < $timeout)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The temporary object {$id}  has exceeded its maximum lifetime, rejecting access and dropping it",
                MIDCOM_LOG_INFO);
            debug_print_r("Object was:", $tmp);
            debug_pop();
            $tmp->delete();
            return null;
        }

        // Update the object timestamp
        $tmp->update();

        return $tmp;
    }

}

?>