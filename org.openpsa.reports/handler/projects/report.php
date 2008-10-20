<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: view.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Deliverable reports
 *
 * @package org.openpsa.projects
 */
class org_openpsa_reports_handler_projects_report extends org_openpsa_reports_handler_base
{
    var $_grouping = 'date';
    var $_valid_groupings = array(
            'date' => true,
            'person' => true,
        );

    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    function _on_initialize()
    {
        $this->module = 'projects';
        $this->_initialize_datamanager1($this->module, $this->_config->get('schemadb_queryform_'. $this->module));
        return true;
    }

/*
    function _handler_xxx($handler_id, $args, &$data)
    {

        $this->_component_data['active_leaf'] = "{$this->_topic->id}:generator_projects";

        return true;
    }

    function _show_xxx($handler_id, &$data)
    {
    }
*/

    /**
     * Get array of IDs of all tasks in subtree
     */
    function _expand_task($task, $ret = array())
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //When recursing we get object, otherwise GUID
        if (!is_object($task))
        {
            $task = new org_openpsa_projects_task_dba($task);
        }
        //Something went seriously wrong, abort as cleanly as possible
        if (!is_object($task))
        {
            debug_add('Could not get task object, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return $ret;
        }

        org_openpsa_reports_handler_projects_report::_verify_cache('tasks', $this->_request_data);
        $this->_request_data['object_cache'][$task->guid] = $task;
        $this->_request_data['object_cache']['tasks'][$task->id] =& $this->_request_data['object_cache'][$task->guid];

        //Add current ID
        debug_add(sprintf('Adding task % (id: %s)', $task->title, $task->id));
        $ret[] = $task->id;

        //Get list of children and recurse
        //We pop already here due to recursion
        debug_add('Checking for children & recursing them');
        debug_pop();
        $qb = org_openpsa_projects_task_dba::new_query_builder();
        $qb->add_constraint('up', '=', $task->id);
        $results = $qb->execute();
        if (is_array($results))
        {
            foreach ($results as $child_task)
            {
                $ret = $this->_expand_task($child_task, $ret);
            }
        }
        return $ret;
    }

    /**
     * Makes and executes querybuilder for filtering hour_reports
     */
    function _get_hour_reports()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //Create queries to get data
        $qb_hr = org_openpsa_projects_hour_report_dba::new_query_builder();
        $qb_hr->add_constraint('date', '<=', $this->_request_data['query_data']['end']['timestamp']);
        $qb_hr->add_constraint('date', '>=', $this->_request_data['query_data']['start']['timestamp']);
        if (   array_key_exists('invoiceable_filter', $this->_request_data['query_data'])
            && $this->_request_data['query_data']['invoiceable_filter'] != -1)
        {
            $qb_hr->add_constraint('invoiceable', '=', (int)$this->_request_data['query_data']['invoiceable_filter']);
        }
        debug_add('checking for approved_filter');
        if (array_key_exists('approved_filter', $this->_request_data['query_data']))
        {
            debug_add('approved_filter detected, raw value: ' . $this->_request_data['query_data']['approved_filter']);
            if ($this->_request_data['query_data']['approved_filter'] != -1)
            {
                if ((int)$this->_request_data['query_data']['approved_filter'])
                {
                    debug_add('approved_filter parsed as only approved, adding constraint');
                    $qb_hr->add_constraint('approved', '<>', '0000-00-00 00:00:00');
                }
                else
                {
                    debug_add('approved_filter parsed as only NOT approved, adding constraint');
                    $qb_hr->add_constraint('approved', '=', '0000-00-00 00:00:00');
                }
            }
            else
            {
                debug_add('approved_filter parsed as BOTH, do not add any constraints');
            }
        }
        debug_add('checking for invoiced_filter');
        if (array_key_exists('invoiced_filter', $this->_request_data['query_data']))
        {
            debug_add('invoiced_filter detected, raw value: ' . $this->_request_data['query_data']['invoiced_filter']);
            if ($this->_request_data['query_data']['invoiced_filter'] != -1)
            {
                if ((int)$this->_request_data['query_data']['invoiced_filter'])
                {
                    debug_add('invoiced_filter parsed as only invoiced, adding constraint');
                    $qb_hr->add_constraint('invoiced', '<>', '0000-00-00 00:00:00');
                }
                else
                {
                    debug_add('invoiced_filter parsed as only NOT invoiced, adding constraint');
                    $qb_hr->add_constraint('invoiced', '=', '0000-00-00 00:00:00');
                }
            }
            else
            {
                debug_add('invoiced_filter parsed as BOTH, do not add any constraints');
            }
        }
        if ($this->_request_data['query_data']['resource'] != 'all')
        {
            $this->_request_data['query_data']['resource_expanded'] = $this->_expand_resource($this->_request_data['query_data']['resource']);
            //TODO: Use IN constraint once 1.8 is out
            $qb_hr->begin_group('OR');
            foreach ($this->_request_data['query_data']['resource_expanded'] as $pid)
            {
                $qb_hr->add_constraint('person', '=', $pid);
            }
            $qb_hr->end_group();
        }
        if ($this->_request_data['query_data']['task'] != 'all')
        {
            $tasks = $this->_expand_task($this->_request_data['query_data']['task']);
            //TODO: Use IN constraint once 1.8 is out
            $qb_hr->begin_group('OR');
            foreach ($tasks as $tid)
            {
                $qb_hr->add_constraint('task', '=', $tid);
            }
            $qb_hr->end_group();
        }
        if (   array_key_exists('hour_type_filter', $this->_request_data['query_data'])
            && $this->_request_data['query_data']['hour_type_filter'] != 'builtin:all')
        {
            $qb_hr->add_constraint('reportType', '=', $this->_request_data['query_data']['hour_type_filter']);
        }
        debug_pop();
        return $_MIDCOM->dbfactory->exec_query_builder($qb_hr);
    }

    function _sort_rows_recursive(&$data)
    {
        //debug_add("sorting code:\n===\n" . $this->_code_for_sort_by_key('sort') . "\n===\n");
        /* HACK: usort can't use even static methods so we create an "anonymous" function from code received via method */
        usort($data, create_function('$a,$b', $this->_code_for_sort_by_key('sort')));
        foreach ($data as $row)
        {
            if (   array_key_exists('is_group', $row)
                && $row['is_group'] == true)
            {
                // Is group, recurse
                $this->_sort_rows_recursive($row['rows']);
            }
            else
            {
                // Is normal row, I don't think we want to do anything
            }
        }
    }

    function _code_for_sort_by_key($key)
    {
        return <<<EOF
        \$ap = false;
        \$bp = false;
        if (array_key_exists('$key', \$a))
        {
            \$ap = \$a['$key'];
        }
        if (array_key_exists('$key', \$b))
        {
            \$bp = \$b['$key'];
        }
        switch (true)
        {
            default:
            case is_numeric(\$ap):
                if (\$ap > \$bp)
                {
                    return 1;
                }
                if (\$ap < \$bp)
                {
                    return -1;
                }
                return 0;
                break;
            case is_string(\$ap):
                return strnatcmp(\$ap, \$bp);
                break;
        }
        return 0;
EOF;
    }

    function _analyze_raw_hours()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !array_key_exists('raw_results', $this->_request_data)
            || !array_key_exists('hr', $this->_request_data['raw_results'])
            || !is_array($this->_request_data['raw_results']['hr']))
        {
            debug_add('Hour reports array not found', MIDCOM_LOG_WARN);
            debug_pop();
            return false;
        }
        org_openpsa_reports_handler_projects_report::_verify_cache('hours', $this->_request_data);
        reset($this->_request_data['raw_results']['hr']);
        foreach ($this->_request_data['raw_results']['hr'] as $hour)
        {
            debug_add('processing hour id: ' . $hour->id);
            //TODO: whatever mangling the hour report requires

            //Put the mangled hour to caches
            $this->_request_data['object_cache'][$hour->guid] = $hour;
            $this->_request_data['object_cache']['hours'][$hour->id] = &$this->_request_data['object_cache'][$hour->guid];

            $row = array();
            $row['is_group'] = false;
            $row['hour'] =& $this->_request_data['object_cache'][$hour->guid];
            $row['task'] =& org_openpsa_reports_handler_projects_report::_get_cache('tasks', $this->_request_data['object_cache'][$hour->guid]->task, $this->_request_data);
            $row['person'] =& org_openpsa_reports_handler_projects_report::_get_cache('users', $this->_request_data['object_cache'][$hour->guid]->person, $this->_request_data);

            // Default (should work for almost eveyr grouping) is to sort rows by the hour report date
            $row['sort'] = &$row['hour']->date;
            //Determine our group
            debug_add("grouping is {$this->_grouping}");
            switch ($this->_grouping)
            {
                case 'date':
                    $group =& $this->_get_report_group('date:' . date('Ymd', $row['hour']->date), date('Ymd', $row['hour']->date), strftime('%x', $row['hour']->date));
                break;
                case 'person':
                    $group =& $this->_get_report_group('person:' . $row['person']->guid, $row['person']->rname, $row['person']->rname);
                break;
            }

            //Place data to group
            $group['rows'][] = $row;
            $group['total_hours'] += $hour->hours;

            //Place data to report
            $this->_request_data['report']['total_hours'] += $hour->hours;
        }

        debug_pop();
        return true;
    }

    function &_get_report_group($matching, $sort, $title, $rows = false, $recursed = 0)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$rows)
        {
            debug_add('rows is not defined, using report[rows]');
            $rows =& $this->_request_data['report']['rows'];
        }
        reset($rows);
        foreach ($rows as $k => $row)
        {
            if (   !is_array($row)
                || !array_key_exists('is_group', $row)
                || !$row['is_group'])
            {
                continue;
            }
            if ($row['matching'] === $matching)
            {
                debug_add(sprintf('found match in key "%s", returning it', $k));
                debug_pop();
                return $rows[$k];
            }
            if (    array_key_exists('rows', $row)
                &&  is_array($row['rows']))
            {
                debug_add(sprintf('found subgroup in key "%s", recursing it', $k));
                debug_pop();
                $got =& $this->_get_report_group($matching, $sort, $title, $rows[$k], $recursed+1);
                debug_push_class(__CLASS__, __FUNCTION__);
                if ($got !== false)
                {
                    debug_add('Got result from recurser, returning it');
                    debug_pop();
                    return $got;
                }
            }
        }
        //Could not find group, but since we're inside recursion loop we won't create it yet
        if ($recursed !== 0)
        {
            debug_add('No match and we\'re in recursive mode, returning false');
            debug_pop();
            $x = false;
            return $x;
        }
        else
        {
            debug_add('No match found, creating new group and returning it');
            //Othewise create a new group to the report
            $group = array();
            $group['is_group'] = true;
            $group['matching'] = $matching;
            $group['sort'] = $sort;
            $group['title'] = $title;
            $group['rows'] = array();
            $group['total_hours'] = 0;
            $next_key = count($rows);
            $rows[$next_key] = $group;
            debug_pop();
            return $rows[$next_key];
        }
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_generator($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();

        if (!$this->_generator_load_redirect($args))
        {
            return false;
        }
        $this->_component_data['active_leaf'] = "{$this->_topic->id}:generator_projects";
        $this->_handler_generator_style();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_generator($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        // Builtin style prefix
        if (preg_match('/^builtin:(.+)/', $this->_request_data['query_data']['style'], $matches))
        {
            $bpr = '-' . $matches[1];
            debug_add('Recognized builtin report, style prefix: ' . $bpr);
        }
        else
        {
            debug_add("'{$this->_request_data['query_data']['style']}' not recognized as builtin style");
            $bpr = '';
        }

        //Mangling if report wants to do it (done here to have style context, othewise MidCOM will not like us.
        debug_add("query data before mangle:\n===\n" . org_openpsa_helpers::sprint_r($this->_request_data['query_data']) . "===\n");
        debug_add("calling midcom_show_style('report{$bpr}-mangle-query') to mangle the query data as necessary");
        midcom_show_style("projects_report{$bpr}-mangle-query");
        debug_add("query data after mangle:\n===\n" . org_openpsa_helpers::sprint_r($this->_request_data['query_data']) . "===\n");

        //Handle grouping
        debug_add('checking grouping');
        if (   array_key_exists('grouping', $this->_request_data['query_data'])
            && !empty($this->_request_data['query_data']['grouping']))
        {
            debug_add("checking validity of grouping value '{$this->_request_data['query_data']['grouping']}'");
            if (array_key_exists($this->_request_data['query_data']['grouping'], $this->_valid_groupings))
            {
                debug_add('Setting grouping to: ' . $this->_request_data['query_data']['grouping']);
                $this->_grouping =& $this->_request_data['query_data']['grouping'];
            }
            else
            {
                debug_add(sprinf("\"%s\" is not a valid grouping, keeping default", $this->_request_data['query_data']['grouping']), MIDCOM_LOG_WARN);
            }
        }

        // Put grouping to request data
        $this->_request_data['grouping'] =& $this->_grouping;

        //Get our results
        $results_hr = $this->_get_hour_reports();

        //For debugging and sensible passing of data
        $this->_request_data['raw_results'] = array();
        $this->_request_data['raw_results']['hr'] = $results_hr;
        //TODO: Mileages, expenses

        $this->_request_data['report'] = array();
        $this->_request_data['report']['rows'] = array();
        $this->_request_data['report']['total_hours'] = 0;

        $this->_analyze_raw_hours();

        $this->_sort_rows_recursive($this->_request_data['report']['rows']);



        //TODO: add other report types when supported
        if (   !is_array($this->_request_data['raw_results']['hr'])
            || count($this->_request_data['raw_results']['hr']) == 0)
        {
            midcom_show_style("projects_report{$bpr}-noresults");
            return true;
        }

        //Start actual display

        //Indented to make style flow clearer
        midcom_show_style("projects_report{$bpr}-start");
            midcom_show_style("projects_report{$bpr}-header");
                $this->_show_generator_group($this->_request_data['report']['rows'], $bpr);
            midcom_show_style("projects_report{$bpr}-totals");
            midcom_show_style("projects_report{$bpr}-footer");
        midcom_show_style("projects_report{$bpr}-end");

        debug_pop();
        return true;
    }

    function _show_generator_group(&$data, $bpr, $level=0)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        reset($data);
        foreach ($data as $row)
        {
            $row['level'] = $level;
            $this->_request_data['current_row'] =& $row;
            if (   array_key_exists('is_group', $row)
                && $row['is_group']==true)
            {
                $this->_request_data['current_group'] =& $row;
                //Indented to make style flow clearer
                midcom_show_style("projects_report{$bpr}-group-start");
                    midcom_show_style("projects_report{$bpr}-group-header");
                        $this->_show_generator_group($row['rows'], $bpr, $level + 1);
                    midcom_show_style("projects_report{$bpr}-group-totals");
                    midcom_show_style("projects_report{$bpr}-group-footer");
                midcom_show_style("projects_report{$bpr}-group-end");
            }
            else
            {
                midcom_show_style("projects_report{$bpr}-item");
            }
        }
        debug_pop();
    }


}
?>