<?php
/**
 * @package net.nemein.quickpoll
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module.
 *
 * @package net.nemein.quickpoll
 */
class net_nemein_quickpoll_viewer extends midcom_baseclasses_components_request
{
    /**
     * The topic in which to look for articles. This defaults to the current content topic
     * unless overridden by the symlink topic feature.
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    function net_nemein_quickpoll_viewer($topic, $config)
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
        $this->_content_topic = new midcom_db_topic($this->_topic->id);

        $this->_request_data['content_topic'] =& $this->_content_topic;
        /**
         * Prepare the request switch, which contains URL handlers for the component
         */

        // Administrative stuff
        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            //FIXME: make configurable
            'schemadb' => 'file:/net/nemein/quickpoll/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );
        // Handle /create/
        $this->_request_switch['create'] = Array
        (
            'handler' => Array('net_nemein_quickpoll_handler_create', 'create'),
            'fixed_args' => Array('create'),
            'variable_args' => 1,
        );
        // Handle /edit/
        $this->_request_switch['edit'] = Array
        (
            'handler' => Array('net_nemein_quickpoll_handler_admin', 'edit'),
            'fixed_args' => Array('edit'),
            'variable_args' => 1,
        );
        // Handle /delete/
        $this->_request_switch['delete'] = Array
        (
            'handler' => Array('net_nemein_quickpoll_handler_admin', 'delete'),
            'fixed_args' => Array('delete'),
            'variable_args' => 1,
        );
        // Handle /manage/<article_id>
        $this->_request_switch['manage'] = array
        (
            'handler' => Array('net_nemein_quickpoll_handler_index', 'view'),
            'fixed_args' => Array('manage'),
            'variable_args' => 1,
        );
        // Handle /comments/<article_id>
        $this->_request_switch['comments'] = array
        (
            'handler' => Array('net_nemein_quickpoll_handler_votes', 'comments'),
            'fixed_args' => Array('comments'),
            'variable_args' => 1,
        );
        
        // Handle /comments/xml/<article_id>
        $this->_request_switch['comments_xml'] = array
        (
            'handler' => Array('net_nemein_quickpoll_handler_votes', 'xml'),
            'fixed_args' => Array('comments', 'xml'),
            'variable_args' => 1,
        );

        // Handle /archive/
        $this->_request_switch['archive'] = Array
        (
            'handler' => Array('net_nemein_quickpoll_handler_archive', 'archive'),
            'fixed_args' => Array('archive'),
        );


        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('net_nemein_quickpoll_handler_index', 'index'),
        );

        // Handle /<article_id>
        $this->_request_switch['view'] = array
        (
            'handler' => Array('net_nemein_quickpoll_handler_index', 'view'),
            'variable_args' => 1,
        );
        
        // Handle /view/<article_id>
        $this->_request_switch['view-results'] = array
        (
            'handler' => Array('net_nemein_quickpoll_handler_index', 'view'),
            'fixed_args' => Array('view'),
            'variable_args' => 1,
        );
        
        // Handle /index/<result_type>
        // Available types: XML
        $this->_request_switch['index-with-type'] = array
        (
            'handler' => Array('net_nemein_quickpoll_handler_index', 'index'),
            'fixed_args' => Array('index'),
            'variable_args' => 1,
        );

        // Handle /polldata/<result_type>/<article_id>
        // Available types: XML, ajax
        $this->_request_switch['polldata-with-type'] = array
        (
            'handler' => Array('net_nemein_quickpoll_handler_index', 'polldata'),
            'fixed_args' => Array('polldata'),
            'variable_args' => 2,
        );

        // Handle /vote/<article_id>
        $this->_request_switch['vote'] = Array
        (
            'handler' => Array('net_nemein_quickpoll_handler_vote', 'vote'),
            'fixed_args' => Array('vote'),
            'variable_args' => 1,
        );

        // Handle /vote/<type>/<article_id>
        // Available types: XML,ajax
        $this->_request_switch['vote-with-type'] = Array
        (
            'handler' => Array('net_nemein_quickpoll_handler_vote', 'vote'),
            'fixed_args' => Array('vote'),
            'variable_args' => 2,
        );
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
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $document->component = $topic->component;
        $indexer->index($document);
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
        if (   $this->_topic->can_do('midgard:create'))
        {
            foreach (array_keys($this->_request_data['schemadb']) as $name)
            {
                $this->_node_toolbar->add_item(Array(
                    MIDCOM_TOOLBAR_URL => "create/{$name}/",
                    MIDCOM_TOOLBAR_LABEL => sprintf
                    (
                        $this->_l10n_midcom->get('create %s'),
                        $this->_l10n->get($this->_request_data['schemadb'][$name]->description)
                    ),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/new-text.png',
                ));
            }
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

        return true;
    }

    /**
     * Check if user has voted already either based on login or IP address
     *
     * @access static
     * @return boolean
     */
    function has_voted($poll_id, &$config)
    {
        if (   $config->get('allow_multiple_votes')
            && (   $config->get('enable_anonymous')
                || $_MIDCOM->auth->user)
            )
        {
            // We always let user vote
            return false;
        }
        
        $qb_vote = new midgard_query_builder('net_nemein_quickpoll_vote');
        $qb_vote->add_constraint('article', '=', $poll_id);

        if ($config->get('lock_ip_address'))
        {
            $qb_vote->add_constraint('ip', '=', $_SERVER['REMOTE_ADDR']);
        }
        else if ($_MIDGARD['user'])
        {
            $qb_vote->add_constraint('user', '=', $_MIDGARD['user']);
        }

        $vote_count = $qb_vote->count();        
        if ($vote_count > 0)
        {
            $voted = true;
        }
        else
        {
            $voted = false;
        }

        if (   $config->get('enable_anonymous')
            && !$config->get('lock_ip_address')
            && !$_MIDGARD['user'])
        {
            $voted = false;
        }

        if (   !$config->get('enable_anonymous')
            && (   !$_MIDCOM->auth->user
                && !$_MIDCOM->auth->admin))
        {
            $voted = true;
        }
        
        return $voted;
    }

    function vote_value_as_total($poll_id)
    {
        $total = 0;
        
        $mc = new midgard_collector('net_nemein_quickpoll_vote_dba', 'article', $poll_id);
        $mc->set_key_property('id');
        $mc->add_value_property('value');
        $mc->execute();
        
        $votes = $mc->list_keys();
        
        if (count($votes) == 0)
        {
            return 0;
        }
        
        foreach ($votes as $id => $data)
        {
            $total += (int) $mc->get_subkey($id, 'value');
        }
        
        return $total;
    }
    
    function vote_value_as_average($poll_id)
    {
        $total = 0;
        
        $mc = new midgard_collector('net_nemein_quickpoll_vote_dba', 'article', $poll_id);
        $mc->set_key_property('id');
        $mc->add_value_property('value');
        $mc->execute();
        
        $votes = $mc->list_keys();
        $vote_count = count($votes);
        if ($vote_count == 0)
        {
            return 0;
        }
        
        foreach ($votes as $id => $data)
        {
            $total += (int) $mc->get_subkey($id, 'value');
        }
        
        $percent = ($total / $vote_count);
        
        return $percent;
    }
}

?>