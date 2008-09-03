<?php
/**
* @package midcom.helper.replicator
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * This class handles mirroring any content to the subscriber
 *
 * In addition, it tries to be smart about different components so that
 * their main dependencies (like root events or index articles) are
 * exported as well.
 *
 * @package midcom.helper.replicator
 */
class midcom_helper_replicator_exporter_mirror extends midcom_helper_replicator_exporter
{
    var $already_serialized = array();

    function midcom_helper_replicator_exporter_mirror($subscription)
    {
        parent::__construct($subscription);
    }

    /**
     * @return boolean Whether the object may be exported based on its metadata
     */
    function is_exportable_by_metadata(&$object)
    {
        //$GLOBALS['midcom_helper_replicator_logger']->log_object($object, "is_exportable_by_metadata called");
        //TODO: Is there a more graceful way to do this ? (see also manager.php)
        $check_exported = true;
        if (   isset($GLOBALS['midcom_helper_replicator_exporter_retry_mode'])
            && !empty($GLOBALS['midcom_helper_replicator_exporter_retry_mode']))
        {
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, "Retry mode, not checking metadata->exported");
            return true;
        }
        if (   $object->metadata->deleted
            || (   isset($this->_serialize_rewrite_to_delete[$object->guid])
                && $this->_serialize_rewrite_to_delete[$object->guid]))
        {
            // Deleted objects are not refreshed for DBA watch and thus have their last replication status...
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, "Object is deleted, skipping further checks and returning true", MIDCOM_LOG_INFO);
            return true;
        }
        // FIXME: When MidCOM core stops messing with these timestamps just use strotime
        if (!is_numeric($object->metadata->exported))
        {
            if (!empty($object->metadata->exported))
            {
                $exported_unixtime = strtotime($object->metadata->exported);
            }
            else
            {
                $exported_unixtime = 0;
            }
        }
        else
        {
            $exported_unixtime = $object->metadata->exported;
        }
        if (!is_numeric($object->metadata->revised))
        {
            if (!empty($object->metadata->revised))
            {
                $revised_unixtime = strtotime($object->metadata->revised);
            }
            else
            {
                $revised_unixtime = 0;
            }
        }
        else
        {
            $revised_unixtime = $object->metadata->revised;
        }
        if ($exported_unixtime >= $revised_unixtime)
        {
            // This has been exported already
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, "has already been exported", MIDCOM_LOG_INFO);
            return false;
        }

        return true;
    }

    /**
     * This is the main entry point of the exporter. Since we're mirroring
     * all content this will always return true.
     *
     * @param midgard_object &$object The Object to export parameters of
     * @return boolean Whether the object may be exported with this exporter
     */
    function is_exportable(&$object)
    {
        // If we already know the state return early
        if (isset($this->exportability[$object->guid]))
        {
            return $this->exportability[$object->guid];
        }

        // Otherwise start checking...
        // Check baseline checks first
        if (!$this->is_exportable_baseline($object))
        {
            return false;
        }

        // CAVEAT: may cause issues with multiple subscriptions
        $this->exportability[$object->guid] = $this->is_exportable_by_metadata($object);

        return $this->exportability[$object->guid];
    }
    
    /**
     * Look for articles with a particular name to export
     *
     * @access private
     * @return array Array of exported objects as XML indexed by GUID
     */
    function _serialize_article_by_name($node, $name)
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $node->id);
        $qb->add_constraint('name', '=', $name);
        $articles = $qb->execute();
        if (count($articles) == 0)
        {
            return array();
        }
        
        return $this->serialize($articles[0]);
    }

    /**
     * This method handles component-based dependencies of different
     *
     * @param midcom_db_topic $node The node to export dependencies of
     * @return array Array of exported objects as XML indexed by GUID
     */
    function serialize_component_dependencies(&$node)
    {
        // FIXME: components interface class should have a method to return dependencies
        switch ($node->component)
        {
            case 'net.nehmer.static':
                return $this->_serialize_article_by_name($node, 'index');
                
            case 'net.nemein.calendar':
                $root_event = $node->parameter('net.nemein.calendar', 'root_event');
                if ($root_event)
                {
                    $event = new midcom_db_event($root_event);
                    if ($event)
                    {
                        return $this->serialize($event);
                    }
                }
                return array();
            
            default:
                return array();
        }
    }
    
    /**
     * Export some children of the object
     *
     * @param midgard_object &$object The Object to export parameters of
     * @return array Array of exported objects as XML indexed by GUID
     */
    function serialize_children(&$object)
    {
        $serializations = array();
        
        // In case of a topic we have to check for possible extra dependencies
        if (is_a($object, 'midgard_topic'))
        {
            $dependency_serializations = $this->serialize_component_dependencies(&$object);
            $serializations = array_merge($serializations, $dependency_serializations);
            unset($dependency_serializations);
        }
        
        return $serializations;
    }
    
    /**
     * Serialize an object
     *
     * This will also serialize the attachments and parameters of the object
     * and walk the object's parent tree as needed.
     *
     * @param midgard_object &$object The Object to export parameters of
     * @return array Array of exported objects as XML indexed by GUID
     */
    function serialize_object(&$object, $skip_children = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called for object {$object->guid}");
        if (array_key_exists($object->guid, $this->already_serialized))
        {
            debug_add("Object {$object->guid} already serialized", MIDCOM_LOG_INFO);
            debug_pop();
            return array();
        }
        
        if (!$this->is_exportable($object))
        {
            debug_add("Object {$object->guid} is not exportable", MIDCOM_LOG_INFO);
            debug_pop();
            return array();
        }
        
        $serializations = array();
        
        // Traverse parent tree as well
        $parent = $object->get_parent();
        if (is_object($parent))
        {
            debug_add("calling this->serialize_object for parent object {$parent->guid}");
            // FIXME: Skipping children is harmful in staging2live situations
            $parent_serialization = $this->serialize_object($parent, true);
            if ($parent_serialization === false)
            {
                debug_add("this->serialize_object returned false for (parent) object {$parent->guid}", MIDCOM_LOG_WARN);
                // What to do ?
            }
            else
            {
                $serializations = array_merge($serializations, $parent_serialization);
            }
            unset($parent_serialization);
        }
        
        $object_serialization = parent::serialize_object($object, $skip_children);
        if ($object_serialization === false)
        {
            debug_add("parent::serialize_object failed, meaning we could not serialize this object, abort", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $serializations = array_merge($serializations, $object_serialization);
        unset($object_serialization);
        
        $this->already_serialized[$object->guid] = true;
        
        if (!$skip_children)
        {
            // Traverse children as needed
            $child_serializations = $this->serialize_children($object);
            if ($child_serializations === false)
            {
                debug_add("this->serialize_children returned false for object {$object->guid}", MIDCOM_LOG_WARN);
                // What to do ?
            }
            else
            {
                $serializations = array_merge($serializations, $child_serializations);
            }
            unset($child_serializations);
        }
        
        $this->process_filters($serializations);
        debug_pop();
        return $serializations;
    }

    /**
     * This is the main entry point of the exporter. It exports not only the
     * object itself, but also its parent hierarchy.
     *
     * @param midgard_object &$object The Object to export parameters of
     * @return array Array of exported objects as XML indexed by GUID
     */
    function serialize(&$object)
    {
        return $this->serialize_object($object);
    }
}
?>