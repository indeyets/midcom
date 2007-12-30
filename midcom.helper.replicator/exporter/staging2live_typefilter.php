<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/** @ignore */
if (!class_exists('midcom_helper_replicator_exporter_staging2live'))
{
    require_once('staging2live.php');
}

/**
 * Staging/Live exporter with additional type checking
 *
 * @see midcom_helper_replicator_exporter_staging2live
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_exporter_staging2live_typefilter extends midcom_helper_replicator_exporter_staging2live
{

    /**
     * array of $object->type values to allow replication for
     *
     * @todo make configurable
     */
    var $pass_types = array
    (
        0,
    );

    /**
     * array of classes for which to check the type for
     *
     * @todo make configurable
     */
    var $check_types_for = array
    (
        'midgard_article',
    );

    function midcom_helper_replicator_exporter_staging2live_typefilter($subscription)
    {
        parent::midcom_helper_replicator_exporter_staging2live($subscription);
    }

    function is_exportable(&$object, $check_exported = true)
    {
        $this->_check_type($object);
        return parent::is_exportable($object, $check_exported);
    }

    function _check_type(&$object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Called for {$object->guid}");
        $GLOBALS['midcom_helper_replicator_logger']->log_object($object, "_check_type called");

        /* Objects to check type for */
        $type_check_continue = false;
        foreach ($this->check_types_for as $class)
        {
            if (is_a($object, $class))
            {
                $type_check_continue = true;
                break;
            }
        }
        if (!$type_check_continue)
        {
            $object_class = get_class($object);
            $msg = "Not checking type for class {$object_class}";
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, $msg);
            debug_add($msg);
            debug_pop();
            return;
        }

        if (!in_array($object->type, $this->pass_types))
        {
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, "Type {$object->type} is not in \$this->pass_types list, skipping");
            $this->exportability[$object->guid] = false;
        }

        debug_pop();
    }
}

?>