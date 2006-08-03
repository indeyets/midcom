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
    var $_toolbars;

    function net_nemein_discussion_viewer($topic, $config)
    {
        parent::midcom_baseclasses_components_request($topic, $config);
        
        $this->_toolbars =& midcom_helper_toolbars::get_instance();
        
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
        $this->_request_switch['read'] = Array
        (
            'handler' => Array('net_nemein_discussion_handler_thread', 'post'),
            'fixed_args' => Array('read'),
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
        
    }
    
    function _on_handle($handler_id, $args)
    {
        $_MIDCOM->add_link_head(
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL.'/net.nemein.discussion/discussion.css',
            )
        );
        $_MIDCOM->add_link_head(
            array(
                'rel'   => 'alternate',
                'type'  => 'application/rss+xml',
                'title' => $this->_l10n->get('rss 2.0 feed'),
                'href'  => $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . 'rss.xml',
            )
        );
        return true;
    }
    
    /**
     * Indexes an article.
     *
     * This function is usually called statically from various handlers.
     *
     * @param midcom_helper_datamanager2_datamanager $dm The Datamanager encaspulating the event.
     * @param midcom_services_indexer $indexer The indexer instance to use.
     * @param midcom_db_topic The topic which we are bound to. If this is not an object, the code
     *     tries to load a new topic instance from the database identified by this parameter.
     */
    function index(&$dm, &$indexer, $topic)
    {
        if (is_object($topic))
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
        $document->author = $dm->storage->object->sendername;
        $document->created = $dm->storage->object->created;
        $document->edited = $dm->storage->object->revised;
        $indexer->index($document);
    }    
}
?>
