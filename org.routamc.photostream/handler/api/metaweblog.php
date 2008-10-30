<?php
/**
 * @package org.routamc.photostream
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: metaweblog.php 3991 2006-09-07 11:28:16Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

 /** @ignore */
// Include PEAR XML-RPC library
error_reporting(E_ERROR);
include_once("XML/RPC/Server.php");
error_reporting(E_ALL);

/**
 * MetaWeblog API handler for the blog component
 *
 * @package org.routamc.photostream
 */
class org_routamc_photostream_handler_api_metaweblog extends midcom_baseclasses_components_handler
{
    /**
     * The photo to operate on
     *
     * @var midcom_db_photo
     * @access private
     */
    var $_photo;

    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    var $_positioning = false;

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Maps the content topic from the request data to local member variables.
     */
    function _on_initialize()
    {
        $this->_content_topic =& $this->_request_data['content_topic'];

        if (!class_exists('XML_RPC_Server'))
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'XML-RPC Server libraries not installer, aborting.');
        }

        if ($GLOBALS['midcom_config']['positioning_enable'])
        {
            if (!class_exists('org_routamc_positioning_object'))
            {
                // Load the positioning library
                $_MIDCOM->load_library('org.routamc.positioning');
            }
            $this->_positioning = true;
        }
    }

    /**
     * DM2 creation callback, binds to the current content topic.
     */
    function _create_photo($title)
    {
        $author = $_MIDCOM->auth->user->get_storage();

        $photo = new midcom_db_photo();
        $photo->topic = $this->_content_topic->id;
        $photo->title = $title;

        //Figure out author
        $photo->author = $author->id;

        if (! $photo->create())
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_print_r('We operated on this object:', $photo);
            debug_pop();
            return null;
        }

        // Generate URL name
        if ($photo->name == '')
        {
            $photo->name = midcom_generate_urlname_from_string($photo->title);
            $tries = 0;
            $maxtries = 999;
            while(   !$photo->update()
                  && $tries < $maxtries)
            {
                $photo->name = midcom_generate_urlname_from_string($photo->title);
                if ($tries > 0)
                {
                    // Append an integer if photos with same name exist
                    $photo->name .= sprintf("-%03d", $tries);
                }
                $tries++;
            }
        }

        $photo->parameter('midcom.helper.datamanager2', 'schema_name', $this->_config->get('api_metaweblog_schema'));

        return $photo;
    }

    /**
     * Internal helper, loads the datamanager for the current photo. Any error triggers a 500.
     *
     * @access private
     */
    function _load_datamanager()
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        if (!$this->_datamanager)
        {
            $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "Failed to create a DM2 instance.");
            // This will exit.
        }
    }

    function _params_to_args($message)
    {
        $args = array();

        foreach ($message->params as $param)
        {
            $args[] = XML_RPC_decode($param);
        }

        return $args;
    }

    // metaWeblog.newPost
    function newPost($message)
    {
        $args = $this->_params_to_args($message);

        if (count($args) != 5)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid arguments.');
        }

        if ($args[0] != $this->_content_topic->guid)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Blog ID does not match this folder.');
        }

        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();

        if (   !array_key_exists('title', $args[3])
            || $args[3]['title'] == '')
        {
            // Create photo with title coming from datetime
            $new_title = strftime('%x %X');
        }
        else
        {
            $new_title = html_entity_decode($args[3]['title'], ENT_QUOTES, 'UTF-8');
        }

        $photo = $this->_create_photo($new_title);
        if (   !$photo
            || !$photo->guid)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to create photo: ' . mgd_errstr());
        }

        if (!$this->_datamanager->autoset_storage($photo))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to initialize DM2 for photo: ' . mgd_errstr());
        }

        foreach ($args[3] as $field => $value)
        {
            switch ($field)
            {
                case 'title':
                    $this->_datamanager->types['title']->value = $new_title;
                    break;

                case 'mt_excerpt':
                    $this->_datamanager->types['abstract']->value = $value;
                    break;

                case 'description':
                    $this->_datamanager->types['content']->value = $value;
                    break;

                case 'link':
                    // TODO: We may have to bulletproof this a bit
                    $this->_datamanager->types['name']->value = str_replace('.html', '', basename($args[3]['link']));
                    break;

                case 'categories':
                    if (array_key_exists('categories', $this->_datamanager->types))
                    {
                        $this->_datamanager->types['categories']->selection = $value;
                        break;
                    }

                case 'http://www.georss.org/georss/':
                    if ($this->_positioning)
                    {
                        foreach ($value as $feature => $val)
                        {
                            switch ($feature)
                            {
                                case 'point':

                                    $coordinates = explode(' ', $val);
                                    if (count($coordinates) != 2)
                                    {
                                        break;
                                    }

                                    $log = new org_routamc_positioning_log_dba();
                                    $log->date = $photo->metadata->published;
                                    $log->latitude = (float) $coordinates[0];
                                    $log->longitude = (float) $coordinates[1];
                                    $log->accuracy = ORG_ROUTAMC_POSITIONING_ACCURACY_MANUAL;
                                    $log->create();

                                    break;
                            }
                            // TODO: Handle different relationshiptags as per http://georss.org/simple.html
                        }
                    }
                    break;
            }
        }

        if (!$this->_datamanager->save())
        {
            $photo->delete();
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to update photo: ' . mgd_errstr());
        }

        // TODO: Map the publish property to approval

        // Index the photo
        $indexer =& $_MIDCOM->get_service('indexer');
        org_routamc_photostream_viewer::index($this->_datamanager, $indexer, $this->_content_topic);

        return new XML_RPC_Response(new XML_RPC_Value($photo->guid, 'string'));
    }

    // metaWeblog.getPost
    function getPost($message)
    {
        $args = $this->_params_to_args($message);

        if (count($args) != 3)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid arguments.');
        }

        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();

        $photo = new midcom_db_photo($args[0]);
        if (!$photo)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'photo not found: ' . mgd_errstr());
        }

        if (!$this->_datamanager->autoset_storage($photo))
        {
           return new XML_RPC_Response(0, mgd_errno(), 'Failed to load DM2 for the photo.');
        }

        $arg = $photo->name ? $photo->name : $photo->guid;
        if ($this->_config->get('view_in_url'))
        {
            $link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "view/{$arg}/";
        }
        else
        {
            $link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$arg}/";
        }

        if (array_key_exists('categories', $this->_datamanager->types))
        {
            $categories = $this->_datamanager->types['categories']->selection;
        }
        else
        {
            $categories = array();
        }

        $response_array = array
        (
            'postid'      => new XML_RPC_Value($photo->guid, 'string'),
            'title'       => new XML_RPC_Value($photo->title, 'string'),
            'permaLink'   => new XML_RPC_Value($_MIDCOM->permalinks->create_permalink($photo->guid), 'string'),
            'link'        => new XML_RPC_Value($link, 'string'),
            'description' => new XML_RPC_Value($photo->content, 'string'),
            'mt_excerpt'  => new XML_RPC_Value($photo->abstract, 'string'),
            'dateCreated' => new XML_RPC_Value(gmdate("Ymd\TH:i:s\Z", $photo->metadata->published), 'dateTime.iso8601'),
            'categories'  => XML_RPC_encode($categories),
        );

        if ($this->_positioning)
        {
            $object_position = new org_routamc_positioning_object($photo);
            $coordinates = $object_position->get_coordinates();
            $georss_array = array
            (
                'point' => new XML_RPC_Value("{$coordinates['latitude']} {$coordinates['longitude']}", 'string'),
            );
            $response_array['http://www.georss.org/georss/'] = new XML_RPC_Value($georss_array, 'struct');
        }

        return new XML_RPC_Response(new XML_RPC_Value($response_array, 'struct'));
    }


    // metaWeblog.editPost
    function editPost($message)
    {
        $args = $this->_params_to_args($message);

        if (count($args) != 5)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid arguments.');
        }

        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();

        $photo = new midcom_db_photo($args[0]);
        if (!$photo)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'photo not found: ' . mgd_errstr());
        }

        if (!$this->_datamanager->autoset_storage($photo))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to initialize DM2 for photo: ' . mgd_errstr());
        }

        foreach ($args[3] as $field => $value)
        {
            switch ($field)
            {
                case 'title':
                    $this->_datamanager->types['title']->value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                    break;

                case 'mt_excerpt':
                    $this->_datamanager->types['abstract']->value = $value;
                    break;

                case 'description':
                    $this->_datamanager->types['content']->value = $value;
                    break;

                case 'link':
                    // TODO: We may have to bulletproof this a bit
                    $this->_datamanager->types['name']->value = str_replace('.html', '', basename($args[3]['link']));
                    break;

                case 'categories':
                    if (array_key_exists('categories', $this->_datamanager->types))
                    {
                        $this->_datamanager->types['categories']->selection = $value;
                        break;
                    }

                case 'http://www.georss.org/georss/':
                    if ($this->_positioning)
                    {
                        foreach ($value as $feature => $val)
                        {
                            switch ($feature)
                            {
                                case 'point':

                                    $coordinates = explode(' ', $val);
                                    if (count($coordinates) != 2)
                                    {
                                        break;
                                    }

                                    $log = new org_routamc_positioning_log_dba();
                                    $log->date = $photo->metadata->published;
                                    $log->latitude = (float) $coordinates[0];
                                    $log->longitude = (float) $coordinates[1];
                                    $log->accuracy = ORG_ROUTAMC_POSITIONING_ACCURACY_MANUAL;
                                    $log->create();

                                    break;
                            }
                            // TODO: Handle different relationshiptags as per http://georss.org/simple.html
                        }
                    }
                    break;
            }
        }

        if (!$this->_datamanager->save())
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to update photo: ' . mgd_errstr());
        }

        // TODO: Map the publish property to approval

        // Index the photo
        $indexer =& $_MIDCOM->get_service('indexer');
        org_routamc_photostream_viewer::index($this->_datamanager, $indexer, $this->_content_topic);

        return new XML_RPC_Response(new XML_RPC_Value($photo->guid, 'string'));
    }

    // metaWeblog.getRecentPosts
    function getRecentPosts($message)
    {
        $args = $this->_params_to_args($message);

        if (count($args) != 4)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid arguments.');
        }

        if ($args[0] != $this->_content_topic->guid)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Blog ID does not match this folder.');
        }

        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();

        $response = array();

        $qb = midcom_db_photo::new_query_builder();
        $qb->set_limit($args[3]);
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_order('metadata.published', 'DESC');

        $photos = $qb->execute();
        foreach ($photos as $photo)
        {
            if (!$this->_datamanager->autoset_storage($photo))
            {
                // This photo has something wrong, skip it
                continue;
            }

            $arg = $photo->name ? $photo->name : $photo->guid;
            if ($this->_config->get('view_in_url'))
            {
                $link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "view/{$arg}/";
            }
            else
            {
                $link = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX) . "{$arg}/";
            }

            if (array_key_exists('categories', $this->_datamanager->types))
            {
                $categories = $this->_datamanager->types['categories']->selection;
            }
            else
            {
                $categories = array();
            }

            $response_array = array
            (
                'postid'      => new XML_RPC_Value($photo->guid, 'string'),
                'title'       => new XML_RPC_Value($photo->title, 'string'),
                'permaLink'   => new XML_RPC_Value($_MIDCOM->permalinks->create_permalink($photo->guid), 'string'),
                'link'        => new XML_RPC_Value($link, 'string'),
                'description' => new XML_RPC_Value($photo->content, 'string'),
                'mt_excerpt'  => new XML_RPC_Value($photo->abstract, 'string'),
                'dateCreated' => new XML_RPC_Value(gmdate("Ymd\TH:i:s\Z", $photo->metadata->published), 'dateTime.iso8601'),
                'categories'  => XML_RPC_encode($categories),
            );

            if ($this->_positioning)
            {
                $object_position = new org_routamc_positioning_object($photo);
                $coordinates = $object_position->get_coordinates();
                $response_array['georss:point'] = new XML_RPC_Value("{$coordinates['latitude']} {$coordinates['longitude']}", 'string');
            }

            $response[] = new XML_RPC_Value($response_array, 'struct');
        }

        return new XML_RPC_Response(new XML_RPC_Value($response, 'array'));
    }

    // metaWeblog.getCategories
    function getCategories($message)
    {
        $args = $this->_params_to_args($message);

        if (count($args) != 3)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid arguments.');
        }

        if ($args[0] != $this->_content_topic->guid)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Blog ID does not match this folder.');
        }

        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();

        $response = array();

        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

        foreach ($this->_request_data['categories'] as $category)
        {
            $response_array = array
            (
                'description' => new XML_RPC_Value($category, 'string'),
                'htmlUrl' => new XML_RPC_Value("{$prefix}category/" . rawurlencode($category), 'string'),
                'rssUrl' => new XML_RPC_Value("{$prefix}feeds/category/" . rawurlencode($category), 'string'),
            );

            $response[$category] = new XML_RPC_Value($response_array, 'struct');
        }

        return new XML_RPC_Response(new XML_RPC_Value($response, 'struct'));
    }

    // metaWeblog.newMediaObject
    function newMediaObject($message)
    {
        $args = $this->_params_to_args($message);

        if (count($args) != 4)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid arguments.');
        }

        if ($args[0] != $this->_content_topic->guid)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Blog ID does not match this folder.');
        }

        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();

        if (count($args) < 3)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid file data.');
        }

        if (!$args[3]['name'])
        {
            return new XML_RPC_Response(0, mgd_errno(), 'No filename given.');
        }

        // Clean up possible path information
        $attachment_name = basename($args[3]['name']);

        $attachment = $this->_content_topic->get_attachment($attachment_name);
        if (!$attachment)
        {
            // Create new attachment
            $attachment = $this->_content_topic->create_attachment($attachment_name, $args[3]['name'], $args[3]['type']);

            if (!$attachment)
            {
                return new XML_RPC_Response(0, mgd_errno(), 'Failed to create attachment: ' . mgd_errstr());
            }
        }

        if (!$attachment->copy_from_memory($args[3]['bits']))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to store contents to attachment: ' . mgd_errstr());
        }

        $attachment_array = array
        (
            'url'  => new XML_RPC_Value("{$GLOBALS['midcom_config']['midcom_site_url']}midcom-serveattachmentguid-{$attachment->guid}/{$attachment->name}", 'string'),
            'guid' => new XML_RPC_Value($attachment->guid, 'string'),
        );
        return new XML_RPC_Response(new XML_RPC_Value($attachment_array, 'struct'));
    }

    // blogger.deletePost
    function deletePost($message)
    {
        $args = $this->_params_to_args($message);

        if (count($args) != 5)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid arguments.');
        }

        if (!mgd_auth_midgard($args[2], $args[3]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();

        $photo = new midcom_db_photo($args[1]);
        if (!$photo)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'photo not found: ' . mgd_errstr());
        }

        if (!$photo->delete())
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Failed to delete photo: ' . mgd_errstr());
        }

        // Update the index
        $indexer =& $_MIDCOM->get_service('indexer');
        $indexer->delete($photo->guid);

        return new XML_RPC_Response(new XML_RPC_Value(true, 'boolean'));
    }

    // metaWeblog.getUsersBlogs
    function getUsersBlogs($message)
    {
        $args = $this->_params_to_args($message);

        if (count($args) != 3)
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Invalid arguments.');
        }

        if (!mgd_auth_midgard($args[1], $args[2]))
        {
            return new XML_RPC_Response(0, mgd_errno(), 'Authentication failed.');
        }
        $_MIDCOM->auth->initialize();

        $response = array();

        $topic = $this->_topic;
        if (!$topic->can_do('midgard:create'))
        {
            // Skip this blog, user cannot edit
            continue;
        }

        $nap = new midcom_helper_nav();
        $node = $nap->get_node($topic->id);
        if (!$node)
        {
            // This topic isn't on site
            continue;
        }

        $response_array = array
        (
            'url'      => new XML_RPC_Value($node[MIDCOM_NAV_FULLURL], 'string'),
            'blogid'   => new XML_RPC_Value($topic->guid, 'string'),
            'blogName' => new XML_RPC_Value($node[MIDCOM_NAV_NAME], 'string'),
        );

        $response[] = new XML_RPC_Value($response_array, 'struct');

        return new XML_RPC_Response(new XML_RPC_Value($response, 'array'));
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_rsd($handler_id, $args, &$data)
    {
        //Content-Type
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->content_type('text/xml');

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_rsd($handler_id, &$data)
    {
        $data['content_topic'] = $this->_content_topic;
        midcom_show_style('rsd');
    }

    /**
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success.
     */
    function _handler_server($handler_id, $args, &$data)
    {
        if (!$this->_config->get('api_metaweblog_enable'))
        {
            return false;
        }

        //Content-Type
        $_MIDCOM->skip_page_style = true;
        $_MIDCOM->cache->content->no_cache();
        $_MIDCOM->cache->content->content_type('text/xml');

        $this->_load_datamanager();

        // Populate the XML-RPC dispatch map
        $data['dispatchmap'] = array
        (
            // MetaWebLog API
            'metaWeblog.newPost' => array
            (
                'function' => array($this, 'newPost'),
            ),
            'metaWeblog.getPost' => array
            (
                'function' => array($this, 'getPost'),
            ),
            'metaWeblog.editPost' => array
            (
                'function' => array($this, 'editPost'),
            ),
            'metaWeblog.getRecentPosts' => array
            (
                'function' => array($this, 'getRecentPosts'),
            ),
            'metaWeblog.getCategories' => array
            (
                'function' => array($this, 'getCategories'),
            ),
            'metaWeblog.newMediaObject' => array
            (
                'function' => array($this, 'newMediaObject'),
            ),
            // Blogger API
            'blogger.deletePost' => array
            (
                'function' => array($this, 'deletePost'),
            ),
            'blogger.getUsersBlogs' => array
            (
                'function' => array($this, 'getUsersBlogs'),
            )
        );

        return true;
    }

    /**
     *
     * @param mixed $handler_id The ID of the handler.
     * @param mixed &$data The local request data.
     */
    function _show_server($handler_id, &$data)
    {
        // Serve the RPC request
        $server = new XML_RPC_Server($data['dispatchmap']);
    }
}