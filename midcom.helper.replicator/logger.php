<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

$_MIDCOM->load_library('midcom.helper.reflector');

/**
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_logger extends midcom_debug 
{
    function __construct($filename)
    {
        parent::__construct($filename);
        if (isset($GLOBALS['midcom_config']['replicator_log_level']))
        {
            $this->_loglevel = $GLOBALS['midcom_config']['replicator_log_level'];
        }
    }

    function log_object(&$object, $action, $loglevel = MIDCOM_LOG_DEBUG)
    {
        $ref = new midcom_helper_reflector($object);
        $message = $action;
        $message .= " {$object->guid}";
        $message .= ', ' . $ref->get_class_label();
        $message .= ' "' . $ref->get_object_label($object) . '"';
        unset($ref);
        
        $this->log($message, $loglevel);
    }
}
?>