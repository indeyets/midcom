<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:collector.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * MidCOM DBA level wrapper for the Midgard Collector.
 *
 * This class must be used instead anyplace within MidCOM instead of the real
 * midgard_collector object within the MidCOM Framework. This wrapper is
 * required for the correct operation of many MidCOM services.
 *
 * It essentially wraps the calls to midcom_helper__dbfactory::new_collector()
 * and midcom_helper__dbfactory::exec_collector().
 *
 * Normally you should never have to create an instance of this type direectly,
 * instead use the get_new_mc() method available in the MidCOM DBA API or the
 * midcom_helper__dbfactory::new_collector() method which is still available.
 *
 * If you have to do create the instance manually however, do not forget to call the
 * initialize() function after construction, or the creation callbacks will fail.
 *
 * <i>Developer's Note:</i>
 *
 * Due to the limitations of the Zend engine this class does not extend the
 * QueryBuilder but proxy to it.
 *
 * @package midcom
 * @todo Optimize the limit/offset implementation.
 * @todo Refactor the class to promote code reuse in the execution handlers.
 */
class midcom_core_collector extends midcom_baseclasses_core_object
{
    /**
     * This private helper holds the type that the application expects to retrieve
     * from this instance.
     *
     * @var string
     * @access private
     */
    var $_real_class;

    /**
     * The collector instance that is internally used.
     *
     * @var midgard_collector
     * @access private
     */
    var $_mc;

    /**
     * The number of records to return to the client at most.
     *
     * @var int
     * @access private
     */
    var $_limit = 0;

    /**
     * The offset of the first record the client wants to have available.
     *
     * @var int
     * @access private
     */
    var $_offset = 0;

    /**
     * This is an internal count which is incremented by one each time a constraint is added.
     * It is used to emit a warning if no constraints have been added to the QB during execution.
     *
     * @var int
     * @access private
     */
    var $_constraint_count = 0;

    /**
     * The number of records found by the last execute() run. This is -1 as long as no
     * query has been executed. This member is read-only.
     *
     * @var int
     */
    var $count = -1;

    /**
     * The number of objects for which access was denied.
     *
     * This is especially useful for reimplementations of functions like mgd_get_article_by_name
     * which must use the QB in the first place.
     *
     * @var int
     */
    var $denied = 0;

    /**
     * Set this element to true to hide all items which are currently invisible according
     * to the approval/scheduling settings made using Metadata. This must be set before executing
     * the query.
     *
     * Be aware, that this setting will currently not use the QB to filter the objects accordingly,
     * since there is no way yet to filter against parameters. This will mean some performance
     * impact.
     *
     * While on-site, this is enabled by default, in AIS it is disabled by default.
     */
    var $hide_invisible = false;

    /**
     * The constructor wraps the class resolution into the MidCOM DBA system.
     * Currently, Midgard requires the actual MgdSchema base classes to be used
     * when dealing with the QB, so we internally note the corresponding class
     * information to be able to do correct typecasting later.
     *
     * @param string $classname The classname which should be queried.
     */
    function midcom_core_collector($classname, $domain, $value)
    {
        static $_class_mapping_cache = Array();

        if (array_key_exists($classname, $_class_mapping_cache))
        {
            $baseclass = $_class_mapping_cache[$classname];
        }
        else
        {
            // Validate the class, we check for a single callback representativly only
            if (! in_array('_on_prepare_new_collector', get_class_methods($classname)))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Cannot create a midcom_core_collector instance for the type {$classname}: Does not seem to be a DBA class name.");
                // This will exit.
            }

            $parent = $classname;
            $baseclass = $classname;
            do
            {
                $baseclass = $parent;
                $parent = get_parent_class($baseclass);
            }
            while ($parent !== false);

            if (! class_exists($baseclass))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Cannot create a midcom_core_collector instance for the type {$baseclass}: Class not found.");
                // This will exit.
            }
            $_class_mapping_cache[$classname] = $baseclass;
        }

        $this->_mc = new midgard_collector($baseclass, $domain, $value);
        $this->_real_class = $classname;
        
        // MidCOM's collector always uses the GUID as the key for ACL purposes
        $this->_mc->set_key_property('guid');

        if (! array_key_exists("view_contentmgr", $GLOBALS))
        {
            $this->hide_invisible = true;
            
            if ($GLOBALS['midcom_config']['i18n_multilang_strict']
                && $_MIDCOM->i18n->get_midgard_language() != 0)
            {
                // FIXME: Re-enable this when it actually works
                // $this->_mc->set_lang($_MIDCOM->i18n->get_midgard_language());
            }
        }
    }


    /**
     * The initialization routin executes the _on_prepare_new_collector callback on the class.
     * This cannot be done in the constructor due to the reference to $this that is used.
     */
    function initialize()
    {
        call_user_func_array(array($this->_real_class, '_on_prepare_new_collector'), array(&$this));
    }


    /**
     * This function will execute the Querybuilder and call the appropriate callbacks from the
     * class it is associated to. This way, class authors have full control over what is actually
     * returned to the application.
     *
     * The calling sequence of all event handlers of the associated class is like this:
     *
     * 1. bool _on_prepare_exec_collector(&$this) is called before the actual query execution. Return false to
     *    abort the operation.
     * 2. The query is executed.
     * 3. void _on_process_query_result(&$result) is called after the successful execution of the query. You
     *    may remove any unwanted entries from the resultset at this point.
     *
     * If the execution of the query fails for some reason all available error information is logged
     * and a MIDCOM_ERRCRIT level error is triggered, halting execution.
     *
     * @param midgard_collector $mc An instance of the Query builder obtained by the new_collector
     *     function of this class.
     * @return Array The result of the collector or null on any error. Note, that empty resultsets
     *     will return an empty array.
     * @todo Implement proper count / Limit support.
     */
    function execute()
    {
        if (! call_user_func_array(array($this->_real_class, '_on_prepare_exec_collector'), array(&$this)))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('The _on_prepare_exec_collector callback returned false, so we abort now.');
            debug_pop();
            return null;
        }

        // Workaround until the QB does return empty arrays: All errors are empty resultsets and errors are ignored.
        $result = $this->_mc->execute();
        if (!$result)
        {
            // Workaround mode for now
            if (mgd_errno() != MGD_ERR_OK)
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r('Result was:', $result);
                debug_add('The collector failed to execute, aborting.', MIDCOM_LOG_ERROR);
                debug_add('Last Midgard error was: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
                if (isset($php_errormsg))
                {
                    debug_add("Error message was: {$php_errormsg}", MIDCOM_LOG_ERROR);
                }
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'The collector failed to execute, see the log file for more information.');
                // This will exit.
            }
        }
        
        return $result;
    }
    
    function list_keys_unchecked()
    {
        return $this->_mc->list_keys();
    }
    
    function list_keys()
    {
        $result = $this->_mc->list_keys();
        $newresult = array();
                
        if (!$result)
        {
            return $newresult;
        }

        // Workaround until the QB returns the correct type, refetch everything
        $classname = $this->_real_class;
        $limit = $this->_limit;
        $offset = $this->_offset;
        $skipped_objects = 0;
        $this->denied = 0;
        foreach ($result as $object_guid => $empty_copy)
        {
            if (   $this->_limit > 0
                && $limit == 0)
            {
                $skipped_objects++;
                continue;
            }

            if (!$_MIDCOM->auth->can_do_byguid('midgard:read', $object_guid, $classname))
            {
                debug_add("Failed to load result, read privilege on {$object_guid} not granted for the current user.", MIDCOM_LOG_INFO);
                $this->denied++;
                $skipped_objects++;
                continue;
            }

            // We need to skip this one, because we are outside the offset.
            if (   $this->_offset > 0
                && $offset > 0)
            {
                $offset--;
                continue;
            }

            // Check visibility
            if ($this->hide_invisible)
            {
                // TODO: Implement
            }

            $newresult[$object_guid] = array();

            if ($this->_limit > 0)
            {
                $limit--;
            }
        }

        call_user_func_array(array($this->_real_class, '_on_process_collector_result'), array(&$newresult));

        /* This must some QB copy-paste leftover
        // correct record count by the number of limit-skipped objects.
        $this->count = count($newresult) + $skipped_objects;
        */
        $this->count = count($newresult);

        debug_pop();
        return $newresult;
    }
    
    function get_subkey($key, $property)
    {
        if (!$_MIDCOM->auth->can_do_byguid('midgard:read', $key, $this->_real_class))
        {
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        return $this->_mc->get_subkey($key, $property);
    }
    
    function get($key)
    {
        if (!$_MIDCOM->auth->can_do_byguid('midgard:read', $key, $this->_real_class))
        {
            mgd_set_errno(MGD_ERR_ACCESS_DENIED);
            return false;
        }
        return $this->_mc->get($key);
    }
    
    function destroy()
    {
        return $this->_mc->destroy();
    }

    /**
     * Add a constraint to the collector.
     *
     * @param string $field The name of the MgdSchema property to query against.
     * @param string $operator The operator to use for the constraint, currently supported are
     *     &lt;, &lt;=, =, &lt;&gt;, &gt;=, &gt;, LIKE. LIKE uses the percent sign ('%') as a
     *     wildcard character.
     * @param mixed $value The value to compare against. It should be of the same type then the
     *     queried property.
     * @param bool Indicating success.
     */
    function add_constraint($field, $operator, $value)
    {
        // Add check against null values, Core QB is too stupid to get this right.
        if ($value === null)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'QueryBuilder: Cannot add constraints with null values.');
        }
        if (! $this->_mc->add_constraint($field, $operator, $value))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to exectue add_constraint.", MIDCOM_LOG_ERROR);
            debug_add("Class = '{$this->_real_class}, Field = '{$field}', Operator = '{$operator}'");
            debug_print_r('Value:', $value);
            debug_pop();

            return false;
        }
        $this->_constraint_count++;

        return true;
    }

    /**
     * Add a ordering constraint to the collector.
     *
     * @param string $field The name of the MgdSchema property to query against.
     * @param string $ordering One of 'ASC' or 'DESC' indicating ascending or descending
     *     ordering. The default is 'ASC'.
     * @param bool Indicating success.
     */
    function add_order($field, $ordering = null)
    {
        if (empty($field))
        {
            // This is a workaround for a situation the 1.7 Midgard core cannot intercept for
            // some reason unknown to me. Should be removed once 1.7.x is far enough in the
            // past.

            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('MidgardCollector: Cannot order by a null field name.', MIDCOM_LOG_INFO);
            debug_pop();

            return false;
        }

        if ($ordering === null)
        {
            $result = $this->_mc->add_order($field);
        }
        else
        {
            $result = $this->_mc->add_order($field, $ordering);
        }

        if (! $result)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to exectue add_order: Unknown or invalid column '{$field}'.", MIDCOM_LOG_ERROR);
            debug_pop();
        }

        return $result;
    }

    /**
     * Creates a new logical group within the query. They are set in parantheses in the final
     * SQL and will thus be evaluated with precedence over the normal out-of-group constraints.
     *
     * While the call lets you decide wether all constraints within the group are AND'ed or OR'ed,
     * only OR constraints make logcially sense in this context, which is why this proxy function
     * sets 'OR' as the default operator.
     *
     * @param string $operator One of 'OR' or 'AND' denoting the logical operation with which all
     *     constraints in the group are concatenated.
     */
    function begin_group($operator = 'OR')
    {
        $this->_mc->begin_group($operator);
    }

    /**
     * Ends a group previously started with begin_group().
     */
    function end_group()
    {
        $this->_mc->end_group();
    }

    /**
     * Limits the resultset to contain at most the specified number of records.
     * Set the limit to zero to retrieve all available records.
     *
     * This implementation overrides the original QB implementation for the implementation
     * of ACL restrictions.
     *
     * @param int $count The maximum number of records in the resultset.
     */
    function set_limit($count)
    {
        $this->_limit = $count;
    }

    /**
     * Sets the offset of the first record to retrieve. This is a zero based index,
     * so if you want to retrieve from the very first record, the correct offset would
     * be zero, not one.
     *
     * This implementation overrides the original QB implementation for the implementation
     * of ACL restrictions.
     *
     * @param int $offset The record number to start with.
     */
    function set_offset($offset)
    {
        $this->_offset = $offset;
    }

    /**
     * Returns only objects that are availble in the specified language. This will
     * disable the automatic fallback to the default language which would be in place
     * otherwise.
     *
     * @param int $language The ID of the language to limit the query to.
     */
    function set_lang($language)
    {
        $this->_mc->set_lang($language);
    }
    
    function set_key_property($property, $value = null)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("MidCOM collector does not allow switching key properties. It is always GUID.", MIDCOM_LOG_ERROR);
        debug_pop();

        return false;
    }
    
    function add_value_property($property)
    {
        return $this->_mc->add_value_property($property);
    }

    /**
     * Returns the number of elements matching the current query.
     *
     * <i>Developer's note:</i> According to the Midgard core documentation, the count method
     * does <b>not</b> execute the query using some COUNT() SQL statement. It merely returns
     * the number of records found and is, thus, mostly useless as you can just count($result)
     * on the PHP level anyway.
     *
     * To match the original inteded behavoir, the class will automatically execute the given
     * query if it has not yet been executed, thus breaking full API compatibility to the Midgard
     * core deliberatly on this point.
     *
     * Therefore, it is currently <i>strongly discouraged</i> to assume midgard_collector::count
     * to be useful as-is. See http://midgard.tigris.org/issues/show_bug.cgi?id=56 for details.
     *
     * @return int The number of records found by the last query.
     */
    function count()
    {
        if ($this->count == -1)
        {
            $this->execute();
            // TODO: ACL check
        }
        return $this->count;
    }

    /**
     * This is a mapping to the real count function of the Midgard Collector. It is mainly
     * intended when speed is important over accuracy, as it bypasses access control to get a
     * fast impression of how many objects are available in a given query. It should always
     * be kept in mind that this is a preliminary number, not a final one.
     *
     * Use this function with care. The information you obtain in general is neglible, but a creative
     * mind might nevertheless be able to take advantage of it.
     *
     * @return int The number of records matching the last query without taking access control into account.
     */
    function count_unchecked()
    {
        return $this->_mc->count();
    }
}


?>