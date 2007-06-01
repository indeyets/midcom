<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_logger extends midcom_debug 
{
    function midcom_helper_replicator_logger($filename)
    {
        parent::midcom_debug($filename);
    }

    function _resolve_object_title($object)
    {
        $vars = get_object_vars($object);
        
        if (is_a($object, 'midgard_parameter'))
        {
            return "{$object->domain}/{$object->name}";
        }
        elseif (is_a($object, 'midgard_topic'))
        {
            return $object->extra;
        }
        elseif (   array_key_exists('title', $vars)
            && !empty($object->title))
        {
            return $object->title;
        } 
        elseif (   array_key_exists('name', $vars)
                && !empty($object->name)) 
        {
            return $object->name;
        }
        else
        {
            return "#{$object->id}";
        }
    }  
    
    function log_object($object, $action, $loglevel = MIDCOM_LOG_DEBUG)
    {
        $message = $action;
        $message .= " {$object->guid}";
        $message .= ', ' . get_class($object);
        $message .= ' "' . $this->_resolve_object_title($object) . '"';
        
        $this->log($message, $loglevel);
    }
}
?>
