<?php

/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum Site interface class.
 *
 * @package net.nemein.discussion
 */

class net_nemein_discussion_viewer extends midcom_baseclasses_components_request
{

    function net_nemein_discussion_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
        
        // Match /
        $this->_request_switch['index'] = array(
            'handler' => Array('net_nemein_discussion_handler_index', 'index'),
        );

        // Match /post/
        $this->_request_switch['post'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_post', 'create'),
            'fixed_args' => Array('post')
        );

        // Match /read/<post guid>
        $this->_request_switch['read_redirect'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_thread', 'post'),
            'fixed_args' => Array('read'),
            'variable_args' => 1,            
        );
        
        // Match /read/xml/<post guid>
        $this->_request_switch['read_xml'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_thread', 'post'),
            'fixed_args' => Array('read', 'xml'),
            'variable_args' => 1,            
        );

        // Match /rss.xml
        $this->_request_switch['rss'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_latest', 'latest'),
            'fixed_args' => Array('rss.xml'),
        );

        // Match /all.xml
        $this->_request_switch['rss_all'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_latest', 'latest_all'),
            'fixed_args' => Array('all.xml'),
        );

        // Match /threadname/
        $this->_request_switch['thread'] = array(
            'handler' => Array('net_nemein_discussion_handler_thread', 'thread'),
            'variable_args' => 1,
        );

        // Match /reply/<post guid>
        $this->_request_switch['reply'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_post', 'reply'),
            'fixed_args' => Array('reply'),
            'variable_args' => 1,
        );
        
        // Match /report/<post guid>
        $this->_request_switch['report'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_moderate', 'report'),
            'fixed_args' => Array('report'),
            'variable_args' => 1,
        );
        
        // Match /latest/all/<N>
        $this->_request_switch['latest_all'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_latest', 'latest_all'),
            'fixed_args' => Array('latest', 'all'),
            'variable_args' => 1,
        ); 
        
        // Match /latest/<N> 
        $this->_request_switch['latest'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_latest', 'latest'),
            'fixed_args' => Array('latest'),
            'variable_args' => 1,
        );

        $this->_request_switch['api-email'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_api_email', 'import'),
            'fixed_args' => Array('api', 'email'),
        );

        // Match /config/
        $this->_request_switch['config'] = Array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/net/nemein/discussion/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );        
    }
    
    function _on_handle($handler_id, $args)
    {
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/net.nemein.discussion/discussion.css',
            )
        );
        $_MIDCOM->add_link_head
        (
            array(
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => $this->_l10n->get('rss 2.0 feed'),
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rss.xml',
            )
        );
        
        $this->_node_toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => 'post.html',
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('create thread'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-reply.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_topic->can_do('midgard:create'),
                MIDCOM_TOOLBAR_ACCESSKEY => 'n',
            )
        );

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
        
        $this->_request_data['schemadb'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        
        return true;
    }
    
    /**
     * Indexes an article.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encapsulating the event.
     * @param midcom_services_indexer $indexer The indexer instance to use.
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
        
        // Ensure the post is in a thread that is in the given topic
        $thread = new net_nemein_discussion_thread_dba($dm->storage->object->thread);
        if ($thread->node != $topic->id)
        {
            return false;
        }

        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.
        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->read_metadata_from_object($dm->storage->object);
        $document->author = $dm->storage->object->sendername;
        $document->component = $topic->component;
        $indexer->index($document);
    }    
}
?>
