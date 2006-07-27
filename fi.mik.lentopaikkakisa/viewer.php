<?php

/**
 * @package fi.mik.lentopaikkakisa
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Flight competition site interface class.
 *
 * @package fi.mik.lentopaikkakisa
 */

class fi_mik_lentopaikkakisa_viewer extends midcom_baseclasses_components_request
{

    function fi_mik_lentopaikkakisa_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
        
        // Match /
        $this->_request_switch['index'] = array(
            'handler' => 'index'
        );

        // Match /xml/
        $this->_request_switch['xml'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_download', 'xml'),
            'fixed_args' => Array('xml'),
        );

        // Match /csv/
        $this->_request_switch['csv'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_download', 'csv'),
            'fixed_args' => Array('csv'),
        );
        
        // Match /report/
        $this->_request_switch['report'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_report', 'new'),
            'fixed_args' => Array('report'),
        );

        // Match /score/organization
        $this->_request_switch['score_organization'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_score', 'organization'),
            'fixed_args' => Array('score', 'organization'),
        );

        // Match /score/pilot
        $this->_request_switch['score_pilot'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_score', 'pilot'),
            'fixed_args' => Array('score', 'pilot'),
        );
        
        // Match /manage/
        $this->_request_switch['manage_list'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_manage', 'list'),
            'fixed_args' => Array('manage'),
        );
        
        // Match /manage/
        $this->_request_switch['manage_delete'] = Array
        (
            'handler' => Array('fi_mik_lentopaikkakisa_handler_manage', 'delete'),
            'fixed_args' => Array('manage', 'delete'),
            'variable_args' => 1,
        );
    }
    
    function _handler_index($handler_id, $args, &$data)
    {
        $this->_request_data['node'] =& $this->_topic;
        
        $this->_node_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => 'report.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('report flight'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                MIDCOM_TOOLBAR_ENABLED => $_MIDCOM->auth->can_user_do('midgard:create', null, 'fi_mik_lentopaikkakisa_report_dba'),
            )
        );

        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => 'xml/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('get xml'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => 'csv/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('get csv'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                MIDCOM_TOOLBAR_ENABLED => true,
            )
        );

        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => 'manage/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('manage reports'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:delete'),
            )
        );
        
        $qb = fi_mik_lentopaikkakisa_report_dba::new_query_builder();
        $qb->add_order('created', 'DESC');
        $qb->set_limit($this->_config->get('show_latest'));
        $this->_request_data['latest'] = $qb->execute();
         
        return true;
    }
    
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('view-index');
    }
}
?>