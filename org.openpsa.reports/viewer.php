<?php
/**
 * @package org.openpsa.reports
 * @author Nemein Oy http://www.nemein.com/
 * @version $Id: viewer.php,v 1.3 2006/05/29 14:13:13 rambo Exp $
 * @copyright Nemein Oy http://www.nemein.com/
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 */

/**
 * org.openpsa.reports site interface class.
 *
 * Reporting interfaces to various org.openpsa components
 *
 * @package org.openpsa.reports
 */
class org_openpsa_reports_viewer extends midcom_baseclasses_components_request
{
    var $_datamanagers = array();
    var $_projects_handler = null;
    /*
    var $_contacts_handler = null;
    */

    /**
     * Constructor.
     */
    function org_openpsa_reports_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    function _on_initialize()
    {
        // Always run in uncached mode
        $_MIDCOM->cache->content->no_cache();
        debug_push_class(__CLASS__, __FUNCTION__);
        $components = org_openpsa_reports_viewer::available_component_generators();
        foreach ($components as $component => $loc)
        {
            $parts = explode('.', $component);
            $last = array_pop($parts);
            // Match /xxx/get
            $this->_request_switch["{$last}_report_get"] = array(
                'fixed_args' => array($last, 'get'),
                'handler' => array("org_openpsa_reports_handler_{$last}_report", 'generator_get'),
            );

            // Match /xxx/<guid>/<filename>
            $this->_request_switch["{$last}_report_guid_file"] = array
            (
                'fixed_args' => array($last),
                'variable_args' => 2,
                'handler' => array("org_openpsa_reports_handler_{$last}_report", 'generator'),
            );

            // Match /xxx/<guid>
            $this->_request_switch["{$last}_report_guid"] = array
            (
                'fixed_args' => array($last),
                'variable_args' => 1,
                'handler' => array("org_openpsa_reports_handler_{$last}_report", 'generator'),
            );

            // Match /xxx
            $this->_request_switch["{$last}_report"] = array
            (
                'fixed_args' => array($last),
                'handler' => array("org_openpsa_reports_handler_{$last}_report", 'query_form'),
            );
        }

        // Match /csv/<filename>
        $this->_request_switch['csv_export'] = array(
            'fixed_args'    => 'csv',
            'variable_args' => 1,
            'handler'       => 'csv',
        );

        // Match /
        $this->_request_switch['frontpage'] = array(
            'handler' => 'frontpage'
        );

        debug_pop();
        return true;
    }

    function _populate_toolbar()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        /*
        //Add icon for user settings
        $GLOBALS['org_openpsa_core_toolbar']->add_item(Array(
            MIDCOM_TOOLBAR_URL => 'settings.html',
            MIDCOM_TOOLBAR_LABEL => $this->_request_data['l10n']->get('settings'),
            MIDCOM_TOOLBAR_HELPTEXT => null,
            MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
            MIDCOM_TOOLBAR_ENABLED => true
        ));
        */

        debug_pop();
        return true;
    }

    /**
     * The CSV handlers return a posted variable with correct headers
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_csv($handler_id, $args, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        if ( !isset($_POST['org_openpsa_reports_csv']) )
        {
            debug_add('Variable org_openpsa_reports_csv not set in _POST, aborting');
            debug_pop();
            return false;
        }

        //We're outputting CSV
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type('application/csv');

        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_csv($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        echo $_POST['org_openpsa_reports_csv'];

        debug_pop();
        return true;
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_frontpage($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        debug_push_class(__CLASS__, __FUNCTION__);
        $nap = new midcom_helper_nav();
        $data['nap_node'] = $nap->get_node($nap->get_current_node());
        $data['available_components'] = org_openpsa_reports_viewer::available_component_generators();

        debug_pop();
        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_frontpage($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        midcom_show_style('show-frontpage');

        debug_pop();
        return true;
    }

    function available_component_generators()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!isset($GLOBALS['available_component_generators_components_checked']))
        {
            $GLOBALS['available_component_generators_components_checked'] = false;
        }
        $components_checked =& $GLOBALS['available_component_generators_components_checked'];
        if (!isset($GLOBALS['available_component_generators_components']))
        {
            $GLOBALS['available_component_generators_components'] = array
            (
                // TODO: better localization strings
                'org.openpsa.projects' => $_MIDCOM->i18n->get_string('org.openpsa.projects', 'org.openpsa.projects'),
                'org.openpsa.sales' => $_MIDCOM->i18n->get_string('org.openpsa.sales', 'org.openpsa.sales'),
                //'org.openpsa.directmarketing' => $_MIDCOM->i18n->get_string('org.openpsa.directmarketing', 'org.openpsa.reports'),
            );
        }
        $components =& $GLOBALS['available_component_generators_components'];
        if ($components_checked)
        {
            reset($components);
            debug_pop();
            return $components;
        }
        foreach ($components as $component => $loc)
        {
            $node = midcom_helper_find_node_by_component($component);
            if (   empty($node)
                || !$node[MIDCOM_NAV_OBJECT]->can_do('midgard:read'))
            {
                debug_add("node for component '{$component}' not found or accessible");
                unset ($components[$component]);
            }
        }
        $components_checked = true;
        reset($components);
        debug_pop();
        return $components;
    }

}