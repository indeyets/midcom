<?php
/**
* @package cc.kaktus.todo
* @author The Midgard Project, http://www.midgard-project.org
* @version $Id: viewer.php 3975 2006-09-06 17:36:03Z bergie $
* @copyright The Midgard Project, http://www.midgard-project.org
* @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
*/

/**
 * Viewer class for cc.kaktus.todo.
 *
 * @package cc.kaktus.todo
 */
class cc_kaktus_todo_viewer extends midcom_baseclasses_components_request
{
    /**
     * Constructor for the class
     *
     * @access public
     */
    function cc_kaktus_todo_viewer($topic, $config)
    {
        parent::__construct($topic, $config);

        // Match /
        $this->_request_switch['list_all'] = array
        (
            'handler'       => array ('cc_kaktus_todo_handler_list', 'list'),
        );

        // Match /create/
        $this->_request_switch['create_root_level'] = array
        (
            'handler'       => array ('cc_kaktus_todo_handler_new', 'new'),
            'fixed_args'    => array ('create'),
        );

        // Match /create/<item id>
        $this->_request_switch['create_sub_item'] = array
        (
            'handler'       => array ('cc_kaktus_todo_handler_new', 'new'),
            'fixed_args'    => array ('create'),
            'variable_args' => 1,
        );

        // Match /finished/
        $this->_request_switch['finished'] = array
        (
            'handler'       => array ('cc_kaktus_todo_handler_list', 'finished'),
            'fixed_args'    => array ('finished'),
        );

        // Match /overtime/
        $this->_request_switch['overtime'] = array
        (
            'handler'       => array ('cc_kaktus_todo_handler_list', 'overtime'),
            'fixed_args'    => array ('overtime'),
        );

        // Match /config/
        $this->_request_switch['config'] = array
        (
            'handler'       => array('midcom_core_handler_configdm', 'configdm'),
            'schemadb'      => 'file:/cc/kaktus/todo/config/schemadb_config.inc',
            'schema'        => 'config',
            'fixed_args'    => array('config'),
        );
    }

    /**
     * Populate the elements
     *
     * @access private
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        // Link to a new root level item creation
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_view_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "create/",
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create item'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_event.png',
                )
            );
        }

        return true;
    }
}
?>