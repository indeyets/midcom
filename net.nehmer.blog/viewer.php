<?php
/**
 * @package net.nehmer.blog
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
 * @package net.nehmer.blog
 */
class net_nehmer_blog_viewer extends midcom_baseclasses_components_request
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    function net_nehmer_blog_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
    }

    /**
     * Initialize the request switch and the content topic.
     *
     * @access protected
     */
    function _on_initialize()
    {
        $this->_determine_content_topic();
        $this->_request_data['content_topic'] =& $this->_content_topic;

        // *** Prepare the request switch ***

        // Index
        $this->_request_switch['index'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_index', 'index'),
        );
        $this->_request_switch['latest'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_index', 'index'),
            'fixed_args' => Array('latest'),
            'variable_args' => 1,
        );
        
        // Handler for /ajax/latest/<number>
        $this->_request_switch['ajax-latest'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_index', 'index'),
            'fixed_args' => Array('ajax', 'latest'),
            'variable_args' => 1,
        );

        // Handler for /category/<category>
        $this->_request_switch['index-category'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_index', 'index'),
            'fixed_args' => Array('category'),
            'variable_args' => 1,
        );
        // Handler for /category/latest/<category/<number>
        $this->_request_switch['latest-category'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_index', 'index'),
            'fixed_args' => Array('category', 'latest'),
            'variable_args' => 2,
        );

        // Various Feeds and their index page
        $this->_request_switch['feed-index'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_feed', 'index'),
            'fixed_args' => Array('feeds'),
        );
        $this->_request_switch['feed-category-rss2'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_feed', 'feed'),
            'fixed_args' => Array('feeds', 'category'),
            'variable_args' => 1,
        );
        $this->_request_switch['feed-rss2'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_feed', 'feed'),
            'fixed_args' => Array('rss.xml'),
        );
        $this->_request_switch['feed-rss1'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_feed', 'feed'),
            'fixed_args' => Array('rss1.xml'),
        );
        $this->_request_switch['feed-rss091'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_feed', 'feed'),
            'fixed_args' => Array('rss091.xml'),
        );
        $this->_request_switch['feed-atom'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_feed', 'feed'),
            'fixed_args' => Array('atom.xml'),
        );
        $this->_request_switch['feed-rsd'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_api_metaweblog', 'rsd'),
            'fixed_args' => Array('rsd.xml'),
        );

        // The Archive
        $this->_request_switch['archive-welcome'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_archive', 'welcome'),
            'fixed_args' => Array('archive'),
        );
        $this->_request_switch['archive-year'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_archive', 'list'),
            'fixed_args' => Array('archive', 'year'),
            'variable_args' => 1,
        );
        $this->_request_switch['archive-year-category'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_archive', 'list'),
            'fixed_args' => Array('archive', 'year'),
            'variable_args' => 2,
        );
        $this->_request_switch['archive-month'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_archive', 'list'),
            'fixed_args' => Array('archive', 'month'),
            'variable_args' => 2,
        );

        // Administrative stuff
        $this->_request_switch['edit'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_admin', 'edit'),
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
        );
        $this->_request_switch['delete'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_admin', 'delete'),
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );
        $this->_request_switch['delete_link'] = array
        (
            'handler' => array('net_nehmer_blog_handler_admin', 'deletelink'),
            'fixed_args' => array('delete', 'link'),
            'variable_args' => 1,
        );
        $this->_request_switch['create'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_create', 'create'),
            'fixed_args' => Array('create'),
            'variable_args' => 1,
        );
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_configuration', 'configdm'),
            //FIXME: make configurable
            'schemadb' => 'file:/net/nehmer/blog/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        $this->_request_switch['api-email'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_api_email', 'import'),
            'fixed_args' => Array('api', 'email'),
        );

        $this->_request_switch['api-metaweblog'] = Array
        (
            'handler' => Array('net_nehmer_blog_handler_api_metaweblog', 'server'),
            'fixed_args' => Array('api', 'metaweblog'),
        );

        // View article
        if ($this->_config->get('view_in_url'))
        {
            $this->_request_switch['view'] = Array
            (
                'handler' => Array('net_nehmer_blog_handler_view', 'view'),
                'fixed_args' => Array('view'),
                'variable_args' => 1,
            );
        }
        else
        {
            $this->_request_switch['view'] = Array
            (
                'handler' => Array('net_nehmer_blog_handler_view', 'view'),
                'variable_args' => 1,
            );
        }

        if ($this->_config->get('rss_subscription_enable'))
        {
            $_MIDCOM->load_library('net.nemein.rss');
            $rss_switches = net_nemein_rss_manage::get_plugin_handlers();
            $this->_request_switch = array_merge($this->_request_switch, $rss_switches);
        }
    }

    /**
     * Adds the RSS Feed LINK head elements.
     *
     * @access protected
     */
    function _add_link_head()
    {
        if ($this->_config->get('rss_enable'))
        {
            $_MIDCOM->add_link_head(
                array(
                    'rel'   => 'alternate',
                    'type'  => 'application/rss+xml',
                    'title' => $this->_l10n->get('rss 2.0 feed'),
                    'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rss.xml',
                )
            );
            $_MIDCOM->add_link_head
            (
                array(
                    'rel'   => 'alternate',
                    'type'  => 'application/atom+xml',
                    'title' => $this->_l10n->get('atom feed'),
                    'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'atom.xml',
                )
            );
        }

        // RSD (Really Simple Discoverability) autodetection
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'EditURI',
                'type' => 'application/rsd+xml',
                'title' => 'RSD',
                'href' => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rsd.xml',
            )
        );
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {
        if ($this->_content_topic->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "create/{$name}.html",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_l10n->get($this->_request_data['schemadb'][$name]->description)
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                    MIDCOM_TOOLBAR_ACCESSKEY => 'n',
                ));
            }
        }

        if ($this->_config->get('rss_subscription_enable'))
        {
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'feeds/subscribe/',
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('subscribe feeds', 'net.nemein.rss'),
                    MIDCOM_TOOLBAR_ICON => 'net.nemein.rss/rss-16.png',
                    MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
                )
            );
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => 'feeds/list/',
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('manage feeds', 'net.nemein.rss'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/properties.png',
                    MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
                )
            );
            $this->_node_toolbar->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "feeds/fetch/all",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('refresh all feeds', 'net.nemein.rss'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_refresh.png',
                    MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
                )
            );
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

    function _enter_language()
    {
        $lang = $this->_config->get('language');
        if ($lang)
        {
            $this->_request_data['original_language'] = $_MIDGARD['lang'];

            $language = $_MIDCOM->i18n->code_to_id($lang);
            if ($language && $language != $_MIDGARD['lang'])
            {
                mgd_set_lang($language);
            }
        }
    }

    function _exit_language()
    {
        if (isset($this->_request_data['original_language']))
        {
            mgd_set_lang($this->_request_data['original_language']);
        }
    }

    /**
     * Generic request startup work:
     *
     * - Load the Schema Database
     * - Add the LINK HTML HEAD elements
     * - Populate the Node Toolbar
     */
    function _on_can_handle($handler, $args)
    {
        $this->_enter_language();
        return true;
    }

    function _on_can_handled($handler, $args)
    {
        $this->_exit_language();
    }

    function _on_handle($handler, $args)
    {
        $this->_request_data['schemadb'] =
            midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));

        $this->_add_categories();

        $this->_add_link_head();
        $this->_populate_node_toolbar();
        $this->_enter_language();
        return true;
    }

    function _on_handled($handler, $args)
    {
        $this->_exit_language();
    }

    function _on_show($handler)
    {
        $this->_enter_language();
        return true;
    }

    function _on_shown($handler)
    {
        $this->_exit_language();
    }


    /**
     * Populate the categories configured for the topic into the schemas
     */
    function _add_categories()
    {
        if ($this->_config->get('categories') == '')
        {
            // No categories defined, skip this.
            $this->_request_data['categories'] = Array();
            return false;
        }

        $this->_request_data['categories'] = explode(',', $this->_config->get('categories'));

        foreach ($this->_request_data['schemadb'] as $name => $schema)
        {
            if (   array_key_exists('categories', $schema->fields)
                && $this->_request_data['schemadb'][$name]->fields['categories']['type'] == 'select')
            {
                // TODO: Merge schema local and component config categories?
                $this->_request_data['schemadb'][$name]->fields['categories']['type_config']['options'] = Array();
                foreach ($this->_request_data['categories'] as $category)
                {
                    $this->_request_data['schemadb'][$name]->fields['categories']['type_config']['options'][$category] = $category;
                }
            }
        }
    }

    /**
     * Set the content topic to use. This will check against the configuration setting
     * 'symlink_topic'.
     *
     * @access protected
     */
    function _determine_content_topic()
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $guid = $this->_config->get('symlink_topic');
        if (is_null($guid))
        {
            // No symlink topic
            // Workaround, we should talk to a DBA object automatically here in fact.
            $this->_content_topic = new midcom_db_topic($this->_topic->id);
            debug_pop();
            return;
        }

        $this->_content_topic = new midcom_db_topic($guid);

        // Validate topic.

        if (! $this->_content_topic)
        {
            debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: '
                . mgd_errstr(), MIDCOM_LOG_ERROR);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to open symlink content topic.');
            // This will exit.
        }

        if ($this->_content_topic->component != 'net.nehmer.blog')
        {
            debug_print_r('Retrieved topic was:', $this->_content_topic);
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Symlink content topic is invalid, see the debug level log for details.');
            // This will exit.
        }

        debug_pop();
    }

    /**
     * Indexes an article.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager &$dm The Datamanager encapsulating the event.
     * @param midcom_services_indexer &$indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        if (!is_object($topic))
        {
            $tmp = new midcom_db_topic($topic);
            if (! $tmp)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to load the topic referenced by {$topic} for indexing, this is fatal.");
                // This will exit.
            }
            $topic = $tmp;
        }

        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.

        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->component = $topic->component;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $indexer->index($document);
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
        // Get last modified timestamp
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $content_topic->id);
        $qb->add_order('metadata.revised', 'DESC');
        $qb->set_limit(4);
        $articles = $qb->execute_unchecked();

        if ($articles)
        {
            if (array_key_exists(0, $articles))
            {
                return max($topic->metadata->revised, $articles[0]->metadata->revised);
            }
            return $topic->metadata->revised;
        }
        else
        {
            return $topic->metadata->revised;
        }
    }

}

?>