<?php
/**
 * @package net.nehmer.comments
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Comments view handler.
 *
 * This handler is a sigle handler which displays the thread for a given object GUID.
 * It checks for various commands in $_REQUEST during startup and processes them
 * if applicable. It relocates to the same page (using $_SERVER info) to prevent
 * duplicate request runs.
 *
 * @package net.nehmer.comments
 */

class net_nehmer_comments_handler_view extends midcom_baseclasses_components_handler
{
    function net_nehmer_comments_handler_view()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * The schema database to use.
     *
     * @var Array
     * @access private
     */
    var $_schemadb = null;

    /**
     * List of comments we are currently working with.
     *
     * @var Array
     * @access private
     */
    var $_comments = null;

    /**
     * A new comment just created for posting.
     *
     * @var net_nehmer_comments_comment
     * @access private
     */
    var $_new_comment = null;

    /**
     * The GUID of the object we're bound to.
     *
     * @var guid
     * @access private
     */
    var $_objectguid = null;

    /**
     * The controller used to post a new comment. Only set if we have a valid user.
     *
     * This is a Creation Mode DM2 controller.
     *
     * @var midcom_helper_datamanager2_controller_create
     * @access private
     */
    var $_post_controller = null;

    /**
     * This datamanager instance is used to display an existing comment. only set
     * if there are actually comments to display.
     *
     * @var midcom_helper_datamanager2_datamanager
     * @access private
     */
    var $_display_datamanager = null;
    
    var $custom_view = null;
    
    /**
     * Prepares the request data
     */
    function _prepare_request_data()
    {
        $this->_request_data['comments'] =& $this->_comments;
        $this->_request_data['objectguid'] =& $this->_objectguid;
        $this->_request_data['post_controller'] =& $this->_post_controller;
        $this->_request_data['display_datamanager'] =& $this->_display_datamanager;
        $this->_request_data['custom_view'] =& $this->custom_view;
    }
    
    /**
     * Update possible ratings cache as requested in configuration
     */
    function _cache_ratings()
    {
        if (   $this->_config->get('ratings_enable')
            && ( $this->_config->get('ratings_cache_to_object')
		|| $this->_config->get('comment_count_cache_to_object'))   )
        {
            // Handle ratings
            $comments = net_nehmer_comments_comment::list_by_objectguid($this->_objectguid);
            $ratings_total = 0;
            $rating_comments = 0;
            foreach ($comments as $comment)
            {
                if (   isset($comment->rating)
                    && !empty($comment->rating))
                {
                    $rating_comments++;
                    $ratings_total += $comment->rating;
                }
            }
            
            // Get parent object
            $parent_property = $this->_config->get('ratings_cache_to_object_property');
            $_MIDCOM->auth->request_sudo('net.nehmer.comments');
            if ($this->_config->get('ratings_cache_total'))
            {
                $value = $ratings_total;
            }
            else
            {
                $value = $ratings_total / $rating_comments;
            }
            
            if ($this->_config->get('ratings_cache_to_object_property_metadata'))
            {
                $metadata = midcom_helper_metadata::retrieve($this->_objectguid);
                $metadata->set($parent_property, round($value));
            }
            else
            {
                $parent_object = $_MIDCOM->dbfactory->get_object_by_guid($this->_objectguid);
                // TODO: Figure out whether to round
                $parent_object->$parent_property = $value;
                $parent_object->update();
            }

            // Get parent object
            $parent_property = $this->_config->get('comment_count_cache_to_object_property');
            if ($this->_config->get('comment_count_cache_to_object_property_metadata'))
            {
                $metadata = midcom_helper_metadata::retrieve($this->_objectguid);
                $metadata->set($parent_property, count($comments));
			}
            else
            {
                $parent_object = $_MIDCOM->dbfactory->get_object_by_guid($this->_objectguid);
                $parent_object->$parent_property = count($comments);
                $parent_object->update();
            }
            $_MIDCOM->auth->drop_sudo();
        }
    }

    function _resolve_object_title($object)
    {
        $vars = get_object_vars($object);
        
        if (array_key_exists('title', $vars)) 
        {
            return $object->title;
        } 
        elseif (array_key_exists('name', $vars)) 
        {
            return $object->name;
        }
        else
        {
            return "#{$object->id}";
        }
    }
    
    function _notify_authors()
    {   
        $parent_metadata = midcom_helper_metadata::retrieve($this->_objectguid);
        if (!$parent_metadata)
        {
            return false;
        }
        
        if (! $this->_config->get('enable_notify'))
        {
            return false;
        }
        
        $authors_string = $parent_metadata->get('authors');
        $authors = explode('|', substr($authors_string, 1, -1));
        if (empty($authors))
        {
            // Fall back to original creator if authors are not set for some reason
            $authors = array();
            $authors[] = $parent_metadata->get('creator');
        }
            
        if (empty($authors))
        {
            return false;
        }
        
        // Construct the message
        $message = array();
        
        // Resolve parent title
        $parent_object = $_MIDCOM->dbfactory->get_object_by_guid($this->_objectguid);
        $parent_title = $this->_resolve_object_title($parent_object);

        // Resolve commenting user
        $user =& $_MIDCOM->auth->get_user($this->_new_comment->metadata->creator);
        if ($user)
        {
            $user_string = "{$user->name} ({$user->username})";
        }
        else
        {
            $user_string = "{$this->_new_comment->author} (" . $data['l10n_midcom']->get('anonymous') . ")";
        }
        
        $message['title'] = sprintf($this->_l10n->get('page %s has been commented by %s'), $parent_title, $user_string);

        $message['content']  = "{$this->_new_comment->title}\n";
        $message['content'] .= "{$this->_new_comment->content}\n\n";
        $message['content'] .= $_MIDCOM->i18n->get_string('link to page', 'net.nemein.wiki') . ":\n";
        $message['content'] .= $_MIDCOM->permalinks->create_permalink($this->_objectguid);
                
        $message['abstract'] = $message['title'];
        
        foreach ($authors as $author)
        {
            // Send the notification to each author of the original document
            org_openpsa_notifications::notify('net.nehmer.comments:comment_posted', $author, $message);
        }
    }

    /**
     * Prepares the _display_datamanager member.
     *
     * @access private
     */
    function _init_display_datamanager()
    {
        $this->_load_schemadb();
        $this->_display_datamanager = new midcom_helper_datamanager2_datamanager($this->_schemadb);

        if (! $this->_display_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to create a DM2 instance (display_datamanager).');
            // This will exit.
        }
    }

    /**
     * Loads the schemadb (unless it has already been loaded).
     */
    function _load_schemadb()
    {
        if (! $this->_schemadb)
        {
            $this->_schemadb = midcom_helper_datamanager2_schema::load_database(
                $this->_config->get('schemadb'));
            
            if (   $this->_config->get('use_captcha')
                || (   ! $_MIDCOM->auth->user 
                    && $this->_config->get('use_captcha_if_anonymous')))
            {
                $this->_schemadb['comment']->append_field
                (
                    'captcha',
                    array
                    (
                        'title' => $this->_l10n_midcom->get('captcha field title'),
                        'storage' => null,
                        'type' => 'captcha',
                        'widget' => 'captcha',
                        'widget_config' => $this->_config->get('captcha_config'),
                    )
                );
            }
            
            if (   $this->_config->get('ratings_enable')
                && array_key_exists('rating', $this->_schemadb['comment']->fields))
            {
                $this->_schemadb['comment']->fields['rating']['hidden'] = false;
            }
        }
    }

    /**
     * Initializes a DM2 for posting.
     */
    function _init_post_controller()
    {
        $this->_load_schemadb();

        $defaults = Array();
        if ($_MIDCOM->auth->user)
        {
            $defaults['author'] = $_MIDCOM->auth->user->name;
        }

        $this->_post_controller = midcom_helper_datamanager2_controller::create('create');
        $this->_post_controller->schemadb =& $this->_schemadb;
        $this->_post_controller->schema = 'comment';
        $this->_post_controller->defaults = $defaults;
        $this->_post_controller->callback_object =& $this;

        if (! $this->_post_controller->initialize())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to initialize a DM2 create controller.');
            // This will exit.
        }
    }

    /**
     * DM2 creation callback, binds the new object directly to the _objectguid.
     */
    function & dm2_create_callback (&$controller)
    {
        $this->_new_comment = new net_nehmer_comments_comment();
        $this->_new_comment->objectguid = $this->_objectguid;
        $this->_new_comment->ip = $_SERVER['REMOTE_ADDR'];

        if (! $this->_new_comment->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $this->_new_comment);
            debug_pop();
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'Failed to create a new comment, cannot continue. Last Midgard error was: '. mgd_errstr());
            // This will exit.
        }

        return $this->_new_comment;
    }


    /**
     * Loads the comments, does any processing according to the state of the GET list.
     * On successful processing we relocate once to ourself.
     */
    function _handler_comments($handler_id, $args, &$data)
    {
        if (! mgd_is_guid($args[0]))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                "The GUID '{$args[0]}' is invalid. Cannot continue.");
            // This will exit.
        }

        $this->_objectguid = $args[0];
        if ($handler_id == 'view-comments-nonempty')
        {
            $this->_comments = net_nehmer_comments_comment::list_by_objectguid_filter_anonymous(
            $this->_objectguid,
            $this->_config->get('items_to_show'),
            $this->_config->get('item_ordering')
            );
        }
        else
        {
            $this->_comments = net_nehmer_comments_comment::list_by_objectguid(
            $this->_objectguid,
            $this->_config->get('items_to_show'),
            $this->_config->get('item_ordering')
            );
        }

        if (   $_MIDCOM->auth->user
            || $this->_config->get('allow_anonymous'))
        {
            $this->_init_post_controller();
            $this->_process_post();
            // This might exit.
        }
        if ($this->_comments)
        {
            $this->_init_display_datamanager();
        }

        $this->_process_admintoolbar();
        // This might exit.

        if (   $handler_id = 'view-comments-custom'
            && count($args) > 1)
        {
            $_MIDCOM->skip_page_style = true;
            $this->custom_view = $args[1];
        }

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata($this->_get_last_modified(), $this->_objectguid);

        return true;
    }

    /**
     * Checks if an button of the admin toolbar was pressed. Detected by looking for the
     * net_nehmer_comment_adminsubmit value in the Request.
     *
     * As of this point, this tool assumes at least owner level privileges for all
     */
    function _process_admintoolbar()
    {
        if (! array_key_exists('net_nehmer_comment_adminsubmit', $_REQUEST))
        {
            // Nothing to do.
            return;
        }

        if (array_key_exists('action_delete', $_REQUEST))
        {
            $comment = new net_nehmer_comments_comment($_REQUEST['guid']);
            if (! $comment)
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Request data invalid, the GUID '{$_REQUEST['guid']}' does not exist.");
                // This will exit;
            }
            if (! $comment->delete())
            {
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    "Failed to delete comment GUID '{$_REQUEST['guid']}': " . mgderrstr());
                // This will exit;
            }
            
            $this->_cache_ratings();

            $this->_relocate_to_self();
        }
    }

    /**
     * Checks if a new post has been submitted.
     */
    function _process_post()
    {
        if (   ! $_MIDCOM->auth->user
            && ! $_MIDCOM->auth->request_sudo())
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                'We were anonymous but could not acquire SUDO privileges, aborting');
            // This will exit.
        }
        
        switch ($this->_post_controller->process_form())
        {
            case 'save':
                $this->_cache_ratings();
                $this->_notify_authors();
                // Fall-through intentional
                
            case 'cancel':
                if (! $_MIDCOM->auth->user)
                {
                    $_MIDCOM->auth->drop_sudo();
                }
                $this->_relocate_to_self();
                // This will exit();
        }
    }

    /**
     * Determines the last modified timestamp. It is the max out of all revised timestamps
     * of the comments (or 0 in case nothing was found).
     *
     * @return int Last-Modified Timestamp
     */
    function _get_last_modified()
    {
        if (! $this->_comments)
        {
            return 0;
        }
        
        if (version_compare(mgd_version(), '1.8', '>='))
        {
            $lastmod = $this->_comments[0]->metadata->revised;
        }
        else
        {
            $lastmod = $this->_comments[0]->revised;
        }
        
        foreach ($this->_comments as $comment)
        {
            if (version_compare(mgd_version(), '1.8', '>='))
            {
                // TODO Workaround for #134
                if (! $comment->metadata->revised)
                {
                    $comment->metadata->revised = $comment->metadata->created;
                }
                if ($comment->metadata->revised > $lastmod)
                {
                    $lastmod = $comment->metadata->revised;
                }
            }
            else
            {
                if ($comment->revised > $lastmod)
                {
                    $lastmod = $comment->revised;
                }

            }
        }

        if ($lastmod)
        {
            return strtotime($lastmod);
        }
        else
        {
            return 0;
        }
    }

    /**
     * This is a shortcut for $_MIDCOM->relocate which relocates to the very same page we
     * are viewing right now, including all GET parameters we had in the original request.
     * We do this by taking the $_SERVER['REQUEST_URI'] variable.
     */
    function _relocate_to_self()
    {
        $_MIDCOM->relocate($_SERVER['REQUEST_URI']);
        // This will exit.
    }

    /**
     * Display the comment list and the submit-comment form.
     */
    function _show_comments($handler_id, &$data)
    {
        midcom_show_style('comments-header');
        if ($this->_comments)
        {
            midcom_show_style('comments-start');
            foreach ($this->_comments as $comment)
            {
                $this->_display_datamanager->autoset_storage($comment);
                $data['comment'] =& $comment;
                midcom_show_style('comments-item');

                if (   $_MIDCOM->auth->admin
                    || (   $_MIDCOM->auth->user
                        && $comment->can_do('midgard:delete')))
                {
                    midcom_show_style('comments-admintoolbar');
                }
            }
            midcom_show_style('comments-end');
        }
        else
        {
            midcom_show_style('comments-nonefound');
        }

        if (   $_MIDCOM->auth->user
            || $this->_config->get('allow_anonymous'))
        {
            midcom_show_style('post-comment');
        }
        else
        {
            midcom_show_style('post-denied');
        }
        midcom_show_style('comments-footer');
    }

}

?>
