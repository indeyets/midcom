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
class midcom_helper_replicator_exporter extends midcom_baseclasses_components_purecode
{
    /**
     * The subscription object the transporter has been instantiated for
     *
     * @var midcom_helper_replication_subscription_dba
     * @access protected
     */
    var $subscription;

    var $exportability = array();
    
    var $handled_dependencies = array();

    var $_serialize_rewrite_to_delete = array();

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     *
     * @param midcom_helper_replication_subscription_dba $subscription Subscription
     */
    function midcom_helper_replicator_exporter($subscription)
    {
         $this->_component = 'midcom.helper.replicator';
         
         $this->subscription = $subscription;
         
         parent::midcom_baseclasses_components_purecode();
    }
    
    /**
     * This is a static factory method which lets you dynamically create exporter instances.
     * It takes care of loading the required class files. The returned instances will be created
     * but not initialized.
     *
     * On any error (class not found etc.) the factory method will call generate_error.
     *
     * <b>This function must be called statically.</b>
     *
     * @param midcom_helper_replication_subscription_dba $subscription Subscription
     * @return midcom_helper_replicator_exporter A reference to the newly created exporter instance.
     * @static
     */
    function &create($subscription)
    {
        $type = $subscription->exporter;
        $filename = MIDCOM_ROOT . "/midcom/helper/replicator/exporter/{$type}.php";
        
        if (!file_exists($filename))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Requested exporter file {$type} is not installed.");
            // This will exit.
        }
        require_once($filename);

        $classname = "midcom_helper_replicator_exporter_{$type}";        
        if (!class_exists($classname))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Requested exporter class {$type} is not installed.");
            // This will exit.
        }
        
        /**
         * Php 4.4.1 does not allow you to return a reference to an expression.
         * http://www.php.net/release_4_4_0.php
         */
        $class = new $classname($subscription);
        return $class;
    }

    /**
     * Serialize the privileges assigned to given object
     *
     * Operates directly on Midgard QB and skips all other checks too
     */
    function serialize_privileges(&$object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $serializations = array();
        $qb = new MidgardQueryBuilder('midcom_core_privilege_db');
        $qb->add_constraint('objectguid', '=', $object->guid);
        // Privilege deletes (or changes) do not go through the DBA object, thus do not get exported through the watchers
        if (method_exists($qb, 'include_deleted'))
        {
            $qb->include_deleted();
        }
        $privileges = $qb->execute();
        if ($privileges === false)
        {
            // QB error
            debug_add("Error when listing privileges to object {$object->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach ($privileges as $privilege)
        {
            $privilege_serialized = midcom_helper_replicator_serialize($privilege);
            if ($privilege_serialized === false)
            {
                unset($privileges, $privilege, $qb);
                debug_add("midcom_helper_replicator_serialize returned false for privilege {$privilege->guid}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $GLOBALS['midcom_helper_replicator_logger']->log_object($privilege, 'Exported');
            $serializations[$privilege->guid] = $privilege_serialized;
            unset($privilege_serialized);
        }
        unset($privileges, $privilege, $qb);
        
        debug_pop();
        return $serializations;
    }

    /**
     * Serialize attachment object
     *
     * @param midgard_attachment $object The attachment object to serialize
     * @return array Array of exported attachments metadata and blob
     */
    function serialize_attachment(&$attachment)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called for attachement {$attachment->guid}");
        $serializations = array();

        // Serialize metadata
        $attachment_serialized = midcom_helper_replicator_serialize($attachment);
        if ($attachment_serialized === false)
        {
            debug_add("midcom_helper_replicator_serialize returned false for attachment {$attachment->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Serialize blob (TODO: Check if we wish to do so)
        $attachment_blob_serialized = midcom_helper_replicator_serialize_blob($attachment);
        if ($attachment_blob_serialized === false)
        {
            unset($attachment_serialized);
            debug_add("midcom_helper_replicator_serialize_blob returned false for attachment {$attachment->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        // Check that we can export parameters of the attachment as well before adding it to the serialized list
        $attachment_parameters = $this->serialize_parameters($attachment);
        if ($attachment_parameters === false)
        {
            unset($attachment_serialized, $attachment_blob_serialized);
            debug_add("this->serialize_parameters returned false for attachment {$attachment->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // Check that we can export privileges of the attachment as well before adding it to the serialized list
        $attachment_privileges = $this->serialize_privileges($attachment);
        if ($attachment_privileges === false)
        {
            unset($attachment_serialized, $attachment_blob_serialized);
            debug_add("this->serialize_privileges returned false for attachment {$attachment->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        $serializations[$attachment->guid . '_metadata'] = $attachment_serialized;
        $serializations[$attachment->guid . '_blob'] = $attachment_blob_serialized;
        $serializations = array_merge($serializations, $attachment_parameters, $attachment_privileges);
        unset($attachment_serialized, $attachment_blob_serialized, $attachment_parameters, $attachment_privileges);

        debug_pop();
        return $serializations;
    }

    /**
     * Serialize attachments of an object
     *
     * @param midgard_object $object The Object to export attachments of
     * @return array Array of exported attachments as XML indexed by GUID
     */
    function serialize_attachments(&$object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called for object {$object->guid}");
        $serializations = array();
        $qb = new midgard_query_builder('midgard_attachment');
        $qb->add_constraint('parentguid', '=', $object->guid);
        /* PONDER: do we need this ? in theory the deletes do trigger DBA watch
        if (method_exists($qb, 'include_deleted'))
        {
            $qb->include_deleted();
        }
        */
        $attachments = $qb->execute();
        if ($attachments === false)
        {
            // QB error
            debug_add("Error when listing attachments to object {$object->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach ($attachments as $attachment)
        {
            if (!$this->is_exportable($attachment))
            {
                debug_add("Attachment {$attachment->guid} is not exportable", MIDCOM_LOG_INFO);
                continue;
            }        
        
            $attachment_serialized = $this->serialize_attachment($attachment);
            if ($attachment_serialized === false)
            {
                unset($attachments, $attachment, $qb);
                debug_add("this->serialize_attachment returned false for attachment {$attachment->guid}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $GLOBALS['midcom_helper_replicator_logger']->log_object($attachment, 'Exported');
            $serializations = array_merge($serializations, $attachment_serialized);
            unset($attachment_serialized);
        }
        unset($attachments, $attachment, $qb);

        debug_pop();
        return $serializations;
    }

    /**
     * Serialize parameters of an object
     *
     * @param midgard_object $object The Object to export parameters of
     * @return array Array of exported parameters as XML indexed by GUID
     */
    function serialize_parameters(&$object)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("called for object {$object->guid}");
        $serializations = array();
        $qb = new midgard_query_builder('midgard_parameter');
        $qb->add_constraint('parentguid', '=', $object->guid);
        // Parameter deletes (nor changes) do not go through the DBA object, thus do not get exported through the watchers
        if (method_exists($qb, 'include_deleted'))
        {
            $qb->include_deleted();
        }
        $parameters = $qb->execute();
        if ($parameters === false)
        {
            // QB error
            debug_add("Error when listing parameters to object {$object->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach ($parameters as $parameter)
        {
            if (!$this->is_exportable($parameter))
            {
                debug_add("Parameter {$parameter->guid} is not exportable", MIDCOM_LOG_INFO);
                continue;
            }
            
            $parameter_serialized = midcom_helper_replicator_serialize($parameter);
            if ($parameter_serialized === false)
            {
                unset($parameters, $parameter, $qb);
                debug_add("midcom_helper_replicator_serialize returned false for parameter {$parameter->guid}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $GLOBALS['midcom_helper_replicator_logger']->log_object($parameter, 'Exported');
            $serializations[$parameter->guid] = $parameter_serialized;
            unset($parameter_serialized);
        }
        unset($parameters, $parameter, $qb);

        debug_pop();
        return $serializations;
    }

    /**
     * Serialize an object
     *
     * This will also serialize the attachments and parameters of the object.
     *
     * @param midgard_object $object The Object to export parameters of
     * @return array Array of exported objects as XML indexed by GUID
     */
    function serialize_object(&$object, $skip_children = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $class = get_class($object);
        debug_add("called for {$class} {$object->guid}");
        $serializations = array();
        $GLOBALS['midcom_helper_replicator_logger']->push_prefix('exporter');     
        
        // Special case of midgard_attachment (and classes extending it)
        if (is_a($object, 'midgard_attachment'))
        {
            debug_add('object is attachment, passing control to serialize_attachment()', MIDCOM_LOG_INFO);
            debug_pop();
            return $this->serialize_attachment($object);
        }
        
        if (   version_compare(mgd_version(), '1.8.2', '>=')
            && !$skip_children)
        {
            // TODO: refator to separate method(s)
            // Reflect the object to handle any linked fields
            $reflector = new midgard_reflection_property(get_class($object));
            foreach (get_object_vars($object) as $property => $value)
            {
                if (empty($value))
                {
                    // We don't need to deal with empty values
                    continue;
                }
                if ($reflector->is_link($property)) 
                {
                    $linked_class = $reflector->get_link_name($property);
                    
                    if (!array_key_exists($linked_class, $this->handled_dependencies))
                    {
                        $this->handled_dependencies[$linked_class] = array();
                    }
                    
                    if (array_key_exists($value, $this->handled_dependencies[$linked_class]))
                    {
                        // Skip this property, we've already exported this dependency
                        continue;
                    }
                    
                    // TODO: use midcom dbfactory reflection to get the correct classname right away to avoid the convert below
                    $linked_object = new $linked_class($value);
                    if (   is_object($linked_object)
                        && $linked_object->guid)
                    {
                        $linked_dba_object = $_MIDCOM->dbfactory->convert_midgard_to_midcom($linked_object);
                        if (   is_object($linked_dba_object)
                            && isset($linked_dba_object->guid)
                            && $linked_dba_object->guid)
                        {
                            $linked_serialization = $this->serialize_object($linked_dba_object, true);
                            $serializations = array_merge($serializations, $linked_serialization);
                            unset($linked_serialization);
                            $this->handled_dependencies[$linked_class][$value] = true;
                        }
                    }
                }
            }
        }
        
        // Export object itself
        $object_serialized = midcom_helper_replicator_serialize($object);
        if ($object_serialized === false)
        {
             $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
            debug_add("midcom_helper_replicator_serialize returned false for object {$object->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        if (   isset($this->_serialize_rewrite_to_delete[$object->guid])
            && $this->_serialize_rewrite_to_delete[$object->guid])
        {
            // TODO: Make less simplistic (though this should be accurate enough)
            // NOTE: If serialization format changes (the slightest bit) this must be changed as well
            $object_serialized = str_replace("\n      <deleted>0</deleted>\n", "\n      <deleted>1</deleted>\n", $object_serialized);
            $object_serialized = str_replace(array(' action="updated" ', ' action="created" '), array(' action="deleted" ', ' action="deleted" '), $object_serialized);
        }
        $serializations[$object->guid] = $object_serialized;
        $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Exported');
        unset($object_serialized);

        if (!$skip_children)
        {
            // Then object's parameters
            $object_parameters = $this->serialize_parameters($object);
            if ($object_parameters === false)
            {
                 $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
                debug_add("this->serialize_parameters returned false for object {$object->guid}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $serializations = array_merge($serializations, $object_parameters);
            unset($object_parameters);
    
            // And lastly object's attachments
            $object_attachments = $this->serialize_attachments($object);
            if ($object_attachments === false)
            {
                $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
                debug_add("this->serialize_attachments returned false for object {$object->guid}", MIDCOM_LOG_ERROR);
                debug_pop();
                return false;
            }
            $serializations = array_merge($serializations, $object_attachments);
            unset($object_attachments);
        }
        // Always serialize object's privileges
        $object_privileges = $this->serialize_privileges($object);
        if ($object_privileges === false)
        {
            $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
            debug_add("this->serialize_privileges returned false for object {$object->guid}", MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $serializations = array_merge($serializations, $object_privileges);
        unset($object_privileges);

        $this->process_filters($serializations);
        $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
        debug_pop();
        return $serializations;
    }

    /**
     * This is the checkpoint of the exporter. This should be overridden in subclasses for more
     * contextual handling of dependencies.
     *
     * @param midgard_object $object The Object to export parameters of
     * @return boolean Whether the object may be exported with this exporter
     */
    function is_exportable(&$object)
    {
        if (isset($this->exportability[$object->guid]))
        {
            // expotability is set, return early
            return $this->exportability[$object->guid];
        }

        // Basic export checks (SG limits, limited objects)
        return $this->is_exportable_baseline($object);
    }

    function is_exportable_baseline(&$object)
    {
        switch (true)
        {
            // Never replicate login sessions
            case (is_a($object, 'midcom_core_login_session_db')):
            // Never replicate across SG borders
            case ($object->sitegroup != $_MIDGARD['sitegroup']):
                $this->exportability[$object->guid] = false;
                break;
            // Replicate everything else by default
            default:
                $this->exportability[$object->guid] = true;
                break;
        }

        return $this->exportability[$object->guid];
    }

    /**
     * This is the main entry point of the exporter. This should be overridden in subclasses for more
     * contextual handling of dependencies.
     *
     * @param midgard_object $object The Object to export parameters of
     * @return array Array of exported objects as XML indexed by GUID
     */
    function serialize(&$object)
    {
        if (!$this->is_exportable($object))
        {
            $GLOBALS['midcom_helper_replicator_logger']->push_prefix('exporter');
            $GLOBALS['midcom_helper_replicator_logger']->log_object($object, 'Is not exportable');
            $GLOBALS['midcom_helper_replicator_logger']->pop_prefix();
            return array();
        }
    
        $serializations = $this->serialize_object($object);
        if ($serializations === false)
        {
            // TODO: Error reporting
            return array();
        }

        return $serializations;
    }

    function filter_preg_replace(&$serializations, &$pluginargs)
    {
        $useargs = $this->_filter_xxx_replace_parseargs($pluginargs);
        $serializations = preg_replace($useargs['search'], $useargs['replace'], $serializations);
    }

    function filter_str_replace(&$serializations, &$pluginargs)
    {
        $useargs = $this->_filter_xxx_replace_parseargs($pluginargs);
        $serializations = str_replace($useargs['search'], $useargs['replace'], $serializations);
    }

    function _filter_xxx_replace_parseargs(&$pluginargs)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $ret['search'] = array();
        $ret['replace'] = array();
        if (!is_array($pluginargs))
        {
            // In fact this should never happen since the spec so
            debug_add("Subscription #{$this->subscription->id} ({$this->subscription->title}) xxx_replace callback options is not array", MIDCOM_LOG_ERROR);
            debug_pop();
            return $ret;
        }
        foreach ($pluginargs as $k => $srarr)
        {
            if (!isset($srarr['search']))
            {
                debug_add("Subscription #{$this->subscription->id} ({$this->subscription->title}) xxx_replace callback options key {$k} is missing 'search'", MIDCOM_LOG_WARN);
                $ret['search'][] = '';
            }
            else
            {
                $ret['search'][] = $srarr['search'];
            }
            if (!isset($srarr['replace']))
            {
                debug_add("Subscription #{$this->subscription->id} ({$this->subscription->title}) xxx_replace callback options key {$k} is missing 'replace'", MIDCOM_LOG_WARN);
                $ret['replace'][] = '';
            }
            else
            {
                $ret['replace'][] = $srarr['replace'];
            }
        }
        //debug_print_r('returning:', $ret);
        debug_pop();
        return $ret;
    }

    /**
     * This runs the given array of serializations through filters defined for
     * the subscription
     */
    function process_filters(&$serializations)
    {
        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Called for subscription #{$this->subscription->id} ({$this->subscription->title})");
        debug_pop();
        */
        if (empty($this->subscription->filters))
        {
            /*
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("subscription #{$this->subscription->id} ({$this->subscription->title}) has no filters");
            debug_pop();
            */
            // no filters
            return true;
        }
        foreach ($this->subscription->filters as $filterplugin => $pluginargs)
        {
            if (!is_array($pluginargs))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Plugin subscription #{$this->subscription->id} ({$this->subscription->title}) plugin {$filterplugin} arguments are not an array", MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }
            $methodname = "filter_{$filterplugin}";
            if (is_callable(array($this, $methodname)))
            {
                // Built-in plugin
                $this->$methodname($serializations, $pluginargs);
            }
            else
            {
                // TODO: Plugin loading mechanism
                // $plugin_class->process($serializations, $pluginargs, $this->subscription);
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Plugin {$filterplugin} is not known (subscription #{$this->subscription->id}:{$this->subscription->title}), skipping", MIDCOM_LOG_WARN);
                debug_pop();
            }
        }
    }
}
?>
