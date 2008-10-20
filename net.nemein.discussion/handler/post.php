<?php
/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Discussion forum create post handler
 *
 * @package net.nemein.discussion
 */

class net_nemein_discussion_handler_post extends midcom_baseclasses_components_handler
{
    /**
     * The thread we're working in
     *
     * @var net_nemein_discussion_thread_dba
     * @access private
     */
    var $_thread = null;

    /**
     * The post we're replying to
     *
     * @var net_nemein_discussion_post_dba
     * @access private
     */
    var $_parent_post = null;

    /**
     * The post which has been created
     *
     * @var net_nemein_discussion_post_dba
     * @access private
     */
    var $_post = null;

    /**
     * The Controller of the article used for editing
     *
     * @var midcom_helper_datamanager2_controller_simple
     * @access private
     */
    var $_controller = null;

    /**
     * The schema database in use, available only while a datamanager is loaded.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * The schema to use for the new article.
     *
     * @var string
     * @access private
     */
    var $_schema = null;

    /**
     * The defaults to use for the new article.
     *
     * @var Array
     * @access private
     */
    var $_defaults = Array();

    /**
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['controller'] =& $this->_controller;
        $this->_request_data['thread'] =& $this->_thread;
        $this->_request_data['parent_post'] =& $this->_parent_post;
        $this->_request_data['schema'] =& $this->_schema;
        $this->_request_data['schemadb'] =& $this->_schemadb;
        $this->_request_data['post_tree'] =& $this->_post_tree;
        $this->_request_data['post_for_tree'] = array();
    }


    /**
     * Simple default constructor.
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatment is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb =& $this->_request_data['schemadb'];
        // TODO: Be extra smart here about populating/hiding fields
        if ($_MIDCOM->auth->user)
        {
            $user = $_MIDCOM->auth->user->get_storage();
            foreach (array_keys($this->_schemadb) as $name)
            {
                $this->_schemadb[$name]->fields['sendername']['readonly'] = true;
                $this->_defaults['sendername'] = $user->name;
                $this->_defaults['senderemail'] = $user->email;
            }
        }

        if ($this->_parent_post)
        {
            if (strstr($this->_parent_post->subject, $this->_l10n->get('re:')))
            {
                $this->_defaults['subject'] = $this->_parent_post->subject;
            }
            else
            {
                $this->_defaults['subject'] = sprintf($this->_l10n->get('re:').' %s', $this->_parent_post->subject);
            }
        }
    }

    /**
     * Internal helper, fires up the creation mode controller. Any error triggers a 500.
     *
     * @access private
     */
    function _load_controller()
    {
        $this->_load_schemadb();
        $this->_controller =& midcom_helper_datamanager2_controller::create('create');
        $this->_controller->schemadb =& $this->_schemadb;
        $this->_controller->schemaname = $this->_schema;
        $this->_controller->defaults = $this->_defaults;
        $this->_controller->callback_object =& $this;
        if (! $this->_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to initialize a DM2 create controller.");
            // This will exit.
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_post = new net_nemein_discussion_post_dba();

        // Set status according to configuration
        if ($_MIDCOM->auth->user)
        {
            $this->_post->status = $this->_config->get('new_message_status_user');
        }
        else
        {
            $this->_post->status = $this->_config->get('new_message_status_anon');
        }

        if ($this->_thread)
        {
            $this->_post->thread = $this->_thread->id;
        }
        else
        {
            $thread = new net_nemein_discussion_thread_dba();
            $thread->node = $this->_topic->id;

            if (!$thread->create())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_print_r('We operated on this object:', $thread);
                debug_pop();
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Failed to create a new thread, cannot continue. Last Midgard error was: '. mgd_errstr());
                // This will exit.
            }

            $this->_thread = new net_nemein_discussion_thread_dba($thread->id);
            $this->_post->thread = $this->_thread->id;
        }

        if ($this->_parent_post)
        {
            $this->_post->replyto = $this->_parent_post->id;
        }

        if ($_MIDCOM->auth->user)
        {
            $user =& $_MIDCOM->auth->user->get_storage();
            $this->_post->sender = $user->id;
            $this->_post->senderemail = $user->email;
            $this->_post->sendername = $user->name;
        }

        if (! $this->_post->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_post);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new post, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_post;
    }
    
    function _populate_post_toolbar($post)
    {
        $toolbar = new midcom_helper_toolbar();

/*        $toolbar->add_item
        (
            array
            (
                MIDCOM_TOOLBAR_URL => "reply/{$post->guid}/",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('reply'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_mail-reply.png',
                MIDCOM_TOOLBAR_ENABLED => $this->_request_data['thread']->can_do('midgard:create'),
            )
        );
        */

        if (   $post->can_do('midgard:update')
            && $_MIDCOM->auth->user
            && $post->status < NET_NEMEIN_DISCUSSION_MODERATED)
        {
            if (!$post->can_do('net.nemein.discussion:moderation'))
            {
                // Regular users can only report abuse
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "report/{$post->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('report abuse'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/stock_help-agent.png',
                        MIDCOM_TOOLBAR_ENABLED =>  $post->can_do('midgard:update'),
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                        (
                            'mark' => 'abuse',
                        )
                    )
                );
            }
            else
            {
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "report/{$post->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('confirm abuse'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                        MIDCOM_TOOLBAR_ENABLED => $post->can_do('net.nemein.discussion:moderation'),
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                        (
                            'mark' => 'confirm_abuse',
                        )
                    )
                );
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "report/{$post->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('confirm junk'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/trash.png',
                        MIDCOM_TOOLBAR_ENABLED => $post->can_do('net.nemein.discussion:moderation'),
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                        (
                            'mark' => 'confirm_junk',
                        )
                    )
                );
                $toolbar->add_item
                (
                    array
                    (
                        MIDCOM_TOOLBAR_URL => "report/{$post->guid}/",
                        MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('not abuse'),
                        MIDCOM_TOOLBAR_HELPTEXT => null,
                        MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/approved.png',
                        MIDCOM_TOOLBAR_ENABLED => $post->can_do('net.nemein.discussion:moderation'),
                        MIDCOM_TOOLBAR_POST => true,
                        MIDCOM_TOOLBAR_POST_HIDDENARGS => array
                        (
                            'mark' => 'not_abuse',
                        )
                    )
                );
            }
        }
        return $toolbar;
    }

    /**
     * Handle thread creation
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_create($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:create');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Update thread accordingly
                $this->_thread->title = $this->_post->subject;
                $this->_thread->name = midcom_generate_urlname_from_string($this->_post->subject);
                $this->_thread->posts = 1;
                $this->_thread->firstpost = $this->_post->id;
                $this->_thread->latestpost = $this->_post->id;
                $this->_thread->latestposttime = (int) $this->_post->metadata->published;
                $i = 0;
                // FIXME: check for duplicate name explicitly
                while (   !$this->_thread->update()
                           && $i < 1000)
                {
                    $this->_thread->name = midcom_generate_urlname_from_string($this->_post->subject).sprintf("-%03d",$i);
                    $i++;
                }

                // Index the post
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_discussion_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                $this->_email_post();

                if ($this->_config->get('autoapprove'))
                {
                    $_MIDCOM->auth->request_sudo('net.nemein.discussion');

                    $meta = $this->_post->get_metadata();
                    $meta->approve();

                    $meta = $this->_thread->get_metadata();
                    $meta->approve();

                    $_MIDCOM->auth->drop_sudo();
                }

                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "read/{$this->_post->guid}/");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
                // This will exit.
                
            case 'edit':
                if (   $this->_thread
                    && $this->_thread->guid)
                {
                    $this->_thread->delete();
                }
                break;
        }

        $this->_prepare_request_data();

        // Set metadata
        $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('post to') . " {$this->_topic->extra}");
        $breadcrumb = Array();
        $breadcrumb[] = Array
        (
            MIDCOM_NAV_URL => 'post/',
            MIDCOM_NAV_NAME => $this->_request_data['l10n']->get('post to') . " {$this->_topic->extra}",
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        $this->_view_toolbar->add_item(
            array
            (
                MIDCOM_TOOLBAR_URL => "",
                MIDCOM_TOOLBAR_LABEL => $this->_l10n->get('back to forum'),
                MIDCOM_TOOLBAR_HELPTEXT => null,
                MIDCOM_TOOLBAR_ICON => 'stock-icons/16x16/back.png',
                MIDCOM_TOOLBAR_ENABLED =>  true,
            )
        );

        return true;
    }

    /**
     * Show thread posting form
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_create($handler_id, &$data)
    {
        $this->_request_data['forum_title'] = $this->_topic->extra;
        midcom_show_style('new-thread');
    }

    /**
     * Lists post childs
     *
     * @param string $post_id The ID of the parentpost.
     * @return array $returnvalues.
     */
    function _list_post_childs($post_id)
    {
        $post_tree = array();

        $mc = net_nemein_discussion_post_dba::new_collector('thread', (int) $this->_thread->id);
        foreach($this->_tree_view_keys as $key)
        {
            $mc->add_value_property($key);
        }
        $mc->add_constraint('replyto', '=', $post_id);
        $mc->add_order('id');
        $keys = $mc->list_keys();
        foreach ( $keys as $guid => $array )
        {
            $post_id = $mc->get_subkey($guid, 'id');
            foreach( $this->_tree_view_keys as $key )
            {
                $post_tree[$post_id][$key] = $mc->get_subkey($guid, $key);
            }
            
            if(!$this->_post_tree_current_passed)
            {
                $this->_post_tree['root']['previous'] = $post_tree[$post_id]['guid'];
            }
            elseif($this->_post_tree['root']['next'] == '')
            {
                $this->_post_tree['root']['next'] = $post_tree[$post_id]['guid'];
            }
            
            if( $this->_parent_post->id == $post_id )
            {
                $this->_post_tree_current_passed = true;
            }
            

            $post_tree[$post_id]['children'] = $this->_list_post_childs($post_id);
            $post_tree[$post_id]['children_count'] = count($post_tree[$post_id]['children']);
        }
        return $post_tree;
    }

    /**
     * Handle replies to threads
     *
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_reply($handler_id, $args, &$data)
    {

        $this->_parent_post = new net_nemein_discussion_post_dba($args[0]);
        if (!$this->_parent_post)
        {
            return false;
        }

        $this->_thread = $this->_parent_post->get_parent();
        if (is_a($this->_thread, 'net_nemein_discussion_post'))
        {
            // This post has up pointing to another post, setting the parent in that way
            while (!is_a($this->_thread, 'net_nemein_discussion_thread'))
            {
                $this->_thread = $this->_thread->get_parent();
            }
        }

        $this->_thread->require_do('midgard:create');

        if ($this->_config->get('auto_quote_on_reply'))
        {
            $mode = $this->_request_data['schemadb']['default']->fields['content']['type_config']['output_mode'];
            $parent_content = $this->_parent_post->content;
            $line_break = "\n";
            $quote = "";

            if ($mode == 'html')
            {
                $quote .= "<div class=\"net_nemein_discussion_post_quote\">";
                $line_break = "<br/>";
                $parent_content = preg_replace("/<br\s*\\/?>/i", "\n", $parent_content);
                $parent_content = preg_replace('/\x0a\x0d|\x0d\x0a|\x0d/', "\n", $parent_content);
            }

            $rows = preg_split("/[\n]/", preg_replace('/\x0a\x0d|\x0d\x0a|\x0d/', "\n", $parent_content));

            $quote .= "> {$line_break}";
            foreach ($rows as $row)
            {
                $quote .= "> {$row}{$line_break}";
            }
            $quote .= "> {$line_break}{$line_break}";

            if ($mode == 'html')
            {
                $quote .= "</div><br />";
            }

            $this->_defaults['content'] = $quote;
        }

        if ( $this->_config->get('display_thread_mode') == 'tree' )
        {
            $this->_post_tree = array();
            $this->_post_tree_current_passed = false;
            $this->_tree_view_keys = explode(',', $this->_config->get('display_tree_keys'));
            if(!in_array('id',$this->_tree_view_keys))
            {
                $this->_tree_view_keys[] = 'id';
            }
            if(!in_array('guid',$this->_tree_view_keys))
            {
                $this->_tree_view_keys[] = 'guid';
            }
            
            $mc = net_nemein_discussion_post_dba::new_collector('thread', (int) $this->_thread->id);
            foreach($this->_tree_view_keys as $key)
            {
                $mc->add_value_property($key);
            }
            $mc->add_constraint('replyto', '=', 0);

            $childrens = $mc->list_keys();

            foreach ( $childrens as $guid => $array )
            {
                $post_id = $mc->get_subkey($guid, 'id');

                foreach( $this->_tree_view_keys as $key )
                {
                    $this->_post_tree['root'][$key] = $mc->get_subkey($guid, $key);
                }
                
                if( $this->_parent_post->id == $post_id )
                {
                    $this->_post_tree['root']['previous'] = '';
                }
                $this->_post_tree['root']['previous'] = $this->_post_tree['root']['guid'];
                $this->_post_tree['root']['next'] = '';
                
                $this->_post_tree['root']['children'] = $this->_list_post_childs($post_id);
                $this->_post_tree['root']['children_count'] = count($this->_post_tree['root']['children']);
            }
            $_MIDCOM->set_pagetitle($this->_parent_post->subject);
        }
        else
        {
            $_MIDCOM->set_pagetitle($this->_request_data['l10n']->get('reply to') . " {$this->_parent_post->subject}");
        }

        // Set metadata
        $breadcrumb = Array();
        $breadcrumb[] = Array
        (
            MIDCOM_NAV_URL => 'reply/' . $this->_parent_post->guid,
            MIDCOM_NAV_NAME => $this->_parent_post->subject,
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $breadcrumb);

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the article
                $indexer =& $_MIDCOM->get_service('indexer');
                net_nemein_discussion_viewer::index($this->_controller->datamanager, $indexer, $this->_topic);

                $this->_email_post();

                if ($this->_config->get('autoapprove'))
                {
                    $_MIDCOM->auth->request_sudo('net.nemein.discussion');

                    $meta = $this->_post->get_metadata();
                    $meta->approve();

                    $meta = $this->_thread->get_metadata();
                    $meta->approve();

                    $_MIDCOM->auth->drop_sudo();
                }

                // *** FALL THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "read/{$this->_parent_post->guid}/");
                // This will exit.
        }

        $this->_prepare_request_data();

        return true;
    }

    function _show_tree_item($post_tree, $level, $previous_childs = array(), $person_link_prefix)
    {
        $this->_request_data['tree_level'] = $level;
        $this->_request_data['tree_previous_childs'] = $previous_childs;
        $this->_request_data['tree_previous_childs'][$this->_request_data['tree_level']] = count($post_tree['children']);
        $this->_request_data['person_link_prefix'] = $person_link_prefix;
        foreach ( $post_tree['children'] as $this->_request_data['post_for_tree'] )
        {
            midcom_show_style('view-reply-widget-tree-item');
            if( count($this->_request_data['post_for_tree']['children'])> 0 )
            {
                $this->_request_data['tree_level']++;
                midcom_show_style('view-reply-widget-tree-enter-level');
                $this->_show_tree_item( $this->_request_data['post_for_tree'], $this->_request_data['tree_level'], $this->_request_data['tree_previous_childs'], $this->_request_data['person_link_prefix']);
                midcom_show_style('view-reply-widget-tree-exit-level');
                $this->_request_data['tree_level']--;
            }
        }
    }

    /**
     * Show reply form
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_reply($handler_id, &$data)
    {
        // Prepare datamanager for displaying parent
        $data['datamanager'] = new midcom_helper_datamanager2_datamanager($data['schemadb']);
        if (! $data['datamanager']->autoset_storage($data['parent_post']))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("The datamanager for parent post {$data['parent_post']->id} could not be initialized, skipping it.",  MIDCOM_LOG_ERROR);
            debug_print_r('Object was:', $data['parent_post']);
            debug_pop();
            continue;
        }
        $data['view_parent_post'] = $data['datamanager']->get_content_html();

        if ( $this->_config->get('display_thread_mode') == 'tree' )
        {
        
            $data['post_toolbar'] = $this->_populate_post_toolbar($data['parent_post']);

            $node_for_person_link = midcom_helper_find_node_by_component('net.nehmer.account');
            $this->_request_data['person_link_prefix'] = '';
            if ($node_for_person_link)
            {
                $this->_request_data['person_link_prefix'] = "{$node_for_person_link[MIDCOM_NAV_FULLURL]}view/";
            }
            

            $tree_position = $this->_config->get('display_tree_position');
            
            $this->_request_data['tree_level'] = 0;
            midcom_show_style('view-reply-widget-header');
            if( $tree_position == 'bottom' )
            {
                midcom_show_style('view-reply-widget-message');
            }
            midcom_show_style('view-reply-widget-tree-header');
            midcom_show_style('view-reply-widget-tree-item-root');
            if( count($data['post_tree']['root']['children'])> 0 )
            {
                $this->_request_data['tree_level']++;
                midcom_show_style('view-reply-widget-tree-enter-level');
                $tree_previous_childs = array( 0 => 1 );
                $this->_show_tree_item($data['post_tree']['root'], $this->_request_data['tree_level'], $tree_previous_childs, $this->_request_data['person_link_prefix']);
                midcom_show_style('view-reply-widget-tree-exit-level');
                $this->_request_data['tree_level']--;
            }
            midcom_show_style('view-reply-widget-tree-footer');
            midcom_show_style('view-reply-widget-footer');
            if( $tree_position != 'bottom' )
            {
                midcom_show_style('view-reply-widget-message');
            }
        }
        else
        {
            midcom_show_style('reply-widget');
        }
    }

    /**
     * emails $this->_post out to configured address
     */
    function _email_post()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (!$this->_config->get('email_out_enable'))
        {
            debug_add('Outbound emailing not enabled');
            debug_pop();
            // We wish to be silent about this
            return true;
        }
        $to_email = trim($this->_config->get('email_out_to'));
        if (empty($to_email))
        {
            debug_add('Outbound email address empty', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $_MIDCOM->componentloader->load_graceful('org.openpsa.mail');
        if (!class_exists('org_openpsa_mail'))
        {
            debug_add('Could not load org.openpsa.mail library', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $post =& $this->_post;
        // Sanitycheck some values
        if (empty($post->senderemail))
        {
            $post->senderemail = 'noreply@net.nemein.discussion.midcom-project.org';
        }
        if (empty($post->sendername))
        {
            $post->sendername = 'unknown';
            
            if (!empty($post->sender))
            {
                $sender = new midcom_db_person();
                $sender->get_by_id($post->sender);
                if ($sender->guid)
                {
                    $post->sendername = $sender->name;
                }
            }
        }

        $subj_prefix = trim($this->_config->get('email_out_subject_prefix'));

        $mail = new org_openpsa_mail();
        // Set Message-Id so we can use it later
        $mail->headers['Message-Id'] = "<{$post->guid}@net.nemein.discussion-{$_SERVER['SERVER_NAME']}>";
        $mail->to = $to_email;
        $override_from = trim($this->_config->get('email_out_from'));
        if (empty($override_from))
        {
            $mail->from = "\"$post->sendername\" <$post->senderemail>";
        }
        else
        {
            $mail->from = $override_from;
        }
        if (   !empty($subj_prefix)
            && strpos($mail->subject, $subj_prefix) !== false)
        {
            $mail->subject = "{$subj_prefix} {$post->subject}";
        }
        else
        {
            $mail->subject = $post->subject;
        }
        // TODO: Figure out when to use html_body in stead.
        $mail->body = $post->content;

        // Set In-Reply-To and References
        if ($post->replyto)
        {
            $parent = new net_nemein_discussion_post_dba($post->replyto);
            $parent_msgid = $parent->get_parameter('net.nemein.discussion.mailheaders', 'Message-Id');
            $mail->headers['In-Reply-To'] = $parent_msgid;
            $mail->headers['References'] = $parent_msgid;
            while ($parent->replyto)
            {
                $parent = new net_nemein_discussion_post_dba($parent->replyto);
                $parent_msgid = $parent->get_parameter('net.nemein.discussion.mailheaders', 'Message-Id');
                $mail->headers['References'] .= "\t{$parent_msgid}";
            }
        }

        // TODO: Handle attachments ??

        if (!$mail->send())
        {
            debug_add("Failed to send post {$post->guid} via email to {$mail->to}, reason: " . $mail->get_error_message(), MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        debug_add("Sent post {$post->guid} via email to {$mail->to}", MIDCOM_LOG_INFO);

        // store headers for future reference
        foreach ($mail->headers as $header => $value)
        {
            if (empty($value))
            {
                continue;
            }
            if (!$post->set_parameter('net.nemein.discussion.mailheaders', $header, $value))
            {
                debug_add("Could not store header '{$header}' data in parameters", MIDCOM_LOG_WARN);
                // PONDER: abort and clean up ?? (this may affect future imports adversely)
                continue;
            }
        }
        debug_pop();
        return true;
    }
}

?>