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
    var $_toolbars;

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
    }


    /**
     * Simple default constructor.
     */
    function net_nemein_discussion_handler_post()
    {
        parent::midcom_baseclasses_components_handler();
        $this->_toolbars =& midcom_helper_toolbars::get_instance();        
    }

    /**
     * Loads and prepares the schema database.
     *
     * Special treatement is done for the name field, which is set readonly for non-creates
     * if the simple_name_handling config option is set. (using an auto-generated urlname based
     * on the title, if it is missing.)
     *
     * The operations are done on all available schemas within the DB.
     */
    function _load_schemadb()
    {
        $this->_schemadb = midcom_helper_datamanager2_schema::load_database($this->_config->get('schemadb'));
        // TODO: Be extra smart here about populating/hiding fields
        if ($_MIDCOM->auth->user)
        {
            $user =& $_MIDCOM->auth->user->get_storage();
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
    
    /**
     * Handle thread creation
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
                $this->_thread->latestpost = $this->_post->id;
                $this->_thread->latestposttime = $this->_post->created;
                $i = 0;
                while (   !$this->_thread->update()
                           && $i < 1000)
                {
                    $this->_thread->name = midcom_generate_urlname_from_string($this->_post->subject).sprintf("-%03d",$i);
                    $i++;
                }
                
                // Index the article
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_discussion_viewer::index($this->_controller->datamanager, $indexer, $this->_thread);

                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "read/{$this->_post->guid}.html");
                // This will exit.

            case 'cancel':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX));
                // This will exit.
        }        

        $this->_prepare_request_data();
        $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('post to %s'), $this->_topic->extra));

        $this->_toolbars->bottom->add_item(
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
     */
    function _show_create($handler_id, &$data)
    {
        $this->_request_data['forum_title'] = $this->_topic->extra;
        midcom_show_style('new-thread');
    }

    /**
     * Handle replies to threads
     */
    function _handler_reply($handler_id, $args, &$data)
    {
    
        $this->_parent_post = new net_nemein_discussion_post_dba($args[0]);
        if (!$this->_parent_post)
        {
            return false;
        }
        
        $this->_thread = $this->_parent_post->get_parent();
        
        $this->_thread->require_do('midgard:create');

        $this->_load_controller();

        switch ($this->_controller->process_form())
        {
            case 'save':
                // Index the article
                //$indexer =& $_MIDCOM->get_service('indexer');
                //net_nemein_discussion_viewer::index($this->_controller->datamanager, $indexer, $this->_thread);

                // *** FALL THROUGH ***

            case 'cancel':
                $_MIDCOM->relocate($_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "read/{$this->_post->guid}.html");
                // This will exit.
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_pagetitle(sprintf($this->_request_data['l10n']->get('reply to %s'), $this->_parent_post->subject));

        return true;
    }

    /**
     * Show reply form
     */
    function _show_reply($handler_id, &$data)
    {
        midcom_show_style('reply-widget');
    }
        
}

?>