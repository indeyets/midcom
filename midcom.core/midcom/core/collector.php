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
 * This class must be used anyplace within MidCOM instead of the real
 * midgard_collector object within the MidCOM Framework. This wrapper is
 * required for the correct operation of many MidCOM services.
 *
 * It essentially wraps the calls to {@link midcom_helper__dbfactory::new_collector()}
 * and {@link midcom_helper__dbfactory::exec_collector()}.
 *
 * Normally you should never have to create an instance of this type directly,
 * instead use the get_new_mc() method available in the MidCOM DBA API or the
 * midcom_helper__dbfactory::new_collector() method which is still available.
 *
 * If you have to do create the instance manually however, do not forget to call the
 * {@link initialize()} function after construction, or the creation callbacks will fail.
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
class midcom_core_collector
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
     * NOTE: Checks not implemented yet
     *
     * Be aware, that this setting will currently not use the QB to filter the objects accordingly,
     * since there is no way yet to filter against parameters. This will mean some performance
     * impact.
     *
     */
    var $hide_invisible = true;

    /**
     * Keep track if $this->execute has been called
     */
    var $_executed = false;

    /**
     * The constructor wraps the class resolution into the MidCOM DBA system.
     * Currently, Midgard requires the actual MgdSchema base classes to be used
     * when dealing with the QB, so we internally note the corresponding class
     * information to be able to do correct typecasting later.
     *
     * @param string $classname The classname which should be queried.
     */
    function __construct($classname, $domain, $value)
    {
        $this->_real_class = $classname;
        if (isset($_class_mapping_cache[$classname]))
        {
            $mgdschemaclass = $_class_mapping_cache[$classname];
        }
        else
        {
            // Validate the class, we check for a single callback representatively only
            if (!method_exists($classname, '_on_prepare_new_collector'))
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Cannot create a midcom_core_collector instance for the type {$classname}: Does not seem to be a DBA class name.");
                // This will exit.
            }

            // Figure out the actual MgdSchema class from the decorator
            $dummy = new $classname();
            $mgdschemaclass = $dummy->__new_class_name__;
            $_class_mapping_cache[$classname] = $mgdschemaclass;
        }

        $this->_mc = new midgard_collector($mgdschemaclass, $domain, $value);

        // MidCOM's collector always uses the GUID as the key for ACL purposes
        $this->_mc->set_key_property('guid');
        if (   $GLOBALS['midcom_config']['i18n_multilang_strict']
            /* Why is this check here (check querybuilder.php for same functionality without this check) ?
            && $_MIDCOM->i18n->get_midgard_language() != 0
            */)
        {
            // FIXME: Re-enable this when it actually works
            $this->_mc->set_lang($_MIDCOM->i18n->get_midgard_language());
        }
    }

    /**
     * The initialization routine executes the _on_prepare_new_collector callback on the class.
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
     * 1. boolean _on_prepare_exec_collector(&$this) is called before the actual query execution. Return false to
     *    abort the operation.
     *
     * @return boolean indicating success/failure
     * @see _real_execute()
     */
    function execute()
    {
        if (! call_user_func_array(array($this->_real_class, '_on_prepare_exec_collector'), array(&$this)))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('The _on_prepare_exec_collector callback returned false, so we abort now.');
            debug_pop();
            return false;
        }
        $this->_executed = true;
        return true;
    }

    /**
     * Executes the MC
     *
     * @return void
     * @see midgard_collector::execute()
     */
    function _real_execute()
    {
        return $this->_mc->execute();
    }

    /**
     * Resets some internal variables for re-execute
     */
    function _reset()
    {
        $this->_executed = false;
        $this->count = -1;
        $this->denied = 0;
    }

    /**
     * Runs a query where <i>limit and offset is taken into account prior to
     * execution in the core.</i>
     *
     * This is useful in cases where you can safely assume read privileges on all
     * objects, and where you would otherwise have to deal with huge resultsets.
     *
     * Be aware that this might lead to empty resultsets "in the middle" of the
     * actual full resultset when read privileges are missing.
     *
     * @see list_keys()
     */
    function list_keys_unchecked()
    {
        $this->_reset();
        // Add the limit / offsets
        if ($this->_limit)
        {
            $this->_mc->set_limit($this->_limit);
        }
        if ($this->_offset)
        {
            $this->_mc->set_offset($this->_offset);
        }

        $newresult = $this->_list_keys_and_check_privileges();

        if (!is_array($newresult))
        {
            return $newresult;
        }

        call_user_func_array(array($this->_real_class, '_on_process_collector_result'), array(&$newresult));

        $this->count = count($newresult);

        debug_pop();
        return $newresult;
    }

    function _list_keys_and_check_privileges()
    {
        $this->_real_execute();
        $result = $this->_mc->list_keys();
        if (!is_array($result))
        {
            return $result;
        }
        $newresult = array();
        $classname = $this->_real_class;
        foreach ($result as $object_guid => $empty_copy)
        {
            if (!$_MIDCOM->auth->can_do_byguid('midgard:read', $object_guid, $classname))
            {
                debug_add("Failed to load result, read privilege on {$object_guid} not granted for the current user.", MIDCOM_LOG_INFO);
                $this->denied++;
                continue;
            }

            // Check visibility
            if ($this->hide_invisible)
            {
                // TODO: Implement
            }

            // Register the GUID as loaded in this request
            $_MIDCOM->cache->content->register($object_guid);

            $newresult[$object_guid] = array();
        }
        return $newresult;
    }

    /**
     * implements midgard_collector::list_keys with ACL and visibility checking
     *
     * @todo implement visibility checking
     */
    function list_keys()
    {
        /**
         * PONDER: Should we implement the moving window limit/offset handling here as well ?
         * probably it's not worth the effort since we're not actually instantiating objects
         * so many of the inefficencies related to that are avoided.
         * @see midcom_core_querybuilder::execute_windowed
         */
        $this->_reset();
        $result = $this->_list_keys_and_check_privileges();
        if (!is_array($result))
        {
            return $result;
        }
        $newresult = array();

        $limit = $this->_limit;
        $offset = $this->_offset;
        $classname = $this->_real_class;
        foreach ($result as $object_guid => $empty_copy)
        {
            // We have hit our limit
            if (   $this->_limit > 0
                && $limit == 0)
            {
                break;
            }

            if (!$_MIDCOM->auth->can_do_byguid('midgard:read', $object_guid, $classname))
            {
                debug_add("Failed to load result, read privilege on {$object_guid} not granted for the current user.", MIDCOM_LOG_INFO);
                $this->denied++;
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

            $newresult[$object_guid] = $empty_copy;

            if ($this->_limit > 0)
            {
                $limit--;
            }
        }

        call_user_func_array(array($this->_real_class, '_on_process_collector_result'), array(&$newresult));

        $this->count = count($newresult);

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
     * @return boolean Indicating success.
     */
    function add_constraint($field, $operator, $value)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $this->_reset();
        // Add check against null values, Core MC is too stupid to get this right.
        if ($value === null)
        {
            debug_add("Collector: Cannot add constraint on field '{$field}' with null value.",MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }

        if (! $this->_mc->add_constraint($field, $operator, $value))
        {
            debug_add("Failed to execute add_constraint.", MIDCOM_LOG_ERROR);
            debug_add("Class = '{$this->_real_class}, Field = '{$field}', Operator = '{$operator}'");
            debug_print_r('Value:', $value);
            debug_pop();

            return false;
        }
        $this->_constraint_count++;
        debug_pop();
        return true;
    }

    /**
     * Add an ordering constraint to the collector.
     *
     * @param string $field The name of the MgdSchema property to query against.
     * @param string $ordering One of 'ASC' or 'DESC' indicating ascending or descending
     *     ordering. The default is 'ASC'.
     * @return boolean Indicating success.
     */
    function add_order($field, $ordering = null)
    {
        /**
         * NOTE: So see also querybuilder.php when making changes here
         */
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
            if (substr($field, 0, 8) == 'reverse ')
            {
                $result = $this->_mc->add_order(substr($field, 8), 'DESC');
            }
            else
            {
                $result = $this->_mc->add_order($field);
            }
        }
        else
        {
            $result = $this->_mc->add_order($field, $ordering);
        }

        if (! $result)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to execute add_order: Unknown or invalid column '{$field}'.", MIDCOM_LOG_ERROR);
            debug_pop();
        }

        return $result;
    }

    /**
     * Creates a new logical group within the query. They are set in parentheses in the final
     * SQL and will thus be evaluated with precedence over the normal out-of-group constraints.
     *
     * While the call lets you decide whether all constraints within the group are AND'ed or OR'ed,
     * only OR constraints make logically sense in this context, which is why this proxy function
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
    function set_limit($limit)
    {
        $this->_reset();
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
        $this->_reset();
        $this->_offset = $offset;
    }

    /**
     * Returns only objects that are available in the specified language. This will
     * disable the automatic fallback to the default language which would be in place
     * otherwise.
     *
     * @param int $language The ID of the language to limit the query to.
     */
    function set_lang($language)
    {
        $this->_reset();
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
        if (!$this->_mc->add_value_property($property))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Failed to execute add_value_property '{$property}' for {$this->_real_class}.", MIDCOM_LOG_ERROR);
            debug_pop();

            return false;
        }
        
        return true;
    }

    /**
     * Returns the number of elements matching the current query.
     *
     * Due to ACL checking we must first execute the full query
     *
     * @return int The number of records found by the last query.
     */
    function count()
    {
        if ($this->count == -1)
        {
            if (!$this->_executed)
            {
                $this->execute();
            }
            $this->list_keys();
        }
        return $this->count;
    }

    /**
     * This is a mapping to the real count function of the Midgard Collector. It is mainly
     * intended when speed is important over accuracy, as it bypasses access control to get a
     * fast impression of how many objects are available in a given query. It should always
     * be kept in mind that this is a preliminary number, not a final one.
     *
     * Use this function with care. The information you obtain in general is negligible, but a creative
     * mind might nevertheless be able to take advantage of it.
     *
     * @return int The number of records matching the constraints without taking access control or visibility into account.
     */
    function count_unchecked()
    {
        $this->_reset();
        if ($this->_limit)
        {
            $this->_mc->set_limit($this->_limit);
        }
        if ($this->_offset)
        {
            $this->_mc->set_offset($this->_offset);
        }
        return $this->_mc->count();
    }
}

?>