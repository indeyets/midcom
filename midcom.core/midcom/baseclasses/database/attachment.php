<?php
/**
 * @package midcom.baseclasses
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:attachment.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM level replacement for the Midgard Attachment record with framework support.
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
class midcom_baseclasses_database_attachment extends __midcom_baseclasses_database_attachment
{
    /**
     * Internal tracking state variable, holds the file handle of any open
     * attachment.
     *
     * @var resource
     * @access private
     */
    var $_open_handle = null;

    function midcom_baseclasses_database_attachment($id = null)
    {
        parent::__midcom_baseclasses_database_attachment($id);
    }

    /**
     * Returns the Parent of the Attachment, which is identified by the table/id combination
     * in the attachment record. The table in question is used to identify the object to
     * use. If multiple objects are registered for a given table, the first matching class
     * returned by the dbfactory is used (which is usually rather arbitary).
     *
     * @todo Not yet implemented.
     * @return MidgardObject Parent object.
     */
    function get_parent_guid_uncached()
    {
        $possible_parent_classes = $_MIDCOM->dbclassloader->get_classes_for_table($this->ptable);
        if (count($possible_parent_classes) == 0)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to retrieve the parent object for Attachment {$this->id}, parent table {$this->ptable} is unknown to the class loader.", MIDCOM_LOG_WARN);
            debug_pop();
            return null;
        }

        $classname = $possible_parent_classes[0]['midcom_class_name'];
        $_MIDCOM->dbclassloader->load_mgdschema_class_handler($classname);
        $parent = new $classname($this->pid);

        if (   ! $parent
            || $parent->id != $this->pid)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to retrieve the parent object for Attachment {$this->id}, could not retrieve parent object.", MIDCOM_LOG_WARN);
            debug_print_r('Retrieved object was:', $parent);
            debug_pop();
            return null;
        }

        return $parent->guid;
    }

    /**
     * Opens the attachment for file IO, the semantics match the original
     * mgd_open_attachment call. Returns a filehandle that can be used with the
     * usual PHP file functions if successful, the handle has to be closed with
     * the close() method when you no longer need it, don't let it fall over
     * the end of the script.
     *
     * <b>Important Note:</b> It is important to use the close() member function of
     * this class to close the file handle, not just fclose(). Otherwise, the upgrade
     * notification switches will fail.
     *
     * @param string $mode The mode which should be used to open the attachment, same as
     *     the mode parameter of the PHP fopen call. This defaults to write access (see
     *     mgd_open_attachmentl for details).
     * @return resource A file handle to the attachment if successful, false on failure.
     */
    function open()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->id)
        {
            debug_add('Cannot open a non-persistent attachment..', MIDCOM_LOG_WARN);
            debug_print_r('Object state:', $this);
            debug_pop();
            return false;
        }

        if ($this->_open_handle !== null)
        {
            debug_add("Warning, the Attachment {$this->id} already had an open file handle, we close it impliciltly.", MIDCOM_LOG_WARN);
            fclose($this->_open_handle);
            $this->_open_handle = null;
        }

        switch (func_num_args())
        {
            case 0:
                $mode = 'default';
                $this->_open_write_mode = true;
                $handle = mgd_open_attachment($this->id);
                break;

            case 1:
                $mode = func_get_arg(0);
                $this->_open_write_mode = ($mode{0} != 'r');
                $handle = mgd_open_attachment($this->id, $mode);
                break;

            default:
                trigger_error('midcom_baseclasses_database_attachment takes either zero or one arguments.', E_USER_ERROR);
                // This should exit.
        }

        if (! $handle)
        {
            debug_add("Failed to open attachment with mode {$mode}, last Midgard error was:" . mgd_errstr(), MIDCOM_LOG_WARN);
        }

        $this->_open_handle = $handle;

        debug_pop();
        return $handle;
    }

    /**
     * This function closes the open write handle obtained by the open() call again.
     * It is required to call this function instead of a simple fclose to ensure proper
     * upgrade notifications.
     */
    function close()
    {
        if ($this->_open_handle === null)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Tried to close non-open attachment {$this->id}", MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }

        fclose ($this->_open_handle);
        $this->_open_hanlde = null;

        // We need to update the attachment now, this cannot be done in the Midgard Core
        // at this time.
        if (! $this->update())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to update attachment {$this->id}", MIDCOM_LOG_WARN);
            debug_pop();
            return;
        }

        $object = $this->get_parent();
        if ($object !== null)
        {
            $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_UPDATE, $object);
        }
    }

    /**
     * Simple wrapper for mgd_stat_attachment.
     *
     * @return mixed Either a stat array as for mgd_stat_attachment or false on failure.
     */
    function stat()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! $this->id)
        {
            debug_add('Cannot open a non-persistent attachment..', MIDCOM_LOG_WARN);
            debug_print_r('Object state:', $this);
            debug_pop();
            return false;
        }

        debug_pop();
        return mgd_stat_attachment($this->id);
    }

    /**
     * Internal helper, computes an MD5 string which is used as an attachment location.
     * It should be random enough, even if the algorithm used does not match the one
     * Midgard uses. If the location already exists, it will iterate until an unused
     * location is found.
     *
     * @return string An unused attachment location.
     */
    function _create_attachment_location()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $location_in_use = true;
        $location = '';

        while ($location_in_use)
        {
            $base = $this->__table__;
            $base .= microtime();
            $base .= $this->id;
            $base .= $_SERVER['SERVER_NAME'];
            $base .= $_SERVER['REMOTE_ADDR'];
            $base .= $_SERVER['REMOTE_PORT'];
            $name = strtolower(md5($base));
            $location = strtoupper(substr($name,0,1) . '/' . substr($name,1,1) . '/') . $name;

            // Check uniqueness
            $qb = midcom_baseclasses_database_attachment::new_query_builder();
            $qb->add_constraint('location', '=', $location);
            if ($this->id)
            {
                // Add this one if and only if we are persistent already.
                $qb->add_constraint('id', '<>', $this->id);
            }
            $result = $qb->execute();

            if (count($result) == 0)
            {
                $location_in_use = false;
            }
        }

        debug_add("Created this location: {$location}");
        debug_pop();
        return $location;
    }

    /**
     * Simple creation event handler which fills out the location field if it
     * is still empty with a location generated by _create_attachemtn_location().
     *
     * @return bool True if creation may commence.
     */
    function _on_creating()
    {
        if (! parent::_on_creating())
        {
            return false;
        }
        $this->location = $this->_create_attachment_location();
        return true;
    }

    /**
     * Created callback, triggers watches on the parent(!) object.
     */
    function _on_created()
    {
        parent::_on_created();
        $object = $this->get_parent();
        if ($object !== null)
        {
            $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_UPDATE, $object);
        }
    }

    /**
     * Updated callback, triggers watches on the parent(!) object.
     */
    function _on_updated()
    {
        parent::_on_updated();
        $object = $this->get_parent();
        if ($object !== null)
        {
            $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_UPDATE, $object);
        }
    }

    /**
     * Deleted callback, triggers watches on the parent(!) object.
     */
    function _on_deleted()
    {
        parent::_on_deleted();
        $object = $this->get_parent();
        if ($object !== null)
        {
            $_MIDCOM->componentloader->trigger_watches(MIDCOM_OPERATION_DBA_UPDATE, $object);
        }
    }

    /**
     * Updates the contents of the attachments with the contents of the resource identified
     * by the filehandle passed.
     *
     * @param resource $source The handle to read from.
     * @return bool Indicating success.
     */
    function copy_from_handle($source)
    {
        $dest = $this->open();
        if (! $dest)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not open attachment for wrtiting, last Midgard error was: ' . mgd_errstr(), MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        while (! feof($source))
        {
            fwrite($dest, fread($source, 100000));
        }

        $this->close();
        return true;
    }

    /**
     * Updates the contents of the attachments with the contents of the file specified.
     * This is a wrapper for copy_from_handle.
     *
     * @param string $filename The file to read.
     * @return bool Indicating success.
     */
    function copy_from_file($filename)
    {
        $source = @fopen ($filename, 'r');
        if (! $source)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('Could not open file for reading.' . mgd_errstr(), MIDCOM_LOG_WARN);
            if (isset($php_errorstr))
            {
                debug_add("Last PHP error was: {$php_errorstr}", MIDCOM_LOG_WARN);
            }
            debug_pop();
            return false;
        }
        $result = $this->copy_from_handle($source);
        fclose($source);
        return $result;
    }


}

?>