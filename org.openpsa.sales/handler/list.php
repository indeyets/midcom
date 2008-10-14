<?php
/**
 * @package org.openpsa.sales
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: list.php,v 1.6 2006/07/17 14:57:13 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * salesproject list handler
 *
 * @package org.openpsa.sales
 */
class org_openpsa_sales_handler_list extends midcom_baseclasses_components_handler
{
    var $datamanagers = array();

    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        if (!isset($this->_datamanagers['salesproject']))
        {
            $this->_initialize_datamanager('salesproject', $this->_config->get('schemadb_salesproject'));
        }

        $this->_request_data['salesprojects'] = Array();
        $this->_request_data['salesprojects_map_id_key'] = Array();
        $this->_request_data['salesprojects_dm'] = Array();
        $this->_request_data['customers'] = Array();
        $this->_request_data['owners'] = Array();

        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'salesproject/new/',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('create salesproject'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_people.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_sales_salesproject_dba'),
            )
        );

        $qb = org_openpsa_sales_salesproject_dba::new_query_builder();

        if ($handler_id == 'list_active')
        {
            $qb->add_constraint('status', '=', ORG_OPENPSA_SALESPROJECTSTATUS_ACTIVE);
            $data['list_title'] = 'active';
        }
        else
        {
            $data['list_title'] = $args[0];
            switch ($args[0])
            {
                case 'won':
                    $this->_component_data['active_leaf'] = "{$this->_topic->id}:deliverable_won";
                    $qb->add_constraint('status', '=', ORG_OPENPSA_SALESPROJECTSTATUS_WON);
                    break;

                case 'canceled':
                    $qb->add_constraint('status', '=', ORG_OPENPSA_SALESPROJECTSTATUS_CANCELED);
                    break;

                case 'lost':
                    $qb->add_constraint('status', '=', ORG_OPENPSA_SALESPROJECTSTATUS_LOST);
                    break;

                default:
                    return false;
            }
        }

        // TODO: Enable listing sales projects of others
        //$qb->add_constraint('owner', '=', $_MIDGARD['user']);
        $salesprojects = $qb->execute();
        foreach ($salesprojects as $salesproject)
        {
            $key = count($this->_request_data['salesprojects']);
            $this->_request_data['salesprojects_map_id_key'][$salesproject->id] = $key;
            $dm_customers = array(0 => '');
            if ($salesproject->customer)
            {
                // Cache the customer, we need it later too
                if (!isset($this->_request_data['customers'][$salesproject->customer]))
                {
                    $this->_request_data['customers'][$salesproject->customer] = new org_openpsa_contacts_group($salesproject->customer);
                }
                $mode = 'id';
                // Add it to the options array for DM mucking to be done later in this loop.
                org_openpsa_helpers__list::task_groups_put($dm_customers, $mode, $this->_request_data['customers'][$salesproject->customer]);
            }
            if (!isset($this->_request_data['owners'][$salesproject->owner]))
            {
                $this->_request_data['owners'][$salesproject->owner] = new org_openpsa_contacts_person($salesproject->owner);
            }

            // Populate previous/next actions in the project
            $salesproject->get_actions();

            $this->_request_data['salesprojects'][$key] = $salesproject;

            // Muck the customers to DM
            org_openpsa_helpers::schema_modifier($this->_datamanagers['salesproject'], 'customer', 'widget', 'select', 'default', false);
            org_openpsa_helpers::schema_modifier($this->_datamanagers['salesproject'], 'customer', 'widget_select_choices', $dm_customers, 'default', false);
            if (!$this->_datamanagers['salesproject']->init($salesproject))
            {
                // DM failure, abort
                return false;
            }
            $this->_request_data['salesprojects_dm'][$key] = $this->_datamanagers['salesproject']->get_array();
            debug_add("salesproject_dm[{$key}]\n===\n" . org_openpsa_helpers::sprint_r($this->_request_data['salesprojects_dm'][$key]) . "===\n");
        }

        // TODO: Filtering

        // Sorting
        if (isset($_REQUEST['org_openpsa_sales_sort_by']))
        {
            switch($_REQUEST['org_openpsa_sales_sort_by'])
            {
                case 'title':
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_title');
                    break;
                case 'value':
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_value');
                    break;
                case 'profit':
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_profit');
                    break;
                case 'weighted_value':
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_weighted_value');
                    break;
                case 'close_est':
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_close_est');
                    break;
                case 'probability':
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_probability');
                    break;
                case 'prev_action':
                    $GLOBALS['org_openpsa_sales_project_map'] =& $this->_request_data['salesprojects_map_id_key'];
                    $GLOBALS['org_openpsa_sales_project_cache'] =& $this->_request_data['salesprojects'];
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_prev_action');
                    break;
                case 'next_action':
                    $GLOBALS['org_openpsa_sales_project_map'] =& $this->_request_data['salesprojects_map_id_key'];
                    $GLOBALS['org_openpsa_sales_project_cache'] =& $this->_request_data['salesprojects'];
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_next_action');
                    break;
                case 'customer':
                    $GLOBALS['org_openpsa_sales_customer_cache'] =& $this->_request_data['customers'];
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_customer');
                    break;
                case 'owner':
                    $GLOBALS['org_openpsa_sales_owner_cache'] =& $this->_request_data['owners'];
                    uasort($this->_request_data['salesprojects_dm'], 'org_openpsa_sales_sort_by_owner');
                    break;
                default:
                    debug_add("Sort {$_REQUEST['org_openpsa_sales_sort_by']} is not supported", MIDCOM_LOG_WARN);
                    break;
            }
        }
        if (   isset($_REQUEST['org_openpsa_sales_sort_order'])
            && strtolower($_REQUEST['org_openpsa_sales_sort_order']) == 'asc')
        {
            $this->_request_data['salesprojects_dm'] = array_reverse($this->_request_data['salesprojects_dm'], true);
        }

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_list($handler_id, &$data)
    {
        if (count($this->_request_data['salesprojects']) > 0)
        {
            // Locate Contacts node for linking
            $this->_request_data['contacts_node'] = midcom_helper_find_node_by_component('org.openpsa.contacts');

            midcom_show_style('show-list-header');
            $this->_request_data['even'] = false;
            foreach ($this->_request_data['salesprojects_dm'] as $key => $project_dm)
            {
                $project =& $this->_request_data['salesprojects'][$key];
                if ($project->customer)
                {
                    $this->_request_data['customer'] =& $this->_request_data['customers'][$project->customer];
                }
                else
                {
                    $this->_request_data['customer'] = false;
                }
                if ($project->owner)
                {
                    $this->_request_data['owner'] =& $this->_request_data['owners'][$project->owner];
                }
                else
                {
                    $this->_request_data['owner'] = false;
                }
                $this->_request_data['salesproject'] =& $project;
                $this->_request_data['salesproject_dmdata'] =& $project_dm;
                midcom_show_style('show-list-item');

                if ($this->_request_data['even'])
                {
                    $this->_request_data['even'] = false;
                }
                else
                {
                    $this->_request_data['even'] = true;
                }
            }
            midcom_show_style('show-list-footer');
        }
    }

    function _initialize_datamanager($type, $schemadb_snippet)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        // Load schema database snippet or file
        debug_add("Loading Schema Database", MIDCOM_LOG_DEBUG);
        $schemadb_contents = midcom_get_snippet_content($schemadb_snippet);
        eval("\$schemadb = Array ( {$schemadb_contents} );");
        // Initialize the datamanager with the schema
        $this->_datamanagers[$type] = new midcom_helper_datamanager($schemadb);

        if (!$this->_datamanagers[$type])
        {
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Datamanager could not be instantiated.");
            // This will exit.
        }
        debug_pop();
    }


}

?>