<?php

/**
 * @package org.openpsa.projects
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: projects_handler.php,v 1.16 2006/06/06 14:55:35 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.projects project handler and viewer class.
 */
class org_openpsa_reports_projects_handler
{
    var $_datamanagers;
    var $_request_data;
    var $_grouping = 'date';
    var $_valid_groupings = array(
            'date' => true,
            'person' => true,
        );
    
    function org_openpsa_reports_projects_handler(&$datamanagers, &$request_data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_datamanagers = &$datamanagers;
        $this->_request_data = &$request_data;
        debug_add('Loading org.openpsa.projects classes');
        $_MIDCOM->componentloader->load('org.openpsa.projects');
        debug_add('Loading org.openpsa.contacts classes');
        $_MIDCOM->componentloader->load('org.openpsa.contacts');
        debug_pop();
    }


    function _load_query($identifier)
    {
        $query = new org_openpsa_reports_query($identifier);
        
        if (!is_object($query))
        {
            return false;
        }
        
        // Load the project to datamanager
        if (!$this->_datamanagers['projects']->init($query))
        {
            return false;
        }
        return $query;
    }

    function _creation_dm_callback(&$datamanager)
    {
        // This is what Datamanager calls to actually create a person
        $result = array (
            "success" => false,
            "storage" => null,
        );
        
        $query = new org_openpsa_reports_query();
        $stat = $query->create();
        if ($stat)
        {
            $this->_request_data['query'] = new org_openpsa_reports_query($query->id);
            //Debugging
            $result["storage"] =& $this->_request_data['query'];
            $result["success"] = true;
            return $result;
        }
        return null;
    }

        
    function _handler_query_form($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        
        $this->_load_query(null);
        
        if (!$this->_datamanagers['projects']->init_creation_mode("newquery",$this,"_creation_dm_callback"))
        {
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "Failed to initialize datamanager in creation mode for schema 'newquery'.");
            // This will exit   
        }
        
        $this->_request_data['datamanager'] = $this->_datamanagers['projects'];
        
        // Process the form
        switch ($this->_datamanagers['projects']->process_form()) {
            case MIDCOM_DATAMGR_CREATING:
                debug_add('First call within creation mode');
                break;            
            case MIDCOM_DATAMGR_SAVED:
                //Fall-trough intentional
            case MIDCOM_DATAMGR_EDITING:
                debug_add("First time submit, the DM has created an object");
                // Change schema setting
                $this->_request_data['query']->parameter("midcom.helper.datamanager","layout","default");
                
                
                // Relocate to report view
                $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
                debug_pop();
                $_MIDCOM->relocate($prefix . 'projects/' . $this->_request_data['query']->guid() . "/");
                //this will exit
                
            case MIDCOM_DATAMGR_CANCELLED_NONECREATED:
                debug_add('Cancel without anything being created, redirecting to the welcome screen.');
                $_MIDCOM->relocate('');
                // This will exit
            
            case MIDCOM_DATAMGR_CANCELLED:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method MIDCOM_DATAMGR_CANCELLED unknown for creation mode.';
                debug_pop();
                return false;
            
            case MIDCOM_DATAMGR_FAILED:
                //Fall-trough intentional
            case MIDCOM_DATAMGR_CREATEFAILED:
                debug_add('The DM failed critically, see above.');
                $this->errstr = 'The Datamanger failed to process the request, see the Debug Log for details';
                $this->errcode = MIDCOM_ERRCRIT;
                debug_pop();
                return false;
                
            default:
                $this->errcode = MIDCOM_ERRCRIT;
                $this->errstr = 'Method unknown';
                debug_pop();
                return false;
        }
        
        debug_pop();
        return true;
    }
    
    function _show_query_form($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        midcom_show_style("show-projects-query-form");
        
        debug_pop();
        return true;
    }

    /**
     * Convert midcom acl identifier to array of person ids
     */
    function _expand_resource($resource_id, $ret=array())
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('Got resource_id: ' . $resource_id);
        $dba_obj =& $_MIDCOM->auth->get_assignee($resource_id);
        //debug_add("got dba_obj:\n===\n" . sprint_r($dba_obj) . "===\n");
        org_openpsa_reports_projects_handler::_verify_cache('users', $this->_request_data);
        switch (get_class($dba_obj))
        {
            case 'midcom_core_group':
            case 'midcom_core_group_midgard':
            case 'midcom_core_group_virtual':
                $members = $dba_obj->list_members();
                if (is_array($members))
                {
                    foreach ($members as $core_user)
                    {
                        $user_obj = $core_user->get_storage();
                        debug_add(sprintf('Adding user %s (id: %s)', $core_user->name, $user_obj->id));
                        $ret[] = $user_obj->id;
                        $this->_request_data['object_cache'][$user_obj->guid] = $user_obj;
                        $this->_request_data['object_cache']['users'][$user_obj->id] =& $this->_request_data['object_cache'][$user_obj->guid];
                    }
                }
            break;
            case 'midcom_core_user':
                $user_obj = $dba_obj->get_storage();
                debug_add(sprintf('Adding user %s (id: %s)', $dba_obj->name, $user_obj->id));
                $ret[] = $user_obj->id;
                $this->_request_data['object_cache'][$user_obj->guid] = $user_obj;
                $this->_request_data['object_cache']['users'][$user_obj->id] =& $this->_request_data['object_cache'][$user_obj->guid];
            break;
            default:
                debug_add('Got unrecognized class for dba_obj: ' . get_class($dba_obj), MIDCOM_LOG_WARN);
            break;
        }
        debug_pop();
        return $ret;
    }

    /**
     * Get array of IDs of all tasks in subtree
     */
    function _expand_task($task, $ret = array())
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //When recursing we get object, otherwise GUID
        if (!is_object($task))
        {
            $task = new org_openpsa_projects_task($task);
        }
        //Something went seriously wrong, abort as cleanly as possible
        if (!is_object($task))
        {
            debug_add('Could not get task object, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return $ret;
        }
        
        org_openpsa_reports_projects_handler::_verify_cache('tasks', $this->_request_data);
        $this->_request_data['object_cache'][$task->guid] = $task;
        $this->_request_data['object_cache']['tasks'][$task->id] =& $this->_request_data['object_cache'][$task->guid];
        
        //Add current ID
        debug_add(sprintf('Adding task % (id: %s)', $task->title, $task->id));
        $ret[] = $task->id;

        //Get list of children and recurse
        //We pop already here due to recursion
        debug_add('Checking for childs & recursing them');
        debug_pop();
        $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_task');
        $qb->add_constraint('up', '=', $task->id);
        $results = $_MIDCOM->dbfactory->exec_query_builder($qb);
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
        $qb_hr = org_openpsa_projects_hour_report::new_query_builder();
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

    function _handler_report_generator_get($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        if (   !array_key_exists('org_openpsa_reports_query_data', $_REQUEST)
            || !is_array($_REQUEST['org_openpsa_reports_query_data']))
        {
            debug_add('query data not present or invalid', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }

        // NOTE: This array must be an same format as we get from DM get_array() method
        $this->_request_data['query_data'] = $_REQUEST['org_openpsa_reports_query_data'];
        $this->_request_data['filename'] = 'get';
        
        $this->_handler_report_generator_style();
        
        debug_pop();
        return true;
    }

    function _show_report_generator_get($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $this->_show_report_generator($handler_id, $data);
        debug_pop();
        return;
    }

    function _handler_report_generator($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        
        debug_add('Loading query object '.$args[0]);
        $this->_request_data['query'] = $this->_load_query($args[0]);
        if ($this->_request_data['query'] === false)
        {
            debug_add('Could not load query', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        
        if (   !isset($args[1])
            || empty($args[1]))
        {
            debug_add('Filename part not specified in URL, generating');
            //We do not have filename in URL, generate one and redirect
            /* It seesm created is returned as timestamp again
            debug_add("Generating timestamp from {$this->_request_data['query']->created}");
            $timestamp = strtotime($this->_request_data['query']->created);
            */
            $timestamp = $this->_request_data['query']->created;
            if (!$timestamp)
            {
                $timestamp = time();
            }
            $filename = date('Y_m_d', $timestamp);
            if ($this->_request_data['query']->title)
            {
                $filename .= '_' . preg_replace('/[^a-z0-9-]/i', '_', strtolower($this->_request_data['query']->title));
            }
            $filename .= $this->_request_data['query']->extension;
            debug_add('Generated filename: ' . $filename);
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            debug_pop();
            $_MIDCOM->relocate($prefix . 'projects/' . $this->_request_data['query']->guid() . '/' . $filename);
            //this will exit
        }
        $this->_request_data['filename'] = $args[1];

        //Get DM schema data to array
        $this->_request_data['query_data'] = $this->_datamanagers['projects']->get_array();

        $this->_handler_report_generator_style();
        

        debug_pop();
        return true;
    }
    
    function _handler_report_generator_style()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        //Handle style
        if (empty($this->_request_data['query_data']['style']))
        {
            debug_add('Empty style definition encountered, forcing builtin:basic');
            $this->_request_data['query_data']['style'] = 'builtin:basic';
        }
        if (!preg_match('/^builtin:(.+)/', $this->_request_data['query_data']['style']))
        {
            debug_add("appending '{$this->_request_data['query_data']['style']}' to substyle path");
            $_MIDCOM->substyle_append($this->_request_data['query_data']['style']);
        }

        //TODO: Check if we're inside DL if so do not force mimetype
        if (   !isset($this->_request_data['query_data']['skip_html_headings'])
            || empty($this->_request_data['query_data']['skip_html_headings']))
        {
            //Skip normal style, and force content type based on query data.
            $_MIDCOM->skip_page_style = true;
            debug_add('Forcing content type: ' . $this->_request_data['query_data']['mimetype']);
            $_MIDCOM->cache->content->content_type($this->_request_data['query_data']['mimetype']);
        }

        debug_pop();
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
        
        org_openpsa_reports_projects_handler::_verify_cache('hours', $this->_request_data);
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
            $row['task'] =& org_openpsa_reports_projects_handler::_get_cache('tasks', $this->_request_data['object_cache'][$hour->guid]->task, $this->_request_data);
            $row['person'] =& org_openpsa_reports_projects_handler::_get_cache('users', $this->_request_data['object_cache'][$hour->guid]->person, $this->_request_data);
            
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
                $got =& $this->_get_report_group($matching, $sort, $title, &$rows[$k], $recursed+1);
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
    
    function _verify_cache($key, &$request_data)
    {
        if (   !array_key_exists('object_cache', $request_data)
            || !is_array($request_data['object_cache']))
        {
            $request_data['object_cache'] = array();
        }
        if ($key !== NULL)
        {
            if (   !array_key_exists($key, $request_data['object_cache'])
                || !is_array($request_data['object_cache'][$key]))
            {
                $request_data['object_cache'][$key] = array();
            }
        }
    }
    
    function &_get_cache($type, $id, &$request_data)
    {
        org_openpsa_reports_projects_handler::_verify_cache($type, $request_data);
        if (!array_key_exists($id, $request_data['object_cache'][$type]))
        {
            switch ($type)
            {
                case 'users':
                    $core_user = new midcom_core_user($id);
                    $obj = $core_user->get_storage();
                break;
                case 'tasks':
                    $obj = new org_openpsa_projects_task($id);
                break;
                case 'groups':
                    $obj = new org_openpsa_contacts_group($id);
                break;
            }
            $request_data['object_cache'][$obj->guid] = $obj;
            $request_data['object_cache'][$type][$obj->id] =& $request_data['object_cache'][$obj->guid];
        }
        else
        {
            $obj =& $request_data['object_cache'][$type][$id];
        }
        //debug_add("returning reference to object\n===\n" . sprint_r($request_data['object_cache'][$type][$obj->id]) . "===\n");
        return $request_data['object_cache'][$type][$obj->id];
    }
    
    function _show_report_generator($handler_id, &$data)
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
        debug_add("query data before mangle:\n===\n" . sprint_r($this->_request_data['query_data']) . "===\n");
        debug_add("calling midcom_show_style('report{$bpr}-mangle-query') to mangle the query data as neccessary");
        midcom_show_style("report{$bpr}-mangle-query");
        debug_add("query data after mangle:\n===\n" . sprint_r($this->_request_data['query_data']) . "===\n");

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
            midcom_show_style("report{$bpr}-noresults");
            return true;
        }
        
        //Start actual display
        
        //Indented to make style flow clearer
        midcom_show_style("report{$bpr}-start");
            midcom_show_style("report{$bpr}-header");
                $this->_show_report_generator_group(&$this->_request_data['report']['rows'], $bpr);
            midcom_show_style("report{$bpr}-totals");
            midcom_show_style("report{$bpr}-footer");
        midcom_show_style("report{$bpr}-end");
        
        debug_pop();
        return true;
    }
    
    function _show_report_generator_group(&$data, $bpr, $level=0)
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
                midcom_show_style("report{$bpr}-group-start");
                    midcom_show_style("report{$bpr}-group-header");
                        $this->_show_report_generator_group(&$row['rows'], $bpr, $level+1);
                    midcom_show_style("report{$bpr}-group-totals");
                    midcom_show_style("report{$bpr}-group-footer");
                midcom_show_style("report{$bpr}-group-end");
            }
            else
            {
                midcom_show_style("report{$bpr}-item");
            }
        }
        debug_pop();
    }
}

?>