<?php
/**
 * @package net.nemein.organizations
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3667 2006-06-28 12:31:03Z torben $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Search page handler, renders index views.
 *
 * @package net.nemein.organizations
 */

class net_nemein_organizations_handler_search extends midcom_baseclasses_components_handler
{
    /**
     * The groups to display on the index page, already ordered correctly.
     *
     * @var Array
     * @access private
     */
    var $_groups = null;

    /**
     * The Datamanager of the article to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['groups'] =& $this->_groups;

        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        
        $this->_request_data['schema'] = $this->_config->get('schema');
        
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }

    /**
     * Simple default constructor.
     */
    function net_nemein_organizations_handler_search()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * This function creates a DM2 Datamanager instance to without any set storage so far.
     * The configured schema will be selected, but no set_storage is done. The various
     * view handlers treat this differently.
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->set_schema($this->_config->get('schema')))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance.');
            // This will exit.
        }
    }
    
    function _validate_operator($operator)
    {
        switch ($operator)
        {
            case '<':
            case '<=':
            case '=':
            case '<>':
            case '>=':
            case '>':
            case 'LIKE':
                return true;
            default:
                return false;
        }
    }

    /**
     * Check each search constraint for validity and normalize
     */
    function _normalize_search($constraints)
    {
        $normalized_parameters = array();

        foreach ($constraints as $constraint)
        {
            if (!array_key_exists('property', $constraint))
            {
                // No field defined for this parameter, skip
                continue;
            }

            if (!array_key_exists($constraint['property'], $this->_request_data['schemadb'][$this->_request_data['schema']]->fields))
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
                    $constraint['value'] .= '%';
                }
            }

            // TODO: Handle typecasting of values to prevent QB errors

            $normalized_parameters[] = $constraint;
        }

        return $normalized_parameters;
    }
    
    function _check_parameter($object, $domain, $name, $constraint)
    {
        $value = $object->parameter($domain, $name);

        switch ($constraint['constraint'])
        {
            case '<':
                if ($value == '')
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
                if ($value == '')
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
                if (strstr($value, str_replace('%', '', $constraint['value'])))
                {
                    return true;
                }
                break;
        }

        return false;
    }

    /**
     * Returns a post-processed list of groups to display on the index page.
     */
    function _search_groups($constraints)
    {
        $filtered_groups = array();
        $php_constraints = array();
        
        $qb = org_openpsa_contacts_group::new_query_builder();
    
        $group = new org_openpsa_contacts_group($this->_config->get('group'));
        $qb->add_constraint('owner', '=', $group->id);
        foreach ($this->_config->get('index_order') as $ordering)
        {
            $qb->add_order($ordering);
        }
        
        foreach ($constraints as $constraint)
        {
            $storage = $this->_request_data['schemadb'][$this->_request_data['schema']]->fields[$constraint['property']]['storage'];
            if (is_array($storage))
            {
                if (   $storage['location'] == 'parameter'
                    || $storage['location'] == 'configuration')
                {
                    $constraint['storage'] = $storage;
                    $php_constraints[] = $constraint;
                }
                else
                {
                    // Simple field storage, use as constraint for QB
                    // FIXME: This seems to be an enormous source for QB crashes and errors
                    $qb->add_constraint($storage['location'], $constraint['constraint'], $constraint['value']);
                }
            }
        }

        $initial_groups = $qb->execute();
        
        foreach ($initial_groups as $group)
        {
            $display = true;

            foreach ($php_constraints as $constraint)
            {
                // Run the group through filters
                if ($storage['location'] == 'parameter')
                {
                    if (!$this->_check_parameter($group, $storage['domain'], $constraint['property'], $constraint))
                    {
                        $display = false;
                    }
                }
                elseif ($storage['location'] == 'configuration')
                {
                    if (!$this->_check_parameter($group, $storage['domain'], $storage['name'], $constraint))
                    {
                        $display = false;
                    }
                }
            }

            /*
            // Check that the schema is correct
            if ($group->get_parameter('midcom.helper.datamanager2', 'schema_name') != $this->_request_data['schema'])
            {
                $display = false;
            }
            */

            if ($display)
            {
                $filtered_groups[] = $group;
            }
        }
        
        return $filtered_groups;
    }

    /**
     * Renders the Group Index. If alphabetic indexing is enabled, the filter char
     * is extracted and set so that the index is limited accordingly. (Defaults to 'A'
     * in case no filter is specified.)
     */
    function _handler_search($handler_id, $args, &$data)
    {
        $this->_prepare_request_data();
            
        if (   array_key_exists('net_nemein_organizations_search', $_REQUEST)
            && is_array($_REQUEST['net_nemein_organizations_search']))
        {
            // Normalize the constraints
            $data['search_constraints'] = $this->_normalize_search($_REQUEST['net_nemein_organizations_search']);
    
            $this->_groups = $this->_search_groups($data['search_constraints']);
        }
        
        $this->_load_datamanager();

        $_MIDCOM->set_26_request_metadata(time(), $this->_topic->guid);
        $_MIDCOM->set_pagetitle($this->_topic->extra);

        return true;
    }

    /**
     * Renders the Group Index.
     */
    function _show_search($handler_id, &$data)
    {
        if ($this->_groups)
        {
            midcom_show_style('show-index-header');
            
            midcom_show_style('show-search-form');

            $current_col = 0;
            $max_cols = (int) $this->_config->get('groups_in_row');
            if ($max_cols < 1)
            {
                $max_cols = 3;
            }
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

            foreach ($this->_groups as $group)
            {
                // Finalize the request data
                $this->_group = $group;
                $this->_datamanager->set_storage($this->_group);
                $url = net_nemein_organizations_viewer::get_url($this->_group);
                $data['view_url'] = "{$prefix}{$url}";

                if ($current_col == 0)
                {
                    midcom_show_style('show-index-row-header');
                }

                midcom_show_style('show-index-item');

                $current_col++;

                if ($current_col >= $max_cols)
                {
                    midcom_show_style('show-index-row-footer');
                    $current_col = 0;
                }
            }

            // Finish the table if neccessary
            if ($current_col > 0)
            {
                for (; $current_col < $max_cols; $current_col++)
                {
                    midcom_show_style('show-index-item-empty');
                }
                midcom_show_style('show-index-row-footer');
            }

            midcom_show_style('show-index-footer');
        }
        else
        {
            midcom_show_style('show-search-empty');
        }
    }
}
