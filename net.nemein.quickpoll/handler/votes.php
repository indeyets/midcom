<?php
/**
 * @package net.nemein.quickpoll
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: admin.php 5385 2007-02-19 10:04:06Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Quickpoll vote moderation and listing handler
 *
 * @package net.nemein.quickpoll
 */

class net_nemein_quickpoll_handler_votes extends midcom_baseclasses_components_handler
{
    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * The article to operate on
     *
     * @var midcom_db_article
     * @access private
     */
    var $_article = null;

    /**
     * Simple default constructor.
     */
    function net_nemein_quickpoll_handler_votes()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];
    }
    
    function _load_schemadb()
    {
        $this->_request_data['schemadb_vote'] = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb_vote'));
        
        // Add additional fields
        $additional_fields = $this->_config->get('additional_vote_keys');
        foreach ($additional_fields as $field)
        {
            $this->_request_data['schemadb_vote']['default']->append_field
            (
                $field, array
                (
                    'title' => 'comment',
                    'storage' => array
                    (
                        'location' => 'configuration',
                        'domain' => 'net.nemein.quickpoll',
                        'name' => $field,
                    ),
                    'type' => 'text',
                    'widget' => 'text',
                )
            );
        }
    }
    
    function _add_breadcrumb($handler_id)
    {
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "{$this->_article->name}/",
            MIDCOM_NAV_NAME => $this->_article->title,
        );
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "comments/{$this->_article->guid}/",
            MIDCOM_NAV_NAME => $this->_l10n->get('moderate comments'),
        );

        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
    }
    
    function _vote_toolbar($vote)
    {
        // Load toolbar
        $metadata = $vote->get_metadata();
        $this->_request_data['votes_toolbars'][$vote->guid] = new midcom_helper_toolbar();
        
        if ($metadata->is_approved())
        {
            $this->_request_data['votes_toolbars'][$vote->guid]->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__ais/folder/unapprove.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('unapprove', 'midcom'),
                    MIDCOM_TOOLBAR_HELPTEXT => $_MIDCOM->i18n->get_string('approved', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                    MIDCOM_TOOLBAR_POST => true,
                    MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                    (
                        'guid' => $vote->guid,
                        'return_to' => $_SERVER['REQUEST_URI'],
                    ),
                    MIDCOM_TOOLBAR_ENABLED => $vote->can_do('midcom:approve'),
                )
            );
        }
        else
        {
            $this->_request_data['votes_toolbars'][$vote->guid]->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__ais/folder/approve.html",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('approve', 'midcom'),
                    MIDCOM_TOOLBAR_HELPTEXT => $_MIDCOM->i18n->get_string('unapproved', 'midcom'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/not_approved.png',
                    MIDCOM_TOOLBAR_POST => true,
                    MIDCOM_TOOLBAR_POST_HIDDENARGS => Array
                    (
                        'guid' => $vote->guid,
                        'return_to' => $_SERVER['REQUEST_URI'],
                    ),
                    MIDCOM_TOOLBAR_ENABLED => $vote->can_do('midcom:approve'),
                )
            );
        }
        if ($GLOBALS['midcom_config']['midcom_services_rcs_enable'])
        {
            $this->_request_data['votes_toolbars'][$vote->guid]->add_item
            (
                array
                (
                    MIDCOM_TOOLBAR_URL => "__ais/rcs/{$vote->guid}/",
                    MIDCOM_TOOLBAR_LABEL => $_MIDCOM->i18n->get_string('show history', 'no.bergfald.rcs'),
                    MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/history.png',
                    MIDCOM_TOOLBAR_ENABLED => $vote->can_do('midgard:update'),
                )
            );
        }
    }
    
    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_comments($handler_id, $args, &$data)
    {        
        $this->_article = new midcom_db_article($args[0]);
        if (   !$this->_article
            || !$this->_article->guid
            || $this->_article->topic != $this->_content_topic->id)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The article {$args[0]} was not found.");
            // This will exit.
        }
        
        // TODO: More specific privilege?
        $this->_article->require_do('midcom:approve');

        $qb = new org_openpsa_qbpager('net_nemein_quickpoll_vote_dba', 'net_nemein_quickpoll_votes');
        $qb->add_constraint('article', '=', $this->_article->id);
        $qb->add_constraint('comment', '<>', '');
        $qb->add_constraint('comment', '<>', 'undefined');
        $qb->add_order('metadata.published', 'DESC');
        $data['qb'] =& $qb;
        
        $data['view_title'] = $this->_article->title;
        $_MIDCOM->set_pagetitle($this->_article->title);
        
        $_MIDCOM->bind_view_to_object($this->_article);
        
        $this->_add_breadcrumb($handler_id);

        $data['votes'] = $data['qb']->execute();
        $data['votes_toolbars'] = array();
        $data['votes_controllers'] = array();
        
        $this->_load_schemadb();
        
        foreach ($data['votes'] as $vote)
        {
            // Load AJAX controllers
            $data['votes_controllers'][$vote->guid] =& midcom_helper_datamanager2_controller::create('ajax');
            $data['votes_controllers'][$vote->guid]->schemadb =& $this->_request_data['schemadb_vote'];
            $data['votes_controllers'][$vote->guid]->set_storage($vote);
            $data['votes_controllers'][$vote->guid]->process_ajax();
            
            // Populate vote toolbar
            $this->_vote_toolbar($vote);
        }
        
        // Prevent client / proxy from caching the resulting page
        $_MIDCOM->cache->content->no_cache();
        $_MIDCOM->cache->content->uncached();
                
        return true;
    }
    
    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_comments($handler_id, &$data)
    {
        midcom_show_style('admin-comments');
    }
    
    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_xml($handler_id, $args, &$data)
    {        
        if ($handler_id == 'comments_xml_latest')
        {
            $mc = midcom_db_article::new_collector('topic', $this->_content_topic->id);
            $mc->add_value_property('guid');
            $mc->add_constraint('topic', '=', $this->_content_topic->id);
            $mc->add_order('metadata.created', 'DESC');
            $mc->set_limit(1);
            $mc->execute();
            
            $guids = $mc->list_keys();
            foreach ($guids as $key => $data)
            {
                $article_guid = $mc->get_subkey($key, 'guid');
            }
            $this->_article = new midcom_db_article($article_guid);
        }
        else
        {
            $this->_article = new midcom_db_article($args[0]);
        }
        
        if (   !$this->_article
            || !$this->_article->guid
            || $this->_article->topic != $this->_content_topic->id)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRNOTFOUND, "The article {$args[0]} was not found.");
            // This will exit.
        }
        
        $_MIDCOM->cache->content->content_type('text/xml');
        $_MIDCOM->header('Content-type: text/xml; charset=UTF-8');
        
        $qb_comments = net_nemein_quickpoll_vote_dba::new_query_builder();
        $qb_comments->add_constraint('article', '=', $this->_article->id);
        $qb_comments->add_constraint('comment', '<>', '');
        $data['qb_comments'] =& $qb_comments;
        
        $qb_options = net_nemein_quickpoll_option_dba::new_query_builder();
        $qb_options->add_constraint('article', '=', $this->_article->id);
        $data['qb_options'] =& $qb_options;
        
        $data['article'] =& $this->_article;
        $data['config'] =& $this->_config;
        
        $_MIDCOM->skip_page_style = true;

        // Prevent client / proxy from caching the resulting page
        $_MIDCOM->cache->content->no_cache();
        $_MIDCOM->cache->content->uncached();
        
        return true;
    }
    
    /**
     * This function does the output.
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_xml($handler_id, &$data)
    {
        midcom_show_style('comments-xml');
    }
}

?>