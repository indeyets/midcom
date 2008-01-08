<?php
/**
 * @package midgard.webdav.styles
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is the class that defines which URLs should be handled by this module. 
 * 
 * @package midgard.webdav.styles
 */
class midgard_webdav_styles_viewer extends midcom_baseclasses_components_request
{
    function midgard_webdav_styles_viewer($topic, $config)
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
        /**
         * Prepare the request switch, which contains URL handlers for the component
         */
        // Handle /config
        $this->_request_switch['config'] = array
        (
            'handler' => Array('midcom_core_handler_configdm', 'configdm'),
            'schemadb' => 'file:/midgard/webdav/styles/config/schemadb_config.inc',
            'schema' => 'config',
            'fixed_args' => Array('config'),
        );

        $this->_request_switch['allstyles'] = array( 
            'handler' => Array ( 'midgard_webdav_styles_handler_allstyles', 'allstyles_index' ),
            'fixed_args' => 'allstyles',
            'variable_args' => -1
        );
        $this->_request_switch['allstyles_sub'] = array( 
            'handler' => Array ( 'midgard_webdav_styles_handler_allstyles', 'allstyles' ),
            'fixed_args' => 'allstyles_sub',
            'variable_args' => 1 
        );


        // a list of available midcoms
        $this->_request_switch['midcoms'] = array( 
            'handler' => Array ( 'midgard_webdav_styles_handler_midcoms', 'midcoms' ),
            'fixed_args' => 'midcoms',
        );
$this->_request_switch['midcoms_stylelement'] = array( 
            'handler' => Array ( 'midgard_webdav_styles_handler_midcoms', 'element' ),
            'fixed_args' => array('midcoms'),
            'variable_args' => 2
        );

        // one midcoms style elements
         $this->_request_switch['midcoms_styleelements'] = array( 
            'handler' => Array ( 'midgard_webdav_styles_handler_midcoms', 'styleelements' ),
            'fixed_args' => array('midcoms'),
            'variable_args' => 1
        );



        // Handle /
        $this->_request_switch['index'] = array
        (
            'handler' => Array('midgard_webdav_styles_handler_index', 'index'),
        );

        $this->_request_switch['styles'] = array
        (
            'handler' => Array('midgard_webdav_styles_handler_allstyles', 'styles'),
            'variable_args' => -1
        );



        /*
    
        require 'HTTP/WebDAV/Server.php';
        require 'dav.php';
       	$_MIDCOM->cache->content->no_cache();
        $_MIDCOM->skip_page_style = true;
        */
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

        // Don't index directly, that would loose a reference due to limitations
        // of the index() method. Needs fixes there.

        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);

        $document = $indexer->new_document($dm);
        $document->topic_guid = $topic->guid;
        $document->topic_url = $node[MIDCOM_NAV_FULLURL];
        $document->component = $topic->component;
        $document->read_metadata_from_object($dm->storage->object);
        $indexer->index($document);
    }

    /**
     * Populates the node toolbar depending on the user's rights.
     *
     * @access protected
     */
    function _populate_node_toolbar()
    {   
        return true;              
    }

    /**
     * The handle callback populates the toolbars.
     */
    function _on_handle($handler, $args)
    {
        return true;
    }


}

?>
