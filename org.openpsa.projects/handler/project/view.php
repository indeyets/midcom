<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: view.php,v 1.1 2006/05/10 13:00:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Project view handler
 * 
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_project_view extends midcom_baseclasses_components_handler
{
    var $_datamanagers;

    function org_openpsa_projects_handler_project_view() 
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

    function _handler_view($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        // Get the requested person object
        $this->_request_data['project'] = $this->_load_project($args[0]);
        if (!$this->_request_data['project'])
        {
            return false;
        }
        
            $_MIDCOM->set_pagetitle($this->_request_data['project']->title);
        
        if ($this->_request_data['project']->forumTopic)
        {
            // Make discussion forum look nicer
            $_MIDCOM->add_link_head(
                array(
                    'rel' => 'stylesheet',
                    'type' => 'text/css',
                    'href' => MIDCOM_STATIC_URL."/net.nemein.discussion/discussion.css",
                )
            );
        }
        
        // Add toolbar items
        if (   count($args) == 1
            && $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['project']))
        {
            $this->_view_toolbar->add_item(
                Array(                    MIDCOM_TOOLBAR_URL => "project/{$this->_request_data['project']->guid}/edit.html",                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("edit"),                    MIDCOM_TOOLBAR_HELPTEXT => null,                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',                    MIDCOM_TOOLBAR_ENABLED => true,                )
            );
        }
        if ($_MIDCOM->auth->can_do('midgard:create', $this->_request_data['project']))
        {
            $this->_node_toolbar->add_item(
                Array(                    MIDCOM_TOOLBAR_URL => "task/new/project/{$this->_request_data['project']->guid}/",                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("create task"),                    MIDCOM_TOOLBAR_HELPTEXT => null,                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',                    MIDCOM_TOOLBAR_ENABLED => true,                )
            );        
        }
        
        // Project news and forum topic creation buttons
        if (   $this->_request_data['config']->get('enable_project_news')
            && !$this->_request_data['project']->newsTopic
            && $_MIDCOM->auth->can_do('midgard:create', $this->_request_data['project_topic'])
            && $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['project']))
        {
            $this->_view_toolbar->add_item(
                Array(                    MIDCOM_TOOLBAR_URL => "project/{$this->_request_data['project']->guid}/create_news.html",                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("create news area"),                    MIDCOM_TOOLBAR_HELPTEXT => null,                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',                    MIDCOM_TOOLBAR_ENABLED => true,                )
            );
        }
        if (   $this->_request_data['config']->get('enable_project_forum')
            && !$this->_request_data['project']->forumTopic
            && $_MIDCOM->auth->can_do('midgard:create', $this->_request_data['project_topic'])
            && $_MIDCOM->auth->can_do('midgard:update', $this->_request_data['project']))
        {
            $this->_view_toolbar->add_item(
                Array(                    MIDCOM_TOOLBAR_URL => "project/{$this->_request_data['project']->guid}/create_forum.html",                    MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n_midcom']->get("create discussion area"),                    MIDCOM_TOOLBAR_HELPTEXT => null,                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',                    MIDCOM_TOOLBAR_ENABLED => true,                )
            );        
        }
        
        $this->_view_toolbar->bind_to($this->_request_data['project']);
                
        return true;
    }
    
    function _show_view($handler_id, &$data)
    {
        $this->_request_data['project_dm']  = $this->_datamanagers['project'];
        midcom_show_style("show-project");
    }
}
?>