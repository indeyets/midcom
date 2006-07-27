<?php
/**
 * @package midcom.helper.datamanager2
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Datamanger 2 Data storage implementation: NULL storage object.
 *
 * This class is aimed to provide a dummy storage object, useable for stuff like mailback forms.
 * Usually, you won't save datamanagers running with this storage backend, accessing the types
 * values directly. The type's convert_to_storage helper functions can be a nice help if you need
 * string representations.
 *
 * Special care has to be taken when attachments are being used with this type. Since
 * $object is always null by default, types working on attachments must create a temporary
 * object before going on. This implementation does *not* work with such an object instance
 * for any data i/o operations except those invoked by the types directly on it. This
 * especially means, that any store operation will still fail on this implementation for
 * all regular types not working on $object directly.
 *
 * Also, there is no code whatsoever involved, which does the transition of a temporary
 * storage object through multiple requests. You need to set $object accordingly yourself.
 *
 * @package midcom.helper.datamanager2
 */
class midcom_helper_datamanager2_storage_null extends midcom_helper_datamanager2_storage
{
    /**
     * The defaults to use when loading the data from the "artificial" storage.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    /**
     * TODO
     *
     * @param midcom_helper_datamanager2_schema $schema The data schema to use for processing.
     * @param Array $defaults The defaults to use as "artificial" storage. This can be ommitted
     *     safely.
     */
    function midcom_helper_datamanager2_storage_null(&$schema, $defaults = Array())
    {
        parent::midcom_helper_datamanager2_storage($schema);
        $this->_defaults = $defaults;
    }

    /**
     * We don't store anything, this can be safely ignored therefore.
     */
    function _on_store_data($name, $data) {}

    /**
     * This returns the defaults set. An empty string is used for unset defaults.
     */
    function _on_load_data($name)
    {
        if (array_key_exists($name, $this->_defaults))
        {
            return $this->_defaults[$name];
        }
        else
        {
            return '';
        }
    }

    /**
     * We do as if we can store successfully at all times.
     */
    function _on_update_object()
    {
        return true;
    }
}

?>