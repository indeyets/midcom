<?php
/**
 * @package org.maemo.socialnews
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_viewer extends midcom_baseclasses_components_request
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
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/org/maemo/socialnews/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('org_maemo_socialnews_handler_index', 'index'),
        );

        // Handle /main
        $this->_request_switch['main'] = array
        (
            'handler' => Array('org_maemo_socialnews_handler_index', 'index'),
            'fixed_args' => Array('main'),
        );
        
        // Handle /best
        $this->_request_switch['bestof'] = array
        (
            'handler' => Array('org_maemo_socialnews_handler_bestof', 'index'),
            'fixed_args' => Array('best'),
        );
        
        // Handle /latest
        $this->_request_switch['latest'] = array
        (
            'handler' => Array('org_maemo_socialnews_handler_latest', 'latest'),
            'fixed_args' => Array('latest'),
        );
        
        if ($this->_config->get('rss_archive_enable'))
        {
            // Handle /rss.xml
            $this->_request_switch['rss20'] = array
            (
                'handler' => Array('org_maemo_socialnews_handler_feed', 'feed'),
                'fixed_args' => Array('rss.xml'),
            );
        }
        
        // Handle /items.xml
        $this->_request_switch['rss20_items'] = array
        (
            'handler' => Array('org_maemo_socialnews_handler_index', 'index'),
            'fixed_args' => Array('items.xml'),
        );
        // Handle /my_items.xml
        $this->_request_switch['rss20_items_personal'] = array
        (
            'handler' => Array('org_maemo_socialnews_handler_index', 'index'),
            'fixed_args' => Array('my_items.xml'),
        );       
        
        // The Archive
        $this->_request_switch['archive-welcome'] = Array
        (
            'handler' => Array('org_maemo_socialnews_handler_archive', 'welcome'),
            'fixed_args' => Array('archive'),
        );
        $this->_request_switch['archive-year'] = Array
        (
            'handler' => Array('org_maemo_socialnews_handler_archive', 'list'),
            'fixed_args' => Array('archive', 'year'),
            'variable_args' => 1,
        );
        $this->_request_switch['archive-month'] = Array
        (
            'handler' => Array('org_maemo_socialnews_handler_archive', 'list'),
            'fixed_args' => Array('archive', 'month'),
            'variable_args' => 2,
        );
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {   
        /*
        if ($this->_content_topic->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "create/{$name}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_request_data['schemadb'][$name]->description
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                ));
            }
        }
        */
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'config/',
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
        $this->_populate_node_toolbar();

        if ($this->_config->get('rss_archive_enable'))
        {
            $_MIDCOM->add_link_head
            (
                array
                (
                    'title' => $this->_l10n->get('daily digest of top news items'),
                    'rel'   => 'alternate',
                    'type'  => 'application/rss+xml',
                    'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rss.xml',
                )
            );
        }
        
        $_MIDCOM->add_link_head
        (
            array
            (
                'title' => $this->_l10n->get('top news items'),
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'items.xml',
            )
        );
        
        if (   $this->_config->get('attention_enable')
            && $_MIDCOM->auth->user)
        {
            $_MIDCOM->add_link_head
            (
                array
                (
                    'title' => $this->_l10n->get('personalized top news items'),
                    'rel'   => 'alternate',
                    'type'  => 'application/rss+xml',
                    'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'my_items.xml',
                )
            );
        }

        return true;
    }

}

?>