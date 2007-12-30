<?php
/**
 * @package midcom.admin.folder
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: configuration.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Handle the folder deleting requests
 *
 * @package midcom.admin.folder
 */
class midcom_admin_folder_handler_delete extends midcom_baseclasses_components_handler
{
    /**
     * Constructor method
     *
     * @access public
     */
    function midcom_admin_folder_handler_delete ()
    {
        parent::midcom_baseclasses_components_handler();
    }

    /**
     * Handler for folder deletion.
     *
     * @access private
     * @param mixed $handler_id The ID of the handler.
     * @param Array $args The argument list.
     * @param Array &$data The local request data.
     * @return boolean Indicating success
     */
    function _handler_delete($handler_id, $args, &$data)
    {
        $this->_topic->require_do('midgard:delete');
        $this->_topic->require_do('midcom.admin.folder:topic_management');

        if (array_key_exists('f_cancel', $_REQUEST))
        {
            $_MIDCOM->relocate('');
            // This will exit.
        }

        if (array_key_exists('f_submit', $_REQUEST))
        {
            if ($this->_process_delete_form())
            {
                $nav = new midcom_helper_nav();
                $node = $nav->get_node($this->_topic->up);
                $_MIDCOM->relocate($node[MIDCOM_NAV_FULLURL]);
                // This will exit.
            }
        }

        $this->_request_data['topic'] = $this->_topic;

        // Add the view to breadcrumb trail
        $tmp = array();
        $tmp[] = array
        (
            MIDCOM_NAV_URL => '__ais/folder/delete.html',
            MIDCOM_NAV_NAME => $_MIDCOM->i18n->get_string('delete folder', 'midcom.admin.folder'),
        );
        $_MIDCOM->set_custom_context_data('midcom.helper.nav.breadcrumb', $tmp);

        // Hide the button in toolbar
        $this->_node_toolbar->hide_item('__ais/folder/delete.html');

        // Set page title
        $data['title'] = sprintf($_MIDCOM->i18n->get_string('delete folder %s', 'midcom.admin.folder'), $data['topic']->extra);
        $_MIDCOM->set_pagetitle($data['title']);

        // Set the help object in the toolbar
        $this->_view_toolbar->add_help_item('delete_folder', 'midcom.admin.folder');

        // Ensure we get the correct styles
        $_MIDCOM->style->prepend_component_styledir('midcom.admin.folder');

        // Add style sheet
        $_MIDCOM->add_link_head
        (
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . '/midcom.admin.folder/folder.css',
            )
        );

        return true;
    }

    /**
     * Removes the folder from indexer if applicable.
     *
     * @access private
     */
    function _delete_topic_update_index()
    {
        if ($GLOBALS['midcom_config']['indexer_backend'] === false)
        {
            // Indexer is not configured.
            return;
        }

        debug_push_class(__CLASS__, __FUNCTION__);

        debug_add("Dropping all NAP registered objects from the index.");

        // First we collect everything we have to delete, this might take a while
        // so we keep an eye on the script timeout.
        $guids = array ();
        $nap = new midcom_helper_nav();

        $node_list = array($nap->get_current_node());

        while (count($node_list) > 0)
        {
            set_time_limit(30);

            // Add the node being processed.
            $nodeid = array_shift($node_list);
            debug_add("Processing node {$nodeid}");

            $node = $nap->get_node($nodeid);
            $guids[] = $node[MIDCOM_NAV_GUID];

            debug_add("Processing leaves of node {$nodeid}");
            $leaves = $nap->list_leaves($nodeid, true);
            debug_add('Got ' . count($leaves) . ' leaves.');
            foreach ($leaves as $leafid)
            {
                $leaf = $nap->get_leaf($leafid);
                $guids[] = $leaf[MIDCOM_NAV_GUID];
            }

            debug_add('Loading subnodes');
            $node_list = array_merge($node_list, $nap->list_nodes($nodeid, true));
            debug_print_r('Remaining node queue', $node_list);
        }

        debug_add('We have to delete ' . count($guids) . ' objects from the index.');

        // Now we go over the entire index and delete the corresponding objects.
        // We load all attachments of the corresponding objects as well, to have
        // them deleted too.
        //
        // Again we keep an eye on the script timeout.
        $indexer =& $_MIDCOM->get_service('indexer');
        foreach ($guids as $guid)
        {
            set_time_limit(60);

            $object = $_MIDCOM->dbfactory->get_object_by_guid($guid);
            if ($object)
            {
                $atts = $object->list_attachments();
                if ($atts)
                {
                    foreach ($atts as $attachment)
                    {
                        debug_add("Deleting attachment {$atts->id} from the index.");
                        $indexer->delete($atts->guid);
                    }
                }
            }

            debug_add("Deleting guid {$guid} from the index.");
            $indexer->delete($guid);
        }

        debug_pop();
    }

    /**
     * Deletes the folder and _midcom_db_article_ objects stored in it.
     *
     * @access private
     */
    function _process_delete_form()
    {
        $this->_delete_topic_update_index();

        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $articles = $qb->execute();

        if (is_null($articles))
        {
            debug_add('Failed to query the articles of this topic: ' . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->_contentadm->msg = 'Error: Could not delete Folder contents: ' . mgd_errstr();
            return false;
        }

        foreach ($articles as $article)
        {
            if (!$article->delete())
            {
                debug_add("Could not delete Article {$article->id}:" . mgd_errstr(), MIDCOM_LOG_ERROR);
                $this->_contentadm->msg = 'Error: Could not delete Folder contents: ' . mgd_errstr();
                return false;
            }
        }

        if (!$this->_topic->delete())
        {
            debug_add("Could not delete Folder {$this->_topic->id}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            $this->_contentadm->msg = 'Error: Could not delete Folder contents: ' . mgd_errstr();
            return false;
        }

        // Invalidate everything since we operate recursive here.
        $_MIDCOM->cache->invalidate_all();

        debug_pop();
        return true;
    }

    /**
     * Shows the _Delete folder_ form.
     *
     * @access private
     */
    function _show_delete($handler_id, &$data)
    {
        $data['folder'] =& $this->_topic;
        midcom_show_style('midcom-admin-show-delete-folder');
    }

    /**
     * List topic contents
     *
     * @access public
     * @static
     * @param int $id Topic ID
     */
    function list_children($id)
    {
        $qb_topic = midcom_db_topic::new_query_builder();
        $qb_topic->add_constraint('up', '=', $id);

        $qb_article = midcom_db_article::new_query_builder();
        $qb_article->add_constraint('topic', '=', $id);

        if (   $qb_topic->count() === 0
            && $qb_article->count() === 0)
        {
            return false;
        }

        echo "<ul class=\"folder_list\">\n";
        foreach ($qb_topic->execute_unchecked() as $topic)
        {
            echo "    <li class=\"node\">\n";
            echo "        <img src=\"".MIDCOM_STATIC_URL."/stock-icons/16x16/stock_folder.png\" alt=\"\" /> {$topic->extra}\n";

            midcom_admin_folder_handler_delete::list_children($topic->id);

            echo "    </li>\n";
        }

        foreach ($qb_article->execute_unchecked() as $article)
        {
            echo "    <li class=\"leaf\">\n";
            echo "        <img src=\"".MIDCOM_STATIC_URL."/stock-icons/16x16/new-text.png\" alt=\"\" /> {$article->title}\n";

            // Check for the reply articles
            $qb = midcom_db_article::new_query_builder();
            $qb->add_constraint('up', '=', $article->id);

            if ($qb->count() > 0)
            {
                echo "        <ul>\n";
                foreach ($qb->execute_unchecked() as $reply)
                {
                    echo "            <li class=\"reply_article\">{$reply->title}</li>\n";
                }
                echo "        </ul>\n";
            }

            echo "    </li>\n";
        }
        echo "</ul>\n";
    }
}
?>