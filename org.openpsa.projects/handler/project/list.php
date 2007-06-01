<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: list.php,v 1.2 2006/05/10 16:27:39 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * New project handler
 *
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_project_list extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function org_openpsa_projects_handler_project_list()
    {
        parent::midcom_baseclasses_components_handler();
    }

    function _on_initialize()
    {
        $this->_datamanagers =& $this->_request_data['datamanagers'];
    }

    function _load_project($identifier)
    {
        $project = new org_openpsa_projects_project($identifier);

        if (!is_object($project))
        {
            return false;
        }

        //Fill the customer field to DM
        debug_add("schema before \n===\n" . sprint_r($this->_datamanagers['project']->_layoutdb['default']) . "===\n");
        org_openpsa_helpers_schema_modifier($this->_datamanagers['project'], 'customer', 'widget', 'select', 'default', false);
        org_openpsa_helpers_schema_modifier($this->_datamanagers['project'], 'customer', 'widget_select_choices', org_openpsa_helpers_task_groups($project), 'default', false);
        debug_add("schema after \n===\n" . sprint_r($this->_datamanagers['project']->_layoutdb['default']) . "===\n");

        // Load the project to datamanager
        if (!$this->_datamanagers['project']->init($project))
        {
            return false;
        }
        return $project;
    }


    function _handler_list($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        // QB queries of projects by status
        $this->_request_data['view'] = 'all';
        $this->_request_data['project_list_results'] = array();
        if (count($args) == 1)
        {
            $this->_request_data['view'] = $args[0];
        }

        $this->_view_toolbar->add_item(
            Array(
                MIDCOM_TOOLBAR_URL => '',
                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("back to index"),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_left.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        if ($_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_projects_project'))
        {
            $this->_node_toolbar->add_item(
                Array(
                    MIDCOM_TOOLBAR_URL => 'project/new/',
                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("create project"),
                    MIDCOM_TOOLBAR_HELPTEXT => null,
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',
                    MIDCOM_TOOLBAR_ENABLED => true,
                )
            );
        }

        if (   $this->_request_data['config']->get('list_projects_by_status')
            || $this->_request_data['view'] != 'all')
        {
            // Projects that haven't been started yet
            if (   $this->_request_data['view'] == 'not_started'
                || $this->_request_data['view'] == 'all')
            {
                $this->_request_data['project_list_results']['not_started'] = array();

                debug_add("Instantiating Query Builder for listing not started projects");
                $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_project');
                //$qb->add_constraint('start', '>', time());
                $qb->add_constraint('status', '<', ORG_OPENPSA_TASKSTATUS_STARTED);
                $qb->add_constraint('status', '<>', ORG_OPENPSA_TASKSTATUS_ONHOLD);
                $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECT);

                // Workgroup filtering
                if (   isset($GLOBALS['org_openpsa_core_workgroup_filter'])
                    && !is_null($GLOBALS['org_openpsa_core_workgroup_filter'])
                    && $GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
                {
                    $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
                }

                debug_add("Executing Query Builder");
                $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
                if (   is_array($ret)
                    && count($ret) > 0)
                {
                    foreach ($ret as $project)
                    {
                        $this->_request_data['project_list_results']['not_started'][$project->guid] = $project;
                    }
                }
            }

            // Currently going projects
            if (   $this->_request_data['view'] == 'ongoing'
                || $this->_request_data['view'] == 'all')
            {
                $this->_request_data['project_list_results']['ongoing'] = array();

                debug_add("Instantiating Query Builder for listing ongoing projects");
                $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_project');
                $qb->add_constraint('start', '<', time());
                /*$qb->begin_group('OR');
                    $qb->begin_group('AND');
                        $qb->add_constraint('status', '>=', ORG_OPENPSA_TASKSTATUS_STARTED);
                        $qb->add_constraint('status', '<', ORG_OPENPSA_TASKSTATUS_COMPLETED);
                    $qb->end_group();
                    $qb->add_constraint('status', '>', ORG_OPENPSA_TASKSTATUS_ACCEPTED);
                $qb->end_group();*/
                $qb->add_constraint('status', '>=', ORG_OPENPSA_TASKSTATUS_ACCEPTED);
                $qb->add_constraint('status', '<>', ORG_OPENPSA_TASKSTATUS_ACCEPTED);
                $qb->add_constraint('status', '<>', ORG_OPENPSA_TASKSTATUS_ONHOLD);
                $qb->add_constraint('status', '<', ORG_OPENPSA_TASKSTATUS_COMPLETED);
                $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECT);

                // Workgroup filtering
                if (   isset($GLOBALS['org_openpsa_core_workgroup_filter'])
                    && !is_null($GLOBALS['org_openpsa_core_workgroup_filter'])
                    && $GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
                {
                    $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
                }

                $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
                if (   is_array($ret)
                    && count($ret) > 0)
                {
                    foreach ($ret as $project)
                    {
                        $this->_request_data['project_list_results']['ongoing'][$project->guid] = $project;
                    }
                }
            }

            // Projects that are over time
            if (   $this->_request_data['view'] == 'overtime'
                || $this->_request_data['view'] == 'all')
            {
                $this->_request_data['project_list_results']['overtime'] = array();

                debug_add("Instantiating Query Builder for listing overtime projects");
                $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_project');
                $qb->add_constraint('end', '<', time());
                $qb->add_constraint('status', '<', ORG_OPENPSA_TASKSTATUS_COMPLETED);
                $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECT);

                // Workgroup filtering
                if (   isset($GLOBALS['org_openpsa_core_workgroup_filter'])
                    && !is_null($GLOBALS['org_openpsa_core_workgroup_filter'])
                    && $GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
                {
                    $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
                }

                $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
                if (   is_array($ret)
                    && count($ret) > 0)
                {
                    foreach ($ret as $project)
                    {
                        $this->_request_data['project_list_results']['overtime'][$project->guid] = $project;
                    }
                }
            }

            // Projects that have been completed
            if (   $this->_request_data['view'] == 'completed'
                || $this->_request_data['view'] == 'all')
            {
                $this->_request_data['project_list_results']['completed'] = array();

                debug_add("Instantiating Query Builder for listing finished projects");
                $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_project');
                $qb->add_constraint('status', '=', ORG_OPENPSA_TASKSTATUS_CLOSED);
                $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECT);

                // Workgroup filtering
                if (   isset($GLOBALS['org_openpsa_core_workgroup_filter'])
                    && !is_null($GLOBALS['org_openpsa_core_workgroup_filter'])
                    && $GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
                {
                    $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
                }

                $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
                if (   is_array($ret)
                    && count($ret) > 0)
                {
                    foreach ($ret as $project)
                    {
                        $this->_request_data['project_list_results']['completed'][$project->guid] = $project;
                    }
                }
            }
        }
        else
        {
            // List *all* projects
            $this->_request_data['project_list_results']['all'] = array();

            debug_add("Instantiating Query Builder for listing all projects");
            $qb = $_MIDCOM->dbfactory->new_query_builder('org_openpsa_projects_project');
            $qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_PROJECT);

            // Workgroup filtering
            if (   isset($GLOBALS['org_openpsa_core_workgroup_filter'])
                && !is_null($GLOBALS['org_openpsa_core_workgroup_filter'])
                && $GLOBALS['org_openpsa_core_workgroup_filter'] != 'all')
            {
                $qb->add_constraint('orgOpenpsaOwnerWg', '=', $GLOBALS['org_openpsa_core_workgroup_filter']);
            }

            debug_add("Executing Query Builder");
            $ret = $_MIDCOM->dbfactory->exec_query_builder($qb);
            if (   is_array($ret)
                && count($ret) > 0)
            {
                foreach ($ret as $project)
                {
                    $this->_request_data['project_list_results']['all'][$project->guid] = $project;
                }
            }
        }
        return true;
    }

    function _show_list($handler_id, &$data)
    {
        // Locate Contacts node for linking
        $this->_request_data['contacts_node'] = midcom_helper_find_node_by_component('org.openpsa.contacts');

        if ($this->_request_data['view'] == 'all')
        {
            // The main listing view, list summary of each status
            foreach ($this->_request_data['project_list_results'] as $status => $results)
            {
                $this->_request_data['project_list_status'] = $status;
                $this->_request_data['project_list_items'] = $results;

                if (!$this->_request_data['config']->get('list_projects_by_status'))
                {
                    midcom_show_style("show-project-list-status-header");
                    foreach ($this->_request_data['project_list_results'][$this->_request_data['view']] as $guid => $project)
                    {
                        $this->_request_data['project'] = $this->_load_project($guid);
                        $this->_request_data['project_dm'] = $this->_datamanagers['project']->get_array();
                        midcom_show_style("show-project-list-status-item");
                    }
                    midcom_show_style("show-project-list-status-footer");
                }
                else
                {
                    midcom_show_style("show-project-list-status-summary");
                }
            }
        }
        else
        {
            // Listing of one status, show verbose output
            midcom_show_style("show-project-list-status-header");
            foreach ($this->_request_data['project_list_results'][$this->_request_data['view']] as $guid => $project)
            {
                $this->_request_data['project'] = $this->_load_project($guid);
                $this->_request_data['project_dm'] = $this->_datamanagers['project']->get_array();
                midcom_show_style("show-project-list-status-item");
            }
            midcom_show_style("show-project-list-status-footer");
        }
    }
}
?>