<?php
/**
 * @package net.nehmer.comments
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comments site interface class
 *
 * See the various handler classes for details.
 *
 * @package net.nehmer.comments
 */
class net_nehmer_comments_viewer extends midcom_baseclasses_components_request
{
    function __construct($topic, $config)
    {
        parent::__construct($topic, $config);
    }

    function _on_initialize()
    {
        // Generic and personal welcome pages
        $this->_request_switch['admin-welcome'] = Array
        (
            'handler' => Array('net_nehmer_comments_handler_admin', 'welcome'),
        );
        $this->_request_switch['view-comments'] = Array
        (
            'handler' => Array('net_nehmer_comments_handler_view', 'comments'),
            'fixed_args' => Array('comment'),
            'variable_args' => 1,
        );
        $this->_request_switch['view-comments-nonempty'] = Array
        (
            'handler' => Array('net_nehmer_comments_handler_view', 'comments'),
            'fixed_args' => Array('comment-nonempty'),
            'variable_args' => 1,
        );
        $this->_request_switch['view-comments-custom'] = Array
        (
            'handler' => Array('net_nehmer_comments_handler_view', 'comments'),
            'fixed_args' => Array('comment'),
            'variable_args' => 2,
        );
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nehmer/comments/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );
        // Match /moderate/
/*        $this->_request_switch['moderate'] = Array
        (
            'handler' => Array('net_nehmer_comments_handler_moderate', 'moderate'),
            'fixed_args' => Array('moderate')
        );*/
        // Match /moderate/<comment_status_to_moderate>
        $this->_request_switch['moderate'] = Array
        (
            'handler' => Array('net_nehmer_comments_handler_admin', 'moderate'),
            'fixed_args' => Array('moderate'),
            'variable_args' => 1,
        );
        // Match /report/<comment guid>
        $this->_request_switch['report'] = Array
        (
            'handler' => Array('net_nehmer_comments_handler_moderate', 'report'),
            'fixed_args' => Array('report'),
            'variable_args' => 1,
        );
    }
    
    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config/',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }
        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('net.nehmer.comments:moderation'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'moderate/reported_abuse',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('reported abuse'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('reported abuse helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_news.png',
            ));
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'moderate/abuse',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('abuse'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('abuse helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_news.png',
            ));
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'moderate/junk',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('junk'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('junk helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_news.png',
            ));
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'moderate/latest',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('latest comments'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('latest helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_news.png',
            ));
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'moderate/latest_new',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('only new'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('only new helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_news.png',
            ));
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'moderate/latest_approved',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('only approved'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('only approved helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_news.png',
            ));
        }
    }
    
    /**
     * Generic request startup work:
     * - Populate the Node Toolbar
     */
    function _on_handle($handler, $args)
    {
        $this->_populate_node_toolbar();

        return true;
    }
}

?>