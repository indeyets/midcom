<?php
/**
 * @package net.nehmer.publications
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Newsticker / Blog site interface class
 *
 * This is a complete rewrite of the old newsticker the has been made for MidCOM 2.6.
 * It incorporates all of the goodies current MidCOM has to offer and can serve as an
 * example component therefore.
 *
 * @package net.nehmer.publications
 */
class net_nehmer_publications_viewer extends midcom_baseclasses_components_request
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_topic = null;

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
        // *** Prepare the request switch ***

        // Welcome handler, as determined by the configuration
        $this->_request_switch['welcome'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_welcome', $this->_config->get('welcome_handler')),
        );
        $this->_request_switch['welcome-latest'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_welcome', 'latest'),
            'fixed_args' => Array('latest'),
            'variable_args' => 1,
        );
        $this->_request_switch['welcome-latest-category'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_welcome', 'latest'),
            'fixed_args' => Array('category', 'latest'),
            'variable_args' => 2,
        );
        $this->_request_switch['welcome-category'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_welcome', 'index'),
            'fixed_args' => Array('category'),
            'variable_args' => 1,
        );
        $this->_request_switch['welcome-categories'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_welcome', 'categories'),
            'fixed_args' => Array('categories'),
        );


        $this->_request_switch['view'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_view', 'view'),
            'fixed_args' => Array('view'),
            'variable_args' => 1,
        );

        // Various Feeds and their index page
        $this->_request_switch['feed-index'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_feed', 'index'),
            'fixed_args' => Array('feeds'),
        );
        $this->_request_switch['feed-rss2'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_feed', 'feed'),
            'fixed_args' => Array('rss.xml'),
        );
        $this->_request_switch['feed-rss1'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_feed', 'feed'),
            'fixed_args' => Array('rss1.xml'),
        );
        $this->_request_switch['feed-rss091'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_feed', 'feed'),
            'fixed_args' => Array('rss091.xml'),
        );
        $this->_request_switch['feed-atom'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_feed', 'feed'),
            'fixed_args' => Array('atom.xml'),
        );

        // The Archive
        $this->_request_switch['archive-welcome'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_archive', 'welcome'),
            'fixed_args' => Array('archive'),
        );
        $this->_request_switch['archive-year'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_archive', 'list'),
            'fixed_args' => Array('archive', 'year'),
            'variable_args' => 1,
        );
        $this->_request_switch['archive-month'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_archive', 'list'),
            'fixed_args' => Array('archive', 'month'),
            'variable_args' => 2,
        );

        // Administrative stuff
        $this->_request_switch['edit'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_admin', 'edit'),
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['delete'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_admin', 'delete'),
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );
        $this->_request_switch['create'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_create', 'create'),
            'fixed_args' => Array('create'),
            'variable_args' => 1,
        );
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('net_nehmer_publications_handler_configuration', 'configdm'),
            'schemadb' => 'file:/net/nehmer/publications/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
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
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_request_data['schemadb'][$name]->description
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                ));
            }
        }

        if (   $this->_topic->can_do('midgard:update')
            && $this->_topic->can_do('midcom:component_config'))
        {
            $this->_node_toolbar->add_item(Array(
                MIDCOM_TOOLBAR_URL => 'config.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n_midcom->get('component configuration'),
                MIDCOM_TOOLBAR_HELPTEXT => $this->_l10n_midcom->get('component configuration helptext'),
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_folder-properties.png',
            ));
        }
    }

    /**
     * Adds the RSS Feed LINK head elements.
     *
     * @access protected
     */
    function _add_link_head()
    {
        $_MIDCOM->add_link_head(
            array(
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => $this->_l10n->get('rss 2.0 feed'),
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rss2.xml',
            )
        );
        $_MIDCOM->add_link_head(
            array(
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => $this->_l10n->get('rss 1.0 feed'),
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rss1.xml',
            )
        );
        $_MIDCOM->add_link_head(
            array(
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => $this->_l10n->get('rss 0.91 feed'),
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rss091.xml',
            )
        );
        $_MIDCOM->add_link_head(
            array(
                'rel'   => 'alternate',
                'type'  => 'application/atom+xml',
                'title' => $this->_l10n->get('atom feed'),
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'atom.xml',
            )
        );
    }

    /**
     */
    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb'] =
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_add_link_head();
        $this->_populate_node_toolbar();

        return true;
    }

    /**
     * Simple helper, gets the last modified timestamp of the topic/content_topic combination
     * specified.
     *
     * @param midcom_db_topic $topic The base topic to use.
     * @param mdicom_db_topic $content_topic The topic where the articles are stored.
     */
    function get_last_modified($topic, $content_topic)
    {
        return time();
        /*
         * TODO
         *
        // Get last modified timestamp
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $content_topic->id);
        $qb->add_order('revised', 'DESC');
        $qb->set_limit(4);
        $articles = $qb->execute_unchecked();

        if ($articles)
        {
            return max($topic->revised, $articles[0]->revised);
        }
        else
        {
            return $topic->revised;
        }
        */
    }

}

?>