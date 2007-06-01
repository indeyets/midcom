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
    /**
     * The project to display
     *
     * @var org_openpsa_projects_project
     * @access private
     */
    var $_project = null;
    
    /**
     * The Datamanager of the project to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_datamanager = null;
    
    
    function org_openpsa_projects_handler_project_view()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['project'] =& $this->_project;
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }
    
    /**
     * Internal helper, loads the datamanager for the current article. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb_project_dm2']);

        if (   ! $this->_datamanager
            || ! $this->_datamanager->autoset_storage($this->_project))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance for project {$this->_project->id}.");
            // This will exit.
        }
    }
    
    function _handler_view($handler_id, $args, &$data)
    {
        $this->_project = new org_openpsa_projects_project($args[0]);
        if (!$this->_project)
        {
            return false;
        }
        
        $this->_load_datamanager();
        $this->_prepare_request_data();
        $_MIDCOM->set_pagetitle($this->_project->title);

        // Add toolbar items
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "project/{$this->_project->guid}/edit.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('edit'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/edit.png',
                MIDCOM_TOOLBAR_ACCESSKEY => 'e',

            )
        );
        $this->_view_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "task/new/project/{$this->_project->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create task'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_project->can_do('midgard:update'),
            )
        );

        $_MIDCOM->bind_view_to_object($this->_project, $this->_datamanager->schema->name);

        $breadcrumb = org_openpsa_projects_viewer::update_breadcrumb_line($this->_project);
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);
        
        $task_qb = org_openpsa_projects_project::new_query_builder();
        $task_qb->add_constraint('orgOpenpsaObtype', '=', ORG_OPENPSA_OBTYPE_TASK);
        $task_qb->add_constraint('up', '=', $this->_project->id);
        $task_qb->add_order('end');
        $data['tasks'] = $task_qb->execute();

        return true;
    }

    function _show_view($handler_id, &$data)
    {
        $this->_request_data['view_project'] = $this->_datamanager->get_content_html();
        midcom_show_style('show-project');
    }
}
?>