<?php
/**
 * @package midcom
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id:querybuilder.php 3765 2006-07-31 08:51:39 +0000 (Mon, 31 Jul 2006) tarjei $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Memcached decorator around the querybuilder object
 * 
 * Use this class to connect to Memcached for selects
 * @todo: go through a defined api on the memcached module instead of the private object
 */

class midcom_core_querybuilder_cached
{
    /**
     * The timeout to use for this cache
     * @var int nr of seconds until object expiry
     */
    var $timeout = 3600;
    protected $key = array();
    protected $cache = null;

    function __construct ($cache = FALSE)
    {
        if ($cache === FALSE)
        {
            $this->cache = $_MIDCOM->cache->memcache;
        }
        else
        {
            $this->cache = $cache;
        }
    }

    /**
     * Recursive walk to build a key nomatter the inputs.
     * Note: With large lists of inputs, the key becomes very loong...
     */
    function rec_implode($key, $val)
    {
        $this->new_key .= "{$key}_$val";
    }

    /*
     * Makes sure that all calls are catched.
     * */
    function __call($name, $args)
    {
        if ($this->qb == NULL) 
        {
            throw new Exception("Querybuilder not set!"); 
        }
        $this->new_key = "";
        array_walk_recursive($args, array($this, 'rec_implode'));
        $this->key[] = $name . $this->new_key;
        if (method_exists($this->qb, $name)) {
            return call_user_func_array(array($this->qb, $name), $args);
        }
        throw new Exception('Tried to call unknown method '.get_class($this->qb).'::'.$name);
    }

    /**
     * Executes the query and saves it to memcached
     */
    function execute()
    {
        $key = "midcom_querybuilder_cache_{$this->qb->classname}" . implode($this->key , "_");

        $return = $this->cache->get($key);
        if ($return)
        {
            return $return; 
        }
        $return = $this->qb->execute();
        $this->cache->put('MISC', $key, $return, $this->timeout);
        return $return;
    }
}


/**
 * MidCOM DBA level wrapper for the Midgard Query Builder.
 *
 * This class must be used instead anyplace within MidCOM instead of the real
 * midgard_query_builder object within the MidCOM Framework. This wrapper is
 * required for the correct operation of many MidCOM services.
 *
 * It essentially wraps the calls to midcom_helper__dbfactory::new_query_builder()
 * and midcom_helper__dbfactory::exec_query_builder().
 *
 * Normally you should never have to create an instance of this type direectly,
 * instead use the get_new_qb() method available in the MidCOM DBA API or the
 * midcom_helper__dbfactory::new_query_builder() method which is still available.
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
class midcom_core_querybuilder extends midcom_baseclasses_core_object
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
     * The query builder instance that is internally used.
     *
     * @var midgard_query_builder
     * @access private
     */
    var $_qb;

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
    var $hide_invisible = true;

    /**
     * The class this qb is working on.
     * @var string classname
     */
    var $classname = null;

    /**
     * Keep track of GUIDs seen to avoid workaround ML bug
     */
    var $_seen_guids = array();
    
    var $_qb_error_result = 'UNDEFINED';
    
    /**
     * When determining window sizes for offset/limit queries use this as minimum size
     */
    var $min_window_size = 10;

    /**
     * When determining window sizes for offset/limit queries use this as maximum size
     */
    var $max_window_size = 500;

    /**
     * The constructor wraps the class resolution into the MidCOM DBA system.
     * Currently, Midgard requires the actual MgdSchema base classes to be used
     * when dealing with the QB, so we internally note the corresponding class
     * information to be able to do correct typecasting later.
     *
     * @param string $classname The classname which should be queried.
     * @todo remove baseclass resolution, Midgard core can handle extended classnames correctly nowadays
     */
    function midcom_core_querybuilder($classname)
    {
        $this->classname = $classname;
        static $_class_mapping_cache = Array();

        if (array_key_exists($classname, $_class_mapping_cache))
        {
            $baseclass = $_class_mapping_cache[$classname];
        }
        else
        {
            // Validate the class, we check for a single callback representativly only
            if (! in_array('_on_prepare_new_query_builder', get_class_methods($classname)))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Cannot create a midcom_core_querybuilder instance for the type {$classname}: Does not seem to be a DBA class name.");
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
                    "Cannot create a midcom_core_querybuilder instance for the type {$baseclass}: Class not found.");
                // This will exit.
            }
            $_class_mapping_cache[$classname] = $baseclass;
        }

        $this->_real_class = $classname;
        $this->_qb = new midgard_query_builder($baseclass);

        if ($GLOBALS['midcom_config']['i18n_multilang_strict'])
        {
            $this->_qb->set_lang($_MIDCOM->i18n->get_midgard_language());
        }
    }

    /**
     * The initialization routin executes the _on_prepare_new_querybuilder callback on the class.
     * This cannot be done in the constructor due to the reference to $this that is used.
     */
    function initialize()
    {
        call_user_func_array(array($this->_real_class, '_on_prepare_new_query_builder'), array(&$this));
    }

    function _execute_and_check_privileges($false_on_empty_mgd_resultset = false)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $result = @$this->_qb->execute();
        if (!is_array($result))
        {
            $this->_qb_error_result = $result;
            debug_print_r('Result was:', $result);
            debug_add('The querybuilder failed to execute, aborting.', MIDCOM_LOG_ERROR);
            debug_add('Last Midgard error was: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            if (isset($php_errormsg))
            {
                debug_add("Error message was: {$php_errormsg}", MIDCOM_LOG_ERROR);
            }

            debug_pop();
            return $result;
        }
        debug_add('Got ' . count($result) . ' initial results');
        if (   empty($result)
            && $false_on_empty_mgd_resultset)
        {
            debug_pop();
            return false;
        }

        // Workaround until the QB returns the correct type, refetch everything
        $newresult = Array();
        $classname = $this->_real_class;
        $skipped_objects = 0;
        $this->denied = 0;
        foreach ($result as $key => $value)
        {
            // Workaround to ML bug where we get multiple results in non-strict mode
            if (isset($this->_seen_guids[$value->guid]))
            {
                debug_add("The {$classname} object {$value->guid} has already been seen, probably MultiLang bug", MIDCOM_LOG_WARN);
                //debug_add('var_export($seen_guids): ' . var_export($this->_seen_guids, true));
                continue;
            }
            $this->_seen_guids[$value->guid] = true;

            // Create a new object instance (checks read privilege implicitly) using the copy-constuctor.
            $object = new $classname($value);

            if (mgd_errno() == MGD_ERR_ACCESS_DENIED)
            {
                // This is logged by the callers
                $this->denied++;
                $skipped_objects++;
                continue;
            }

            if (   ! $object
                || ! is_object($object))
            {
                debug_add("Could not create a MidCOM DBA instance of the {$classname} ID {$value->id}. See debug level log for details.",
                    MIDCOM_LOG_INFO);
                $skipped_objects++;
                continue;
            }

            // Check visibility
            if ($this->hide_invisible)
            {
                $metadata =& midcom_helper_metadata::retrieve($object);
                if (! $metadata)
                {
                    debug_add("Could not create a MidCOM metadata instance for {$classname} ID {$value->id}, assuming an invisible object.",
                        MIDCOM_LOG_INFO);
                    $skipped_objects++;
                    continue;
                }

                if (! $metadata->is_object_visible_onsite())
                {
                    debug_add("The {$classname} ID {$value->id} is hidden by metadata.", MIDCOM_LOG_INFO);
                    $skipped_objects++;
                    continue;
                }
            }

            $newresult[] = $object;
        }
        debug_add('Returning ' . count($newresult) . ' items');
        debug_pop();
        return $newresult;
    }

    /**
     * This function will execute the Querybuilder and call the appropriate callbacks from the
     * class it is associated to. This way, class authors have full control over what is actually
     * returned to the application.
     *
     * The calling sequence of all event handlers of the associated class is like this:
     *
     * 1. bool _on_prepare_exec_query_builder(&$this) is called before the actual query execution. Return false to
     *    abort the operation.
     * 2. The query is executed.
     * 3. void _on_process_query_result(&$result) is called after the successful execution of the query. You
     *    may remove any unwanted entries from the resultset at this point.
     *
     * If the execution of the query fails for some reason all available error information is logged
     * and a MIDCOM_ERRCRIT level error is triggered, halting execution.
     *
     * @param midgard_query_builder $qb An instance of the Query builder obtained by the new_query_builder
     *     function of this class.
     * @return Array The result of the query builder or null on any error. Note, that empty resultsets
     *     will return an empty array.
     * @todo Implement proper count / Limit support.
     */
    function execute_windowed()
    {
        // Reset these two in case someone tries to re-execute this
        $this->_seen_guids = array(); 
        $this->_qb_error_result = 'UNDEFINED';
        
        if (! call_user_func_array(array($this->_real_class, '_on_prepare_exec_query_builder'), array(&$this)))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('The _on_prepare_exec_query_builder callback returned false, so we abort now.');
            debug_pop();
            return null;
        }

        if ($this->_constraint_count == 0)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('This Query Builder instance has no constraints (set loglevel to debug to see stack trace)', MIDCOM_LOG_WARN);
            debug_print_function_stack('We were called from here:');
            debug_pop();
        }

        if (   empty($this->_limit)
            && empty($this->_offset))
        {
            // No point to do windowing
            $newresult = $this->_execute_and_check_privileges();
            if (!is_array($newresult))
            {
                return $newresult;
            }
        }
        else
        {
            //debug_push_class(__CLASS__, __FUNCTION__);
            $newresult = array();
            // Must be copies
            $limit = $this->_limit;
            $offset = $this->_offset;
            $i = 0;
            $this->_set_limit_offset_window($i);
            
            while (($resultset = $this->_execute_and_check_privileges(true)) !== false)
            {
                //debug_add("Iteration loop #{$i}");
                if ($this->_qb_error_result !== 'UNDEFINED')
                {
                    // QB failed in above method TODO: better catch
                    /*
                    debug_add('_execute_and_check_privileges caught QB error, returning that now', MIDCOM_LOG_WARN);
                    debug_pop();
                    */
                    return $this->_qb_error_result;
                }

                foreach($resultset as $object)
                {
                    // We still have offset left to skip
                    if ($offset)
                    {
                        //debug_add("Offset of {$this->_offset} not yet reached, continuing loop");
                        $offset--;
                        continue;
                    }
                    // We have hit our limit
                    if (   $this->_limit > 0
                        && $limit == 0)
                    {
                        //debug_add("Limit of {$this->_limit} hit, breaking out of loops");
                        break 2;
                    }

                    $newresult[] = $object;

                    if ($this->_limit > 0)
                    {
                        $limit--;
                    }
                }
                ++$i;
                $this->_set_limit_offset_window($i);
            }
        }

        call_user_func_array(array($this->_real_class, '_on_process_query_result'), array(&$newresult));

        /*
        // correct record count by the number of limit-skipped objects.
        $this->count = count($newresult) + $skipped_objects;
        */
        $this->count = count($newresult);

        //debug_pop();
        return $newresult;
    }

    function _set_limit_offset_window($iteration)
    {
        /*
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add("Called for iteration #{$iteration}");
        */
        static $window_size = 0;
        if (!$window_size)
        {
            // Try to be smart about the window size
            switch (true)
            {
                case (   empty($this->_offset)
                      && $this->_limit):
                    // Get limited number from start (I supposed generally less than 50% will be unreadable)
                    $window_size = round($this->_limit * 1.5);
                    break;
                case (   empty($this->_limit)
                      && $this->_offset):
                    // Get rest from offset
                    /* TODO: Somehow factor in that if we have huge number of objects and relatively small offset we want to increase window size
                    $full_object_count = $this->_qb->count();
                    */
                    $window_size = round($this->_offset * 2);
                case (   $this->_offset > $this->_limit):
                    // Offset is greater than limit, basically this is almost the same problem as above
                    $window_size = round($this->_offset * 2);
                    break;
                case (   $this->_limit > $this->_offset):
                    // Limit is greater than offset, this is probably similar to getting limited number from beginning
                    $window_size = round($this->_limit * 2);
                    break;
                case ($this->_limit == $this->_offset):
                    $window_size = round($this->_offset * 2);
                    break;
            }

            if ($window_size > $this->max_window_size)
            {
                $window_size = $this->max_window_size;
            }
            if ($window_size < $this->min_window_size)
            {
                $window_size = $this->min_window_size;
            }
        }
        //debug_add("Got window size {$window_size}");
        $offset = $iteration*$window_size;
        if ($offset)
        {
            //debug_add("Setting offset to {$offset}");
            $this->_qb->set_offset($offset);
        }
        //debug_add("Setting limit to {$window_size}");
        $this->_qb->set_limit($window_size);
        //debug_pop();
    }

    function execute()
    {
        return $this->execute_windowed();
    }

    /**
     * This function will execute the Querybuilder and call the appropriate callbacks from the
     * class it is associated to. This way, class authors have full control over what is actually
     * returned to the application.
     *
     * The calling sequence of all event handlers of the associated class is like this:
     *
     * 1. bool _on_prepare_exec_query_builder(&$this) is called before the actual query execution. Return false to
     *    abort the operation.
     * 2. The query is executed.
     * 3. void _on_process_query_result(&$result) is called after the successful execution of the query. You
     *    may remove any unwanted entries from the resultset at this point.
     *
     * If the execution of the query fails for some reason all available error information is logged
     * and a MIDCOM_ERRCRIT level error is triggered, halting execution.
     *
     * @param midgard_query_builder $qb An instance of the Query builder obtained by the new_query_builder
     *     function of this class.
     * @return Array The result of the query builder or null on any error. Note, that empty resultsets
     *     will return an empty array.
     * @todo Implement proper count / Limit support.
     */
    function execute_notwindowed()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! call_user_func_array(array($this->_real_class, '_on_prepare_exec_query_builder'), array(&$this)))
        {
            debug_add('The _on_prepare_exec_query_builder callback returned false, so we abort now.');
            debug_pop();
            return null;
        }

        if ($this->_constraint_count == 0)
        {
            debug_add('This Query Builder instance has no constraints.', MIDCOM_LOG_WARN);
            debug_print_function_stack('We were called from here:');
        }

        $result = $this->_qb->execute();
        if (!is_array($result))
        {
            debug_print_r('Result was:', $result);
            debug_add('The querybuilder failed to execute, aborting.', MIDCOM_LOG_ERROR);
            debug_add('Last Midgard error was: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            if (isset($php_errormsg))
            {
                debug_add("Error message was: {$php_errormsg}", MIDCOM_LOG_ERROR);
            }

            /*
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The query builder failed to execute, see the log file for more information.');
            // This will exit.
            */
            return false;
        }

        // Workaround until the QB returns the correct type, refetch everything
        $newresult = Array();
        $classname = $this->_real_class;
        $limit = $this->_limit;
        $offset = $this->_offset;
        $skipped_objects = 0;
        $this->denied = 0;
        // Workaround to ML bug where we get multiple results in non-strict mode
        $seen_guids = array();
        foreach ($result as $key => $value)
        {
            if (   $this->_limit > 0
                && $limit == 0)
            {
                $skipped_objects++;
                continue;
            }

            // Create a new object instance (checks read privilege implicitly) using the copy-constuctor.
            $object = new $classname($value);

            if (mgd_errno() == MGD_ERR_ACCESS_DENIED)
            {
                // This is logged by the callers
                $this->denied++;
                $skipped_objects++;
                continue;
            }

            if (   ! $object
                || ! is_object($object))
            {
                debug_add("Could not create a MidCOM DBA instance of the {$classname} ID {$value->id}. See debug level log for details.",
                    MIDCOM_LOG_INFO);
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
                $metadata =& midcom_helper_metadata::retrieve($object);
                if (! $metadata)
                {
                    debug_add("Could not create a MidCOM metadata instance for {$classname} ID {$value->id}, assuming an invisible object.",
                        MIDCOM_LOG_INFO);
                    $skipped_objects++;
                    continue;
                }

                if (! $metadata->is_object_visible_onsite())
                {
                    debug_add("The {$classname} ID {$value->id} is hidden by metadata.", MIDCOM_LOG_INFO);
                    $skipped_objects++;
                    continue;
                }
            }
            
            if (isset($seen_guids[$object->guid]))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The {$classname} object {$object->guid} has already been seen, probably MultiLang bug", MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }

            $newresult[] = $object;
            
            $seen_guids[$object->guid] = true;

            if ($this->_limit > 0)
            {
                $limit--;
            }
        }

        call_user_func_array(array($this->_real_class, '_on_process_query_result'), array(&$newresult));

        // correct record count by the number of limit-skipped objects.
        $this->count = count($newresult) + $skipped_objects;

        debug_pop();
        return $newresult;
    }

    /**
     * Temporary helper until execute can be optimized with rerunnable QBs,
     * runs a query where <i>limit and offset is taken into account prior to
     * execution in the core.</i>
     *
     * This is useful in cases where you can safely assume read privileges on all
     * objects, and where you would otherwise have to deal with huge resultsets.
     *
     * Be aware that this might lead to empty resultsets "in the middle" of the
     * actual full resultset when read privileges are missing.
     *
     * @see execute()
     */
    function execute_unchecked()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if (! call_user_func_array(array($this->_real_class, '_on_prepare_exec_query_builder'), array(&$this)))
        {
            debug_add('The _on_prepare_exec_query_builder callback returned false, so we abort now.');
            debug_pop();
            return null;
        }

        if ($this->_constraint_count == 0)
        {
            debug_add('This Query Builder instance has no constraints.', MIDCOM_LOG_WARN);
            debug_print_function_stack('We were called from here:');
        }

        // Add the limit / offsets
        if ($this->_limit)
        {
            $this->_qb->set_limit($this->_limit);
        }
        if ($this->_offset)
        {
            $this->_qb->set_offset($this->_offset);
        }

        $result = $this->_qb->execute();
        if (!is_array($result))
        {
            debug_print_r('Result was:', $result);
            debug_add('The querybuilder failed to execute, aborting.', MIDCOM_LOG_ERROR);
            debug_add('Last Midgard error was: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            if (isset($php_errormsg))
            {
                debug_add("Error message was: {$php_errormsg}", MIDCOM_LOG_ERROR);
            }

            /*
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'The query builder failed to execute, see the log file for more information.');
            // This will exit.
            */
            return false;
        }

        // Workaround until the QB returns the correct type, refetch everything
        $newresult = Array();
        $classname = $this->_real_class;
        $this->denied = 0;
        foreach ($result as $key => $value)
        {
            // Create a new object instance (checks read privilege implicitly) using the copy-constuctor.
            $object = new $classname($value);

            if (mgd_errno() == MGD_ERR_ACCESS_DENIED)
            {
                // This is logged by the callers
                $this->denied++;
                continue;
            }

            if (   ! $object
                || ! is_object($object))
            {
                debug_add("Could not create a MidCOM DBA instance of the {$classname} ID {$value->id}. See debug level log for details.",
                    MIDCOM_LOG_INFO);
                continue;
            }

            // Check visibility
            if ($this->hide_invisible)
            {
                $metadata =& midcom_helper_metadata::retrieve($object);
                if (! $metadata)
                {
                    debug_add("Could not create a MidCOM metadata instance for {$classname} ID {$value->id}, assuming an invisible object.",
                        MIDCOM_LOG_INFO);
                    continue;
                }

                if (! $metadata->is_object_visible_onsite())
                {
                    debug_add("The {$classname} ID {$value->id} is hidden by metadata.", MIDCOM_LOG_INFO);
                    continue;
                }
            }

            $newresult[$key] = $object;
        }

        call_user_func_array(array($this->_real_class, '_on_process_query_result'), array(&$newresult));

        $this->count = count($newresult);

        debug_pop();
        return $newresult;
    }

    /**
     * Add a constraint to the query builder.
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
        if (! $this->_qb->add_constraint($field, $operator, $value))
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
     * Add a ordering constraint to the query builder.
     *
     * This function has extended functionality against the pure Midgard Query Builder:
     * It can deal with legacy Midgard 'reverse $field' style sorting orders. All calls
     * to sort with such fields when using the default ordering will enforce descending
     * ordering over the default.
     *
     * @param string $field The name of the MgdSchema property to query against.
     * @param string $ordering One of 'ASC' or 'DESC' indicating ascending or descending
     *     ordering. The default is 'ASC'.
     * @param bool Indicating success.
     */
    function add_order($field, $ordering = null)
    {
        if (! $field)
        {
            // This is a workaround for a situation the 1.7 Midgard core cannot intercept for
            // some reason unknown to me. Should be removed once 1.7.x is far enough in the
            // past.

            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('QueryBuilder: Cannot order by a null field name.', MIDCOM_LOG_INFO);
            debug_pop();

            return false;
        }

        if ($ordering === null)
        {
            if (substr($field, 0, 8) == 'reverse ')
            {
                $result = $this->_qb->add_order(substr($field, 8), 'DESC');
            }
            else
            {
                $result = $this->_qb->add_order($field);
            }
        }
        else
        {
            $result = $this->_qb->add_order($field, $ordering);
        }

        if (! $result)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to exectue add_order for column '{$field}', midgard error: " . mgd_errstr(), MIDCOM_LOG_ERROR);
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
        $this->_qb->begin_group($operator);
    }

    /**
     * Ends a group previously started with begin_group().
     */
    function end_group()
    {
        $this->_qb->end_group();
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
    function set_limit($limit)
    {
        $this->_limit = $limit;
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
        $this->_qb->set_lang($language);
    }
    
    /**
     * Include deleted objects (metadata.deleted is TRUE) in query results.
     */
    function include_deleted()
    {
        $this->_qb->include_deleted();
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
     * Therefore, it is currently <i>strongly discouraged</i> to assume midgard_query_builder::count
     * to be useful as-is. See http://midgard.tigris.org/issues/show_bug.cgi?id=56 for details.
     *
     * @return int The number of records found by the last query.
     */
    function count()
    {
        if ($this->count == -1)
        {
            $this->execute();
        }
        return $this->count;
    }

    /**
     * This is a mapping to the real count function of the Midgard Query Builder. It is mainly
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
        // TODO: Handle limit and offset
        return $this->_qb->count();
    }
}


?>
