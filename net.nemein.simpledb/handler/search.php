<?php
/**
 * @package net.nemein.simpledb
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: search.php 4051 2006-09-12 07:32:51Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * simpledb forum search
 *
 * @package net.nemein.simpledb
 */
class net_nemein_simpledb_handler_search extends midcom_baseclasses_components_handler
{
    /**
     * Schema field names and their storage locations
     *
     * @access private
     * @var array
     */
    var $_fields = array ();

    /**
     * Stores the parameter field names
     *
     * @access private
     * @var array
     */
    var $_parameters = array ();

    /**
     * Query Builder for common use inside the handler class. Helps in breaking methods
     * into smaller pieces.
     *
     * @access private
     */
    var $_qb = null;

    /**
     * Storage for the set query keys to prevent duplicated entries
     *
     * @access private
     */
    var $_set_query_keys = array ();

    /**
     * Simple default constructor.
     */
    function net_nemein_simpledb_handler_search()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Get the column names from the schema fields
     *
     * @access private
     */
    function _get_columns()
    {
        // Make layout array visible to elements
        $columns = array();
        foreach ($this->_request_data['schema_fields'] as $key => $field)
        {
            $viewable = true;
            if (   isset($field['hidden'])
                && $field['hidden'])
            {
                // Hidden field, skip
                continue;
            }
            if (   isset($field['net_nemein_simpledb_list'])
                && $field['net_nemein_simpledb_list'] == false)
            {
                // View not to be listed, skip
                continue;
            }

            $columns[$key] = $this->_l10n->get($field['description']);
        }

        return $columns;
    }

    /**
     * Get the schema field keys and their storage locations
     *
     * @access private
     */
    function _get_storage_location()
    {
        $locations = array ();
        foreach ($this->_request_data['schema_fields'] as $key => $array)
        {
            if (!array_key_exists('location', $array))
            {
                continue;
            }
            if (   isset($array['net_nemein_simpledb_search'])
                && empty($array['net_nemein_simpledb_search']))
            {
                // Marked as non-searchable
                continue;
            }

            $locations[$key] = $array['location'];
        }

        return $locations;
    }

    /**
     * Check the filters
     *
     *
     */
    function _check_filters()
    {
        // TODO: These should be reformatted to be run inside the QB
        if ($this->_config->get('enable_filtering'))
        {
            // Regular text filters
            if (   array_key_exists('net_nemein_simpledb_filter', $_REQUEST)
                && is_array($_REQUEST['net_nemein_simpledb_filter']))
            {
                foreach ($_REQUEST['net_nemein_simpledb_filter'] as $field => $filter)
                {
                    // Support for "view all"
                    if (   $filter === '**'
                        || $filter === '*')
                    {
                        // Support for multiselect fields
                        // Note: this is strict search for now, full value needed
                    }
                    elseif (is_array($this->_request_data['view'][$field]))
                    {
                        if (!array_key_exists($filter, $this->_request_data['view'][$field]))
                        {
                            return false;
                        }
                        // Support for regular text fields
                    }
                    elseif (!stristr($this->_request_data['view'][$field], $filter))
                    {
                        return false;
                    }
                }
            }

            // Less than (<) filters
            if (   array_key_exists('net_nemein_simpledb_filter_lessthan', $_REQUEST)
                && is_array($_REQUEST['net_nemein_simpledb_filter_lessthan']))
            {
                foreach ($_REQUEST['net_nemein_simpledb_filter_lessthan'] as $field => $filter)
                {
                    if (   array_key_exists($field, $this->_request_data['view'])
                        && is_numeric($filter)
                        && is_numeric($this->_request_data['view'][$field]))
                    {
                        if ($this->_request_data['view'][$field] > $filter)
                        {
                            return false;
                        }
                    }
                }
            }

            // Greater than (>) filters
            if (   array_key_exists('net_nemein_simpledb_filter_greaterthan', $_REQUEST)
                && is_array($_REQUEST['net_nemein_simpledb_filter_greaterthan']))
            {
                foreach ($_REQUEST['net_nemein_simpledb_filter_greaterthan'] as $field => $filter)
                {
                    if (   array_key_exists($field, $this->_request_data['view'])
                        && is_numeric($filter)
                        && is_numeric($this->_request_data['view'][$field]))
                    {
                        if ($this->_request_data['view'][$field] < $filter)
                        {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * Helper method, which generates the query builder constraint depending on the storage location
     *
     * @access private
     * @param $value String defining the query words
     * @param $domain Defines which field in the schema should contain the query words
     * @param $constraint Defines the searching method.
     */
    function _add_qb_constraint($value, $domain, $type = 'LIKE')
    {
        if ($value === '%%')
        {
            return;
        }

        if (!array_key_exists($domain, $this->_fields))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Trying to restrict the search with an invalid schema key: {$domain}.");
            // This will exit
        }

        if (!array_key_exists('location', $this->_request_data['schema_fields'][$domain]))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "No storage specified for '{$domain}' in schema!");
            // This will exit
        }

        $storage = $this->_fields[$domain];

        if ($storage === 'attachment')
        {
            return;
        }

        switch ($type)
        {
            case 'is':
            case 'equal':
                $constraint = '=';
                break;

            case 'less_than':
            case 'lt':
                $constraint = '<';
                break;

            case 'greater_than':
            case 'gt':
                $constraint = '>';
                break;

            case 'lte':
                $constraint = '<=';
                break;

            case 'gte':
                $constraint = '>=';
                break;

            case 'not':
                $constraint = '<>';
                break;

            default:
                // TODO: A likkle bick of error checking?
                $constraint = $type;
        }

        if ($storage === 'parameter')
        {
            /* See core bug #141
            if (version_compare(mgd_version(), '1.7', '>'))
            {
                $this->_qb->begin_group('AND');
                    $this->_qb->add_constraint('parameter.domain', '=', 'midcom.helper.datamanager');
                    $this->_qb->add_constraint('parameter.name', '=', "data_{$domain}");
                    $this->_qb->add_constraint('parameter.value', $constraint, $value);
                $this->_qb->end_group();
            }
            else
            {
            */
                // With Midgard 1.7 it has to be done this way. Unfortunately it means that
                // we will be including only those search results, which have the parameter
                // AND a hit in an earlier point. Results might be left out.
                $this->_parameters[$domain] = array
                (
                    'constraint' => $constraint,
                    'query' => $value,
                );
            //}
            return;
        }

        // If the query has already been set, skip the duplicate entry
        // TODO: Would be very nice to have duplicate, but using grouping if it is the case.
        if (array_key_exists($storage, $this->_set_query_keys))
        {
            return;
        }

        $this->_set_query_keys[$storage] = $value;

        // echo "{$storage} => {$value} ({$constraint})<br />\n";
        $this->_qb->add_constraint($storage, $constraint, $value);
    }

    /**
     * Check the GET filters. This provides some backwards compatibility with earlier
     * versions of net.nemein.simpledb
     *
     * @access private
     */
    function _check_get_filters()
    {
        // Filtering support
        if (   array_key_exists('net_nemein_simpledb_filter', $_REQUEST)
            && is_array($_REQUEST['net_nemein_simpledb_filter']))
        {
            foreach ($_REQUEST['net_nemein_simpledb_filter'] as $field => $filter)
            {
                // Skip the get all methods
                if (   $filter === '*'
                    || $filter === '**'
                    || trim($filter) === '')
                {
                    continue;
                }
                // echo "{$field} => {$filter}<br />\n";
                $this->_add_qb_constraint($filter, $field, '=');
            }
        }

        if (   array_key_exists('net_nemein_simpledb_filter_lessthan', $_REQUEST)
            && is_array($_REQUEST['net_nemein_simpledb_filter_lessthan']))
        {
            foreach ($_REQUEST['net_nemein_simpledb_filter_lessthan'] as $field => $filter)
            {
                // Skip the get all methods
                if (   $filter === '*'
                    || $filter === '**'
                    || trim($filter) === '')
                {
                    continue;
                }

                // echo "{$field} => {$filter} (<)<br />\n";
                $this->_add_qb_constraint($filter, $field, '<');

            }
        }

        if (   array_key_exists('net_nemein_simpledb_filter_greaterthan', $_REQUEST)
            && is_array($_REQUEST['net_nemein_simpledb_filter_greaterthan']))
        {
            foreach ($_REQUEST['net_nemein_simpledb_filter_greaterthan'] as $field => $filter)
            {
                // Skip the get all methods
                if (   $filter === '*'
                    || $filter === '**'
                    || trim($filter) === '')
                {
                    continue;
                }

                // echo "{$field} => {$filter} (>)<br />\n";
                $this->_add_qb_constraint($filter, $field, '>');
            }
        }
    }

    /**
     * Handle the searching phase
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return bool Indicating success.
     */
    function _handler_search($handler_id, $args, &$data)
    {
        // Get information on the columns
        $data['columns'] = $this->_get_columns();

        // Initialize the results array
        $data['results'] = array ();

        // Get the storage locations for the schema field names
        $this->_fields = $this->_get_storage_location();

        $data['query'] = '';

        // Run the query
        $this->_qb = midcom_baseclasses_database_article::new_query_builder();

        if (array_key_exists('net_nemein_simpledb_viewer_query', $_REQUEST))
        {
            if (version_compare(mgd_version(), '1.7', '>'))
            {
                $this->_check_get_filters();
            }

            // Prepare the query
            $data['query'] = $_REQUEST['net_nemein_simpledb_viewer_query'];

            if (   strstr($data['query'], '*')
                || strstr($data['query'], '%'))
            {
                // Change wildcards to QB supported format
                $data['query'] = str_replace('*', '%', $data['query']);
            }
            else
            {
                // User didn't specify wildcards, insert them
                $data['query'] = "%{$data['query']}%";
            }

            $parameters = array ();

            $this->_qb->add_order($this->_config->get('sort order'));

            $this->_qb->add_constraint('topic', '=', $this->_topic->id);

            // Do not add constraints if the query is only wildcards (much faster)
            if (!preg_match('/^%+$/', $data['query']))
            {
                $this->_qb->begin_group('OR');
                    foreach ($this->_fields as $key => $storage)
                    {
                        $this->_add_qb_constraint($data['query'], $key);
                    }
                $this->_qb->end_group();
            }

            // Enable strictly specified query types
            if (array_key_exists('net_nemein_simpledb_viewer_query_key', $_POST))
            {
                foreach ($_POST['net_nemein_simpledb_viewer_query_key'] as $key => $value)
                {
                    if (is_array($value))
                    {
                        $this->_qb->begin_group('AND');
                            foreach ($value as $type => $query)
                            {
                                $this->_add_qb_constraint($query, $key, $type);
                            }
                        $this->_qb->end_group();
                    }
                    else
                    {
                        $this->_add_qb_constraint($query, $key, $value);
                    }
                }
            }
        }

        if (array_key_exists('net_nemein_simpledb_viewer_query_submit', $_REQUEST))
        {
            $data['results'] = $this->_qb->execute();
        }

        return true;
    }

    /**
     * Check if the component should handle quick searches
     *
     * @return boolean Indicating success
     */
    function _can_handle_quick ($handler_id, $args, &$data)
    {
        // Check if a topic with the name is found
        $qb = midcom_db_topic::new_query_builder();
        $qb->add_constraint('up', '=', $this->_topic->id);
        $qb->add_constraint('name', '=', $args[0]);
        $qb->set_limit(1);

        if ($qb->count() !== 0)
        {
            return false;
        }

        $this->_request_data['datamanager'] = new midcom_helper_datamanager($this->_config->get('schemadb'));
        if (!$this->_request_data['datamanager'])
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to instantiate datamanager for schema database ' . $this->_config->get('schemadb'));
            // This will exit.
        }

        // Check if the search would be possible
        $this->_request_data['schema_name'] = $this->_config->get('topic_schema');
        $this->_request_data['schema_fields'] = $this->_request_data['datamanager']->_layoutdb[$this->_request_data['schema_name']]['fields'];

        if (!array_key_exists($args[0], $this->_request_data['schema_fields']))
        {
            return false;
        }

        return true;
    }

    /**
     * All the necessary error checking was done in the method _can_handle_quick
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array $data The local request data.
     * @return boolean indicating success
     */
    function _handler_quick ($handler_id, $args, &$data)
    {
        $this->_domain = $args[0];
        $this->_query = $args[1];

        // Get information on the columns
        $data['columns'] = $this->_get_columns();

        $data['query'] = $this->_query;

        if ($this->_query != '')
        {
            // Format the query back for display
            $this->_query = str_replace('%', '*', $data['query']);
        }

        // Get the storage locations for the schema field names
        $this->_fields = $this->_get_storage_location();

        $this->_qb = midcom_db_article::new_query_builder();
        $this->_qb->add_constraint('topic', '=', $this->_topic->id);
        $this->_add_qb_constraint("%{$this->_query}%", $this->_domain);

        return true;
    }

    /**
     * Show the results of the quick search
     *
     * @access private
     */
    function _show_quick($handler_id, &$data)
    {
        midcom_show_style('view-header');
        midcom_show_style('view-search-form');
        midcom_show_style('view-index-header');

        $entries_shown = 0;
        if ($this->_qb->count() === 0)
        {
            midcom_show_style('view-index-nomatch');
        }
        else
        {
            $data['results'] = $this->_qb->execute();

            foreach ($data['results'] as $result)
            {
                // Load through datamanager
                $data['datamanager']->init($result);
                $data['view'] = $data['datamanager']->get_array();

                // Filter out the values that couldn't be used in query builder
                if (version_compare(mgd_version(), '1.8', '<'))
                {
                    if (!$this->_check_filters())
                    {
                        // We're not displaying this one
                        continue;
                    }
                }

                $data['entry'] = $result;
                $data['view_name'] = "view/{$result->name}.html";
                midcom_show_style('view-index-item');
                $entries_shown++;
            }
        }

        $data['entries_shown'] = $entries_shown;

        midcom_show_style('view-index-footer');
        midcom_show_style('view-footer');
    }

    /**
     * Show the search form
     *
     *
     */
    function _show_search($handler_id, &$data)
    {
        if ($data['query'] != '')
        {
            // Format the query back for display
            $data['query'] = str_replace('%', '*', $data['query']);

            return $this->_show_search_results($handler_id, &$data);
        }

        return $this->_show_search_form($handler_id, &$data);
    }

    function _show_search_form($handler_id, &$data)
    {
        midcom_show_style('view-header');
        midcom_show_style('view-search-form');
        midcom_show_style('view-footer');
    }

    function _show_search_results($handler_id, &$data)
    {
        midcom_show_style('view-header');
        midcom_show_style('view-search-form');
        midcom_show_style('view-index-header');

        $entries_shown = 0;

        foreach ($data['results'] as $result)
        {
            // Load through datamanager
            $data['datamanager']->init($result);
            $data['view'] = $data['datamanager']->get_array();

            // Filter out the values that couldn't be used in query builder
            if (version_compare(mgd_version(), '1.8', '<'))
            {
                if (!$this->_check_filters())
                {
                    // We're not displaying this one
                    continue;
                }
            }

            $data['entry'] = $result;
            $data['view_name'] = "view/{$result->name}.html";
            midcom_show_style('view-index-item');
            $entries_shown++;
        }

        if ($entries_shown == 0)
        {
            midcom_show_style('view-index-nomatch');
        }

        $data['entries_shown'] = $entries_shown;

        midcom_show_style('view-index-footer');
        midcom_show_style('view-footer');
    }
}

?>