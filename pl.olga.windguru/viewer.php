<?php
/**
 * @package pl.olga.windguru
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package pl.olga.windguru
 */
class pl_olga_windguru_viewer extends midcom_baseclasses_components_request
{
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        /**
         * Prepare the request switch, which contains URL handlers for the component
         */
         
        // Handle /config

        $this->_request_switch['config'] = array
        (
            'handler' => array ('midcom_helper_dm2config_config', 'config'),
            'fixed_args' => array ('config'),
        );

        $this->_request_switch['edit'] = Array
        (
            'handler' => Array('pl_olga_windguru_handler_admin', 'edit'),
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['delete'] = Array
        (
            'handler' => Array('pl_olga_windguru_handler_admin', 'delete'),
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );

        $this->_request_switch['create'] = Array
        (
            'handler' => Array('pl_olga_windguru_handler_create', 'create'),
            'fixed_args' => Array('create'),
        );


        $this->_request_switch['view'] = array
        (
            'handler' => array('pl_olga_windguru_handler_view', 'view'),
            'variable_args' => 1,
        );


        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => array('pl_olga_windguru_handler_view', 'index'),
        );
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if ($this->_topic->can_do('midgard:create'))
        {
            $this->_node_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "create.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create spot'),
                        $this->_l10n->get($this->_request_data['schemadb']['default']->description)
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'n',
            ));
        }
   

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config.html',
                    MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                    MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
                )
            );
        }
        
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb'] =
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_populate_node_toolbar();

        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL."/pl.olga.windguru/wgstyle.css",
            )
        );


        return true;
    }


}

?>