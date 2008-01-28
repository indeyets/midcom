<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Publications query, wraps the QB and provides additional features that are required to
 * transform a query to the M:N mapping table into a set of publication objects. Current
 * features:
 *
 * - Collect all categories which should be selected. Note, that there is only one category
 *   selectable per type (and this is treated like an *and* query.
 * - All constraints added will be automatically added to the publications table, not to the
 *   m:n mapping table. Same goes for order_bys.
 * - Post-process the resultset by eliminating duplicates and returning publication objects.
 * - Limits and Offsets are computed of the m:n lists, they override set_limit and set_offset
 *   accordingly, as limits on the m:n table don't make sense at this point.
 * - Hide the difference of queries with and without categories in the limit (the latter is much
 *   easier to handle).
 *
 * This means, that you can essentially use this function in place of a QB you'd normally run
 * against. It follows the QB API completely, which means that the documentation of the core
 * class is completely valid for this class unless mentioned otherwise.
 *
 * The new_query_builder method of the publication class is overridden to deliver an instance
 * of this class instead of a generic query builder.
 *
 * Their privileges link to the corresponding publication.
 *
 * Note, that immediately before execution of the query, an ordering over the publication field
 * of the m:n table is done to ensure that all publications are next to each other (this'll
 * keep the ordering stable if the query is rerun in case of m:n elements change).
 *
 * @todo Optimize count_unchecked (don't create publication objects, just count)
 * @todo Optimize count (only keep one publicaiton in memory, not all of them, just count)
 *
 * @see midcom_core_querybuilder
 * @package net.nehmer.publications
 */
class net_nehmer_publications_query extends midcom_baseclasses_components_purecode
{
    /**
     * The categories to which the query should be limited. For each category group, only one
     * limit can be set. Successive sets will overwrite existing ones.
     *
     * This member holds the actual fully qualified category identifiers as value.
     *
     * @var Array
     * @access private
     */
    var $_categories = Array();


    /**
     * A list of regular constraints, outside of the category limit. It contains an array triplet
     * for each constraint, holding field, operator and value in this order.
     *
     * @var Array
     * @access private
     */
    var $_constraints = Array();

    /**
     * A list of order directives. It contains an array for each one, holding field and order
     * in this order.
     *
     * @var Array
     * @access private
     */
    var $_orders = Array();

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
     * The QB instance with the actual constructed query.
     *
     * @var midcom_core_querybuilder
     * @access private
     */
    var $_qb = null;

    /**
     * Total count of matches
     */
    var $count = -1;

    /**
     * Creates a new query object.
     *
     * @param boolean $autofilter If this is true, the apply_filter_list is called automatically after
     *     construction. Since this is enabled by default, most standard cases should be already
     *     covered.
     */
    function net_nehmer_publications_query($autofilter = true)
    {
        $this->_component = 'net.nehmer.publications';
        parent::midcom_baseclasses_components_purecode();

        if ($autofilter)
        {
            $this->apply_filter_list();
        }
    }

    /**
     * Adds a category to the query. Only one category can be added per group.
     *
     * You can either pass group name and local identifier in two argument or a fully
     * qualified category identifier in one argument.
     *
     * @param string $group Either the group name or the fully qualified identifier of the
     *     category to add.
     * @param string $id The ID of the category to add in case only a group name was specified
     *     as first argument
     */
    function add_category($group, $id = null)
    {
        if($id !== null)
        {
            $this->_categories[] = "{$group}-{$id}";
        }
        else
        {
            $this->_categories[] = $group;
        }
    }

    /**
     * Adds a constraint against the publications table.
     */
    function add_constraint($field, $operator, $value)
    {
        $this->_constraints[] = Array($field, $operator, $value);
        return true;
        // return parent::add_constraint("publication.{$field}", $operator, $value);
    }

    /**
     * Adds a constraint against the publications table.
     */
    function add_order($field, $ordering = null)
    {
        // Evaluate reverse before we save it (to allow post-processing of the
        // fieldname).
        if ($ordering === null)
        {
            if (substr($field, 0, 8) == 'reverse ')
            {
                $this->_orders[] = Array(substr($field, 8), 'DESC');
            }
            else
            {
                $this->_orders[] = Array($field, 'ASC');
            }
        }
        else
        {
            $this->_orders[] = Array($field, $ordering);
        }

        return true;
    }

    function set_limit($count)
    {
        $this->_limit = $count;
    }

    function set_offset($offset)
    {
        $this->_offset = $offset;
    }

    /**
     * Executes an ACL aware query. The code will branch between categorized and uncategorized
     * queries according to the $_categories member. See the _categorized_* methods for details.
     *
     * In case of uncategorized queries, the result of the QB is returned directly without
     * any further modification.
     */
    function execute()
    {
        if ($this->_categories)
        {
            $this->_categorized_prepare_query();
            return $this->_categorized_compute_result(false);
        }
        else
        {
            $this->_uncategorized_prepare_query();
            $result = $this->_qb->execute();
            $this->count = $this->_qb->count;
            return $result;
        }
    }

    /**
     * Executes an ACL unaware query. The code will branch between categorized and uncategorized
     * queries according to the $_categories member. See the _categorized_(unchecked_)* methods
     * for details.
     *
     * In case of uncategorized queries, the result of the QB is returned directly without
     * any further modification.
     */
    function execute_unchecked()
    {
        if ($this->_categories)
        {
            $this->_categorized_prepare_query();
            return $this->_categorized_compute_result(true);
        }
        else
        {
            $this->_uncategorized_prepare_query();
            $result = $this->_qb->execute_unchecked();
            $this->count = $this->_qb->count;
            return $result;
        }
    }

    /**
     * Prepares a query with category support.
     */
    function _categorized_prepare_query()
    {
        $this->_qb = net_nehmer_publications_categorymap::new_query_builder();
        
        // add a dummy constraint to the publications table so that it gets joined
        // correctly if we only have an orderby constraint.
        $this->_qb->add_constraint('publication.id', '>', '0');

        foreach($this->_constraints as $constraint)
        {
            $this->_qb->add_constraint("publication.{$constraint[0]}", $constraint[1], $constraint[2]);
        }
        $this->_qb->add_constraint('category', 'IN', $this->_categories);

        foreach($this->_orders as $order)
        {
            $this->_qb->add_order("publication.{$order[0]}", $order[1]);
        }
        
        $this->_qb->add_order('publication');
    }

    /**
     * Prepares a query without category support.
     */
    function _uncategorized_prepare_query()
    {
        $this->_qb = net_nehmer_publications_entry::new_query_builder();

        foreach($this->_constraints as $constraint)
        {
            $this->_qb->add_constraint($constraint[0], $constraint[1], $constraint[2]);
        }

        foreach($this->_orders as $order)
        {
            $this->_qb->add_order($order[0], $order[1]);
        }

        $this->_qb->add_order('guid');
        $this->_qb->set_limit($this->_limit);
        $this->_qb->set_offset($this->_offset);
    }

    /**
     * Computes the actual resultset from a categorized query. It iterates over all m:n matches,
     * validates that each entry matches all categories and returns only the publications
     * valid.
     *
     * If you decide to run the query unchecked, the query result analyser trades speed over
     * accuracy. Currently this setting has no effect.
     *
     * @param boolean $checked This is true, if a fully query should be done, false if
     *     an "unchecked" query should go.
     * @see _categorized_unchecked_compute_result
     */
    function _categorized_compute_result($unchecked)
    {
        $matches = $this->_qb->execute();

        if (! $matches)
        {
            $this->count = 0;
            return Array();
        }

        $result = Array();

        // Counting state variables
        $limit = $this->_limit;
        $offset = $this->_offset;
        $this->count = 0;

        // State variables for category check
        $category_count = count($this->_categories);
        $missing_categories = $category_count;
        $match = null;
        $next = null;
        $eof = false;

        reset($matches);
        do
        {
            // Populate current and next match and EOF
            if ($match === null)
            {
                // Loop Startup
                $match = current($matches);
            }
            else
            {
                $match = $next;
            }
            $next = next($matches);
            $eof = ($next == false);

            // Count current category
            $missing_categories--;

            // If this is EOF or the next match has a different publication,
            // we go into post-processing. We do this unconditionally in case
            // we have duplicate categories in the DB (inconsistency) but to
            // keep this loop running cleanly.
            if (   $eof
                || $match->publication != $next->publication)
            {
                if ($missing_categories <= 0)
                {
                    // Do some counting
                    $this->count++;

                    if (   $this->_offset > 0
                        && $offset > 0)
                    {
                        // We need to skip this one, because we are outside the offset.
                        $offset--;
                        continue;
                    }

                    if (   $this->_limit == 0
                        || $limit > 0)
                    {
                        $publication = $match->get_publication();
                        if (! $publication)
                        {
                            // Ignore broken entries
                            debug_push_class(__CLASS__, __FUNCTION__);
                            debug_add("Failed to get publication {$match->publication}, last Midgard error was:" .
                                mgd_errstr(), MIDCOM_LOG_WARN);
                            debug_pop();
                            continue;
                        }

                        if ($limit)
                        {
                            $limit--;
                        }
                        $result[] = $publication;
                    }
                }

                // Prepare for the next publication
                $missing_categories = $category_count;
            }
        } while (! $eof);

        return $result;
    }

    /**
     * Returns the number of elements matching the current query.
     *
     * This is based on execute() in case the query wasn't run yet.
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
     * Returns the number of elements matching the current query.
     *
     * In case of categorized queries, this is based on execute_unchecked() in case the query
     * wasn't run yet. For uncategorized queries, we map to count_unchecked of the
     * underlying QB.
     */
    function count_unchecked()
    {
        if ($this->count == -1)
        {
            $this->execute_unchecked();
        }
        return $this->count;
    }

    /**
     * Helper function, used to compute the necessary filter listing for the current
     * configuration which is taken from the request context.
     *
     * @param midcom_helper_configuration $config The configuration to use. Defaults to the
     *     current config from the request data.
     */
    function apply_filter_list($config = null)
    {
        if (! $config)
        {
            $request_data =& $_MIDCOM->get_custom_context_data('request_data');
            $config =& $request_data['config'];
        }

        $filter_spec = $config->get('default_categories');
        if (! $filter_spec)
        {
            return;
        }

        $filters_raw = explode(':', $filter_spec);

        $result = Array();
        foreach ($filters_raw as $filter)
        {
            $this->add_category($filter);
        }

        return;
    }

}

?>