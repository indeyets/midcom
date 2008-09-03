<?php
/**
 * @package org.openpsa.products
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Product search class
 *
 * @package org.openpsa.products
 */
class org_openpsa_products_handler_product_search extends midcom_baseclasses_components_handler
{
    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Redirector moving user to the search form of first schema
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     */
    function _handler_search_redirect($handler_id, $args, &$data)
    {
        foreach ($data['schemadb_product'] as $name => $schema)
        {
            $_MIDCOM->relocate("search/{$name}.html");
            // This will exit
        }
    }

    function _validate_operator($operator)
    {
        switch ($operator)
        {
            case '<':
            case 'lt':
            case '<=':
            case 'lte':
            case '=':
            case 'eq':
            case '<>':
            case '!eq':
            case '>=':
            case 'gte':
            case '>':
            case 'gt':
            case 'LIKE':
            case 'NOT LIKE':
            case 'INTREE':
                return true;
            default:
                return false;
        }
    }

    function _normalize_operator($operator)
    {
        switch ($operator)
        {
            case 'lt':
                return '<';
            case 'gt':
                return '>';
            case 'lte':
                return '<=';
            case 'gte':
                return '>=';
            case '!eq':
                return '<>';
            case 'eq':
                return '=';
            default:
                return $operator;
        }
    }

    /**
     * Check each search constraint for validity and normalize
     */
    function _normalize_search($constraints)
    {
        $normalized_parameters = array();

        foreach ($constraints as $key => $constraint)
        {
            if (!array_key_exists('property', $constraint))
            {
                // No field defined for this parameter, skip
                continue;
            }

            if (strstr(',', $constraint['property']))
            {
                $properties = explode(',', $constraint['property']);
                unset($constraints[$key]);
                foreach ($properties as $property)
                {
                    $constraints[] = array
                    (
                        'property'   => $property,
                        'constraint' => $constraint['constraint'],
                        'value'      => $constraint['value'],
                        'group'      => 'OR',
                    );
                }
            }
        }

        foreach ($constraints as $constraint)
        {
            if (!array_key_exists('property', $constraint))
            {
                // No field defined for this parameter, skip
                continue;
            }

            if (!array_key_exists($constraint['property'], $this->_request_data['schemadb_product'][$this->_request_data['search_schema']]->fields))
            {
                // This field is not in the schema
                // TODO: Raise error?
                continue;
            }

            if (!array_key_exists('constraint', $constraint))
            {
                $constraint['constraint'] = '=';
            }

            // Validate available constraints
            if (!$this->_validate_operator($constraint['constraint']))
            {
                continue;
            }

            $constraint['constraint'] = $this->_normalize_operator($constraint['constraint']);

            if (   !array_key_exists('value', $constraint)
                || $constraint['value'] == '')
            {
                // No value specified for this constraint, skip
                continue;
            }

            if ($constraint['constraint'] == 'LIKE')
            {
                $constraint['value'] = str_replace('*', '%', $constraint['value']);

                if (!strstr($constraint['value'], '%'))
                {
                    // Append a wildcard
                    $constraint['value'] = '%' . $constraint['value'] . '%';
                }
                // Replace multiple consecutive wildcards with single one.
                $constraint['value'] = preg_replace('/%+/', '%', $constraint['value']);
                /* if we do this here we get no results for some reason...
                // Do not add constraint if it's all wildcards
                if (preg_match('/^%+$/', $constraint['value']))
                {
                    continue;
                }
                */
            }

            // TODO: Handle typecasting of values to prevent QB errors

            $normalized_parameters[] = $constraint;
        }

        return $normalized_parameters;
    }

    /**
     * Search products using Midgard 1.8+ Query Builder
     */
    function _qb_list_all()
    {
        $qb = new org_openpsa_qbpager('org_openpsa_products_product_dba', 'org_openpsa_products_product_dba');
        $qb->results_per_page = $this->_config->get('products_per_page');

        $return_products = array();

        // Check that the object has correct schema
        $qb->begin_group('AND');
        $qb->add_constraint('parameter.domain', '=', 'midcom.helper.datamanager2');
        $qb->add_constraint('parameter.name', '=', 'schema_name');
        $qb->add_constraint('parameter.value', '=', $this->_request_data['search_schema']);
        $qb->end_group();

        foreach ($this->_config->get('search_index_order') as $ordering)
        {
            if (preg_match('/\s*reversed?\s*/', $ordering))
            {
                $reversed = true;
                $ordering = preg_replace('/\s*reversed?\s*/', '', $ordering);
            }
            else
            {
                $reversed = false;
            }

            if ($ordering === 'metadata.score')
            {
                if (version_compare(mgd_version(), '1.8.2', '<'))
                {
                    $ordering = 'score';
                    $reversed = false;
                }
            }

            if (   strpos($ordering, '.')
                && !class_exists('midgard_query_builder'))
            {
                debug_add("Ordering by linked properties requires 1.8 series Midgard", MIDCOM_LOG_WARN);
                continue;
            }

            if ($reversed)
            {
                $qb->add_order($ordering, 'DESC');
            }
            else
            {
                $qb->add_order($ordering);
            }
        }

        $products = $qb->execute();

        // FIXME: hack to prevent duplication of results
        foreach ($products as $product)
        {
            $return_products[$product->guid] = $product;
        }

        return $return_products;
    }

    /**
     * Search products using Midgard 1.8+ Query Builder
     */
    function _qb_search($constraints)
    {
        $qb = new org_openpsa_qbpager('org_openpsa_products_product_dba', 'org_openpsa_products_product_dba');
        $qb->results_per_page = $this->_config->get('products_per_page');

        // Check that the object has correct schema
        /* core bug prevents us from doing this properly
        $qb->begin_group('AND');
            $qb->add_constraint('parameter.domain', '=', 'midcom.helper.datamanager2');
            $qb->add_constraint('parameter.name', '=', 'schema_name');
            $qb->add_constraint('parameter.value', '=', $this->_request_data['search_schema']);
        $qb->end_group();
        */

        if ($this->_request_data['search_type'] == 'OR')
        {
            $qb->begin_group('OR');
        }
        foreach ($constraints as $constraint)
        {
            $storage = $this->_request_data['schemadb_product'][$this->_request_data['search_schema']]->fields[$constraint['property']]['storage'];
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('constraint', $constraint);
            debug_print_r('storage', $storage);
            debug_pop();
            if (   !is_array($storage)
                // Do not add constraint if it's all wildcards
                || preg_match('/^%+$/', $constraint['value']))
            {
                continue;
            }
            if ($storage['location'] == 'parameter')
            {
                $qb->begin_group('AND');
                    $qb->add_constraint('parameter.domain', '=', $storage['domain']);
                    $qb->add_constraint('parameter.name', '=', $constraint['property']);
                    $qb->add_constraint('parameter.value', $constraint['constraint'], $constraint['value']);
                $qb->end_group();
            }
            elseif ($storage['location'] == 'configuration')
            {
                $qb->begin_group('AND');
                    $qb->add_constraint('parameter.domain', '=', $storage['domain']);
                    $qb->add_constraint('parameter.name', '=', $storage['name']);
                    $qb->add_constraint('parameter.value', $constraint['constraint'], $constraint['value']);
                $qb->end_group();
            }
            else
            {
                // Simple field storage
                if (is_numeric($constraint['value']))
                {
                    // TODO: When 1.8.4 becomes more common we can reflect this instead
                    $constraint['value'] = (int) $constraint['value'];
                }
                $qb->add_constraint($storage['location'], $constraint['constraint'], $constraint['value']);
            }
        }
        if ($this->_request_data['search_type'] == 'OR')
        {
            $qb->end_group();
        }

        foreach ($this->_config->get('search_index_order') as $ordering)
        {
            if (preg_match('/\s*reversed?\s*/', $ordering))
            {
                $reversed = true;
                $ordering = preg_replace('/\s*reversed?\s*/', '', $ordering);
            }
            else
            {
                $reversed = false;
            }

            if ($ordering === 'metadata.score')
            {
                if (version_compare(mgd_version(), '1.8.2', '<'))
                {
                    $ordering = 'score';
                    $reversed = false;
                }
            }

            if (   strpos($ordering, '.')
                && !class_exists('midgard_query_builder'))
            {
                debug_add("Ordering by linked properties requires 1.8 series Midgard", MIDCOM_LOG_WARN);
                continue;
            }

            if ($reversed)
            {
                $qb->add_order($ordering, 'DESC');
            }
            else
            {
                $qb->add_order($ordering);
            }
        }

        $ret = $qb->execute();
        /* FIXME: It this the right way to do this? */
        $this->_request_data['search_qb'] =& $qb;

        // Check schemas this way until the core issue is fixed
        debug_push_class(__CLASS__, __FUNCTION__);
        foreach ($ret as $k => $product)
        {
            $schema = $product->get_parameter('midcom.helper.datamanager2', 'schema_name');
            debug_add("product schema '{$schema}' vs desired schema '{$this->_request_data['search_schema']}'");
            if ($schema == $this->_request_data['search_schema'])
            {
                continue;
            }
            unset($ret[$k]);
        }
        debug_pop();
        // array_merge reindexes the array to be continous
        return array_merge($ret);
    }

    function _constraint_test_value($constraint, $value, $parameter_exceptions = false)
    {
        switch ($constraint['constraint'])
        {
            case '<':
                if (   $parameter_exceptions
                    && $value == '')
                {
                    // Exception, don't allow empty params as results for "smaller than" search
                    return false;
                }
                if ($value < $constraint['value'])
                {
                    return true;
                }
                break;
            case '<=':
                if (   $parameter_exceptions
                    && $value == '')
                {
                    // Exception, don't allow empty params as results for "smaller than" search
                    return false;
                }
                if ($value <= $constraint['value'])
                {
                    return true;
                }
                break;
            case '=':
                if ($value == $constraint['value'])
                {
                    return true;
                }
                break;
            case '<>':
                if ($value != $constraint['value'])
                {
                    return true;
                }
                break;
            case '>=':
                if ($value >= $constraint['value'])
                {
                    return true;
                }
                break;
            case '>':
                if ($value > $constraint['value'])
                {
                    return true;
                }
                break;
            case 'LIKE':
                debug_push_class(__CLASS__, __FUNCTION__);
                /* LIKE is case-INsensitive, also the wildcard use might be more complex than trailing/preceding
                if (strstr($value, str_replace('%', '', $constraint['value'])))
                {
                    return true;
                }
                */
                // Find a delimiter not part of the constraint value (the SQL wildcard will be rewritten so % CAN be used as delimiter)
                $delimiters = array('/', '#', '%', '|', '_');
                $contraint_test_value = str_replace('%', '', $constraint['value']);
                foreach($delimiters as $delimiter)
                {
                    if (!strstr($contraint_test_value, $delimiter))
                    {
                        break;
                    }
                    $delimiter = false;
                }
                if (!$delimiter)
                {
                    // Could not determine delimiter to use, what to do ?
                    debug_add("could not determine regex delimiter for constraint '{$constraint['value']}'",  MIDCOM_LOG_ERROR);
                    debug_pop();
                    return false;
                }
                $regex = $delimiter . '^' . str_replace('%', '.*', $constraint['value']) . '$' . $delimiter . 'i';
                debug_add("testing preg_match({$regex}, {$value})");
                if (preg_match($regex, $value))
                {
                    debug_add("preg_match({$regex}, {$value}) returned true");
                    debug_pop();
                    return true;
                }
                debug_pop();
                break;
        }

        return false;
    }

    function _check_parameter($object, $domain, $name, $constraint)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $value = $object->parameter($domain, $name);
        debug_add("calling _constraint_test_value({$constraint}, {$value}, true)");
        debug_pop();
        return $this->_constraint_test_value($constraint, $value, true);
    }

    /**
     * Search products using combination of Query Builder and PHP-based checks
     */
    function _php_list_all()
    {
        $qb = new org_openpsa_qbpager('org_openpsa_products_product_dba', 'org_openpsa_products_product_dba');
        $qb->results_per_page = $this->_config->get('products_per_page');

        $filtered_products = array();

        foreach ($this->_config->get('search_index_order') as $ordering)
        {
            if (preg_match('/\s*reversed?\s*/', $ordering))
            {
                $reversed = true;
                $ordering = preg_replace('/\s*reversed?\s*/', '', $ordering);
            }
            else
            {
                $reversed = false;
            }

            if ($ordering === 'metadata.score')
            {
                if (version_compare(mgd_version(), '1.8.2', '<'))
                {
                    $ordering = 'score';
                    $reversed = false;
                }
            }

            if (   strpos($ordering, '.')
                && !class_exists('midgard_query_builder'))
            {
                debug_add("Ordering by linked properties requires 1.8 series Midgard", MIDCOM_LOG_WARN);
                continue;
            }

            if ($reversed)
            {
                $qb->add_order($ordering, 'DESC');
            }
            else
            {
                $qb->add_order($ordering);
            }
        }

        $initial_products = $qb->execute();

        foreach ($initial_products as $product)
        {
            $display = true;

            // Check that the schema is correct
            if ($product->get_parameter('midcom.helper.datamanager2', 'schema_name') != $this->_request_data['search_schema'])
            {
                $display = false;
            }

            if ($display)
            {
                $filtered_products[] = $product;
            }
        }

        return $filtered_products;
    }

    /**
     * Search products using combination of Query Builder and PHP-based checks
     */
    function _php_search($constraints)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = new org_openpsa_qbpager('org_openpsa_products_product_dba', 'org_openpsa_products_product_dba');
        $qb->results_per_page = $this->_config->get('products_per_page');

        $php_constraints = array();
        $filtered_products = array();
        $parameter_dummy_constraint_added = false;

        if ($this->_request_data['search_type'] == 'OR')
        {
            $qb->begin_group('OR');
        }
        // TODO: Refactor so that check for existence of parameter constraints first and then be smarter about the PHP constraints
        foreach ($constraints as $constraint)
        {
            debug_add("checking constraint\n===\n" . sprint_r($constraint) . "===\n");
            $storage = $this->_request_data['schemadb_product'][$this->_request_data['search_schema']]->fields[$constraint['property']]['storage'];
            if (   is_array($storage)
                // Do not add constraint if it's all wildcards
                && !preg_match('/^%+$/', $constraint['value']))
            {
                if (   $storage['location'] == 'parameter'
                    || $storage['location'] == 'configuration')
                {
                    if (   $this->_request_data['search_type'] == 'OR'
                        && !$parameter_dummy_constraint_added)
                    {
                        $qb->add_constraint('id', '>', 0);
                        $parameter_dummy_constraint_added;
                    }
                    $constraint['storage'] = $storage;
                    $php_constraints[] = $constraint;
                }
                else
                {
                    // Simple field storage, use as constraint for QB
                    // FIXME: This seems to be an enormous source for QB crashes and errors
                    if (is_numeric($constraint['value']))
                    {
                        // TODO: When 1.8.4 becomes more common we can reflect this instead
                        $constraint['value'] = (int) $constraint['value'];
                    }

                    $qb->add_constraint($storage['location'], $constraint['constraint'], $constraint['value']);
                    if ($this->_request_data['search_type'] == 'OR')
                    {
                        $constraint['storage'] = $storage;
                        $php_constraints[] = $constraint;
                    }
                }
            }
            // do not leave this laying around
            unset($storage);
        }
        if ($this->_request_data['search_type'] == 'OR')
        {
            $qb->end_group();
        }

        foreach ($this->_config->get('search_index_order') as $ordering)
        {
            if (preg_match('/\s*reversed?\s*/', $ordering))
            {
                $reversed = true;
                $ordering = preg_replace('/\s*reversed?\s*/', '', $ordering);
            }
            else
            {
                $reversed = false;
            }

            if ($ordering === 'metadata.score')
            {
                if (version_compare(mgd_version(), '1.8.2', '<'))
                {
                    $ordering = 'score';
                    $reversed = false;
                }
            }

            if (   strpos($ordering, '.')
                && !class_exists('midgard_query_builder'))
            {
                debug_add("Ordering by linked properties requires 1.8 series Midgard", MIDCOM_LOG_WARN);
                continue;
            }

            if ($reversed)
            {
                $qb->add_order($ordering, 'DESC');
            }
            else
            {
                $qb->add_order($ordering);
            }
        }

        $initial_products = $qb->execute();

        foreach ($initial_products as $product)
        {
            $display = true;
            $display_OR_tmp = false;

            foreach ($php_constraints as $constraint)
            {
                $storage =& $constraint['storage'];
                // Run the product through filters
                if ($storage['location'] == 'parameter')
                {
                    $pstat = $this->_check_parameter($product, $storage['domain'], $constraint['property'], $constraint);
                    if (!$pstat)
                    {
                        $display = false;
                    }
                    if (   $this->_request_data['search_type'] == 'OR'
                        && $pstat)
                    {
                        $display_OR_tmp = true;
                    }
                }
                elseif ($storage['location'] == 'configuration')
                {
                    $pstat = $this->_check_parameter($product, $storage['domain'], $storage['name'], $constraint);
                    if (!$pstat)
                    {
                        $display = false;
                    }
                    if (   $this->_request_data['search_type'] == 'OR'
                        && $pstat)
                    {
                        $display_OR_tmp = true;
                    }
                }
                // in OR searches we must validate the actual values as well
                elseif ($this->_request_data['search_type'] == 'OR')
                {
                    debug_add("calling this->_constraint_test_value(\$constraint, \$product->{$storage['location']})\n\$product->{$storage['location']}: {$product->$storage['location']}, \$constraint\n===\n" . sprint_r($constraint) . "===\n");
                    if ($this->_constraint_test_value($constraint, $product->$storage['location']))
                    {
                        $display_OR_tmp = true;
                    }
                    else
                    {
                        $display = false;
                    }
                }
            }
            if (   $this->_request_data['search_type'] == 'OR'
                && $display_OR_tmp)
            {
                $display = true;
            }

            // Check that the schema is correct
            $schema = $product->get_parameter('midcom.helper.datamanager2', 'schema_name');
            if (   $schema != $this->_request_data['search_schema']
                && !empty($schema))
            {
                $display = false;
            }

            if ($display)
            {
                $filtered_products[] = $product;
            }
        }

        debug_pop();
        return $filtered_products;
    }

    /**
     * Looks up a product to display.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_search($handler_id, $args, &$data)
    {
        $data['search_schema'] = $args[0];
        if (!array_key_exists($data['search_schema'], $data['schemadb_product']))
        {
            // Invalid schema to search for
            return false;
        }

        if ($handler_id == 'view_search_raw')
        {
            $_MIDCOM->skip_page_style = true;
        }

        $data['results'] = array();

        // Determine search type (AND vs OR)
        switch (true)
        {
            case (!array_key_exists('org_openpsa_products_search_type', $_REQUEST)):
            default:
                $data['search_type'] = 'AND';
                break;
            case ($_REQUEST['org_openpsa_products_search_type'] == 'OR'):
                $data['search_type'] = 'OR';
                break;
        }

        if (   array_key_exists('org_openpsa_products_search', $_REQUEST)
            && is_array($_REQUEST['org_openpsa_products_search']))
        {
            // Normalize the constraints
            $data['search_constraints'] = $this->_normalize_search($_REQUEST['org_openpsa_products_search']);

            if (count($data['search_constraints']) > 0)
            {

                // Process search
                if (   class_exists('midgard_query_builder')
                //    && $this->_request_data['search_type'] == 'OR'
                )
                {
                    $data['results'] = $this->_qb_search($data['search_constraints']);
                }
                /*
                else if (class_exists('midgard_query_builder'))
                {
                    // 1.8 would allow us to do this properly if not for core bug regarding parameters
                    $data['results'] = $this->_qb_search($data['search_constraints']);
                }
                */
                else
                {
                    // 1.7 forces us to read all objects and filter on PHP level
                    $data['results'] = $this->_php_search($data['search_constraints']);
                }
            }
        }
        elseif (array_key_exists('org_openpsa_products_list_all', $_REQUEST))
        {
            // Process search
            if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
            {
                // 1.8 allows us to do this elegantly
                $data['results'] = $this->_qb_list_all();
            }
            else
            {
                // 1.7 forces us to read all objects and filter on PHP level
                $data['results'] = $this->_php_list_all();
            }
        }
        else
        {
            // No search has yet been made
            if ($this->_config->get('search_default_to_all'))
            {
                // Process search
                if (version_compare(mgd_version(), '1.8.0alpha1', '>='))
                {
                    // 1.8 allows us to do this elegantly
                    $data['results'] = $this->_qb_list_all();
                }
                else
                {
                    // 1.7 forces us to read all objects and filter on PHP level
                    $data['results'] = $this->_php_list_all();
                }
            }
        }

        // Prepare datamanager
        $data['datamanager'] = new midcom_helper_datamanager2_datamanager($data['schemadb_product']);

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/midcom.helper.datamanager2/legacy.css",
            )
        );

        // Populate toolbar
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "create/{$data['root_group']}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_l10n->get('product group')
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                )
            );

            foreach (array_keys($this->_request_data['schemadb_product']) as $name)
            {
                $this->_node_toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "product/create/{$data['root_group']}/{$name}.html",
                        MIDCOM_TOOLBAR_LABEL => sprintf
                        (
                            $this->_l10n_midcom->get('create %s'),
                            $this->_l10n->get($this->_request_data['schemadb_product'][$name]->description)
                        ),
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                    )
                );
            }
        }

        $_MIDCOM->bind_view_to_object($this->_topic, $data['search_schema']);

        $data['view_title'] = $this->_l10n->get('search') . ': ' . $this->_l10n->get($data['schemadb_product'][$data['search_schema']]->description);

        $_MIDCOM->set_pagetitle("{$this->_topic->extra}: {$data['view_title']}");

        return true;
    }

    /**
     * Shows the loaded product.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_search($handler_id, &$data)
    {
        midcom_show_style('product_search_header');
        midcom_show_style('product_search_form');

        if (count($data['results']) == 0)
        {
            midcom_show_style('product_search_noresults');
        }
        else
        {
            $data['results_count'] = count($data['results']);
            $results_counter = 0;

            midcom_show_style('product_search_result_header');
            foreach ($data['results'] as $result)
            {
                $results_counter++;
                $data['results_counter'] = $results_counter;

                if (! $data['datamanager']->autoset_storage($result))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The datamanager for product {$result->id} could not be initialized, skipping it.");
                    debug_print_r('Object was:', $result);
                    debug_pop();
                    continue;
                }

                $data['product'] = $result;
                midcom_show_style('product_search_result_item');
            }
            midcom_show_style('product_search_result_footer');
        }

        midcom_show_style('product_search_footer');
    }

    /**
     * Static helper for finding queried search values
     */
    function get_queried_value($property, $constraint = null)
    {
        if (   !array_key_exists('org_openpsa_products_search', $_REQUEST)
            || !is_array($_REQUEST['org_openpsa_products_search']))
        {
            // No search was made
            return null;
        }

        if (   !array_key_exists($property, $_REQUEST['org_openpsa_products_search'])
            || !is_array($_REQUEST['org_openpsa_products_search'][$property]))
        {
            // This property wasn't specified
            return null;
        }

        if (!array_key_exists('value', $_REQUEST['org_openpsa_products_search'][$property]))
        {
            return null;
        }

        if (   !is_null($constraint)
            && $_REQUEST['org_openpsa_products_search'][$property]['constraint'] != $constraint)
        {
            return null;
        }

        return $_REQUEST['org_openpsa_products_search'][$property]['value'];
    }
}
?>