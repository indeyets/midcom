<?php
/**
 * @package org.openpsa.projects
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id: frontpage.php,v 1.1 2006/05/10 13:00:45 rambo Exp $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Projects index handler
 * 
 * @package org.openpsa.projects
 */
class org_openpsa_projects_handler_frontpage extends midcom_baseclasses_components_handler
{
    function org_openpsa_projects_handler_frontpage() 
    {
        parent::midcom_baseclasses_components_handler();       
    }

    function _handler_frontpage($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        
        // FIXME: Could be great to make d_l do this
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.helpers/ajax_tableform.js");
        $_MIDCOM->add_jsfile(MIDCOM_STATIC_URL."/org.openpsa.projects/hours_widget.js");
        $_MIDCOM->add_link_head(array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/org.openpsa.projects/hours_widget.css",
            )
        );

        $this->_node_toolbar->add_item(
            Array(                MIDCOM_TOOLBAR_URL => 'project/new/',                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("create project"),                MIDCOM_TOOLBAR_HELPTEXT => null,                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-dir.png',                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_projects_project'),            )
        );
        
        $this->_node_toolbar->add_item(
            Array(                MIDCOM_TOOLBAR_URL => 'task/new/',                MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get("create task"),                MIDCOM_TOOLBAR_HELPTEXT => null,                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new_task.png',                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'org_openpsa_projects_task'),            )
        );
        
        return true;
    }
    
    function _show_frontpage($handler_id, &$data)
    {
        midcom_show_style("show-frontpage");
    }
}
?>