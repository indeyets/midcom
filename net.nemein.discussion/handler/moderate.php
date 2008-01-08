<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum moderation handler
 *
 * @package net.nemein.discussion
 */

class net_nemein_discussion_handler_moderate extends midcom_baseclasses_components_handler
{
    /**
     * The thread we're working in
     *
     * @var net_nemein_discussion_thread_dba
     * @access private
     */
    var $_thread = null;

    /**
     * The post which is being moderated
     *
     * @var net_nemein_discussion_post_dba
     * @access private
     */
    var $_post = null;

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['thread'] =& $this->_thread;
        $this->_request_data['post'] =& $this->_parent_post;
    }


    /**
     * Simple default constructor.
     */
    function net_nemein_discussion_handler_moderate()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Marks post as possible abuse
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_report($handler_id, $args, &$data)
    {
        if (!array_key_exists('mark', $_POST))
        {
            return false;
        }

        $this->_post = new net_nemein_discussion_post_dba($args[0]);
        if (!$this->_post)
        {
            return false;
        }

        $this->_post->require_do('midgard:update');

        switch ($_POST['mark'])
        {
            case 'abuse':
                // Report the abuse
                $moderators = $this->_config->get('moderators');
                if (   $this->_post->report_abuse()
                    && $moderators)
                {
                    // Prepare notification message   
                    $_MIDCOM->load_library('org.openpsa.notifications');
                    $message = array();
                    $message['title'] = sprintf($data['l10n']->get('post %s reported as abuse'), $this->_post->subject);
                    $message['content'] = '';         
                    $logs = $post->get_logs();
                    if (count($logs) > 0)
                    {
                        $message['content'] .= $data['l10n']->get('moderation history').":\n\n";
                        foreach ($logs as $time => $log)
                        {
                            $reported = strftime('%x %X', strtotime("{$time}Z"));
                            $message['content'] .= $data['l10n']->get(sprintf('%s: %s by %s (from %s)', "$reported:\n", $data['l10n']->get($log['action']), $log['reporter'], $log['ip'])) . "\n\n";
                        }
                    }
                    $message['content'] = "\n\n" . $_MIDCOM->permalinks->create_permalink($this->_post->guid);  
                                      
                    $message['abstract'] = sprintf($data['l10n']->get('post %s reported as abuse'), $this->_post->subject);
                    $message['abstract'] = " " . $_MIDCOM->permalinks->create_permalink($this->_post->guid);
 
                    // Notify moderators
                    $moderator_guids = explode('|', $moderators);
                    foreach ($moderator_guids as $moderator_guid)
                    {
                        if (empty($moderator_guid))
                        {
                            continue;
                        }
                        org_openpsa_notifications::notify('net.nemein.discussion:reported_abuse', $moderator_guid, $message);
                    }
                }
                break;

            case 'confirm_abuse':
                $this->_post->require_do('net.nemein.discussion:moderation');
                // Confirm the message is abuse
                $this->_post->confirm_abuse();

                // Update the index
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->delete($this->_post->guid);

                break;

            case 'confirm_junk':
                $this->_post->require_do('net.nemein.discussion:moderation');
                // Confirm the message is abuse
                $this->_post->confirm_junk();

                // Update the index
                $indexer =& $_MIDCOM->get_service('indexer');
                $indexer->delete($this->_post->guid);

                break;

            case 'not_abuse':
                $this->_post->require_do('net.nemein.discussion:moderation');
                // Confirm the message is abuse
                $this->_post->report_not_abuse();
                
                if (isset($_POST['return_url']))
                {
                    $_MIDCOM->relocate($_POST['return_url']);
                    // This will exit.
                }
                
                $_MIDCOM->relocate("read/{$this->_post->guid}.html");
                // This will exit
        }

        $this->_thread = $this->_post->get_parent();
        if (is_a($this->_thread, 'net_nemein_discussion_post'))
        {
            // This post has up pointing to another post, setting the parent in that way
            while (!is_a($this->_thread, 'net_nemein_discussion_thread'))
            {
                $this->_thread = $this->_thread->get_parent();
            }
        }

        if (isset($_POST['return_url']))
        {
            $_MIDCOM->relocate($_POST['return_url']);
            // This will exit.
        }
        
        if ($this->_thread->posts > 0)
        {
            $_MIDCOM->relocate("{$this->_thread->name}/");
            // This will exit.
        }
        $_MIDCOM->relocate('');
        // This will exit.
    }

    /**
     * List posts marked as reported abuse for moderation purposes
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_moderate($handler_id, $args, &$data)
    {
        $_MIDCOM->auth->require_valid_user();
        $_MIDCOM->cache->content->enable_live_mode();
        
        $qb = new org_openpsa_qbpager('net_nemein_discussion_post_dba', 'net_nemein_discussion_posts');
        $qb->results_per_page = $this->_config->get('display_posts');
        $qb->display_pages = $this->_config->get('display_pages');
        $qb->add_constraint('status', '=', NET_NEMEIN_DISCUSSION_REPORTED_ABUSE);
        //$qb->add_constraint('thread.node', '=', $this->_topic->id);
        $qb->add_order('metadata.published', 'DESC');
        
        $data['posts'] = array();
        $data['post_qb'] =& $qb;
        
        $posts = $qb->execute();
        foreach ($posts as $post)
        {
            if (   !$post->can_do('net.nemein.discussion:moderation')
                || !$post->can_do('midgard:update'))
            {
                // Skip, this user can't mod
                continue;
            }
            
            $data['posts'][] = $post;
        }
        
        // Prepare datamanager
        $data['datamanager'] = new midcom_helper_datamanager2_datamanager($data['schemadb']);
        
        $tmp = Array();
        $tmp[] = Array
        (
            MIDCOM_NAV_URL => "moderation/",
            MIDCOM_NAV_NAME => sprintf($this->_l10n->get('%s moderation'), $this->_topic->extra),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);
        
        $_MIDCOM->set_pagetitle(sprintf($this->_l10n->get('%s moderation'), $this->_topic->extra));
        
        return true;
    }

    function _show_moderate($handler_id, &$data)
    {
        $data['node'] = $this->_topic;
        if (count($data['posts']) == 0)
        {
            midcom_show_style('view-moderate-empty');
            return;
        }
        
        midcom_show_style('view-moderate-header');
        
        foreach ($data['posts'] as $post)
        {
            $data['post'] =& $post;
            $data['post_toolbar'] = $this->_populate_post_toolbar($post);
            
            if (! $data['datamanager']->autoset_storage($post))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("The datamanager for post {$post->id} could not be initialized, skipping it.", MIDCOM_LOG_ERROR);
                debug_print_r('Object was:', $post);
                debug_pop();
                continue;
            }
            $data['view_post'] = $data['datamanager']->get_content_html();
            
            midcom_show_style('view-moderate-item');
        }
        
        midcom_show_style('view-moderate-footer');
    }
    
    function _populate_post_toolbar($post)
    {
        $toolbar = new midcom_helper_toolbar();

        $toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "report/{$post->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('confirm abuse'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $post->can_do('net.nemein.discussion:moderation'),
                MIDCOM_TOOLBAR_POST => true,
                MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                (
                    'mark' => 'confirm_abuse',
                    'return_url' => 'moderate/',
                )
            )
        );
        $toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "report/{$post->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('confirm junk'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                MIDCOM_TOOLBAR_ENABLED => $post->can_do('net.nemein.discussion:moderation'),
                MIDCOM_TOOLBAR_POST => true,
                MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                (
                    'mark' => 'confirm_junk',
                    'return_url' => 'moderate/',
                )
            )
        );
        $toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "report/{$post->guid}.html",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('not abuse'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                MIDCOM_TOOLBAR_ENABLED => $post->can_do('net.nemein.discussion:moderation'),
                MIDCOM_TOOLBAR_POST => true,
                MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                (
                    'mark' => 'not_abuse',
                    'return_url' => 'moderate/',
                )
            )
        );

        return $toolbar;
    }
}

?>