<?php
/**
 * @package net.nehmer.static
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAViewer Autoindex page handler
 *
 * @package net.nehmer.static
 */

class net_nehmer_static_handler_autoindex extends midcom_baseclasses_components_handler
{
    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * Simple default constructor.
     */
    function net_nehmer_static_handler_autoindex()
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

    /**
     * Shows the autoindex list. Nothing to do in the handle phase except setting last modified
     * dates.
     */
    function _handler_autoindex ($handler_id, $args, &$data)
    {
        // Get last modified timestamp
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_topic->id);
        $qb->add_order('metadata.revised', 'DESC');
        $qb->set_limit(1);
        $result = $qb->execute();
        if ($result)
        {
            $article_time = $result[0]->metadata->revised;
        }
        else
        {
            $article_time = 0;
        }
        $topic_time = $this->_topic->metadata->revised;
        $_MIDCOM->set_26_request_metadata(max($article_time, $topic_time), null);
        return true;
    }

    /**
     * Displays the autoindex of the Taviewer. This is a list of all articles and attachments on
     * the current topic.
     *
     * The globals view_title, view_l10n and view_l10n_midcom are populated for compatibility reasons
     * only, they have been superseeded
     * by the corresponding request data key. The global will be dropped after MidCOM 2.6.
     *
     * @deprecate The globals view_title, view_l10n and view_l10n_midcom will be deprecated after MidCOM 2.6.
     */
    function _show_autoindex($handler_id, &$data)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        midcom_show_style('autoindex-start');

        $view = $this->_load_autoindex_data();

        if (count ($view) > 0)
        {
            foreach ($view as $filename => $thedata)
            {
                $data['filename'] = $filename;
                $data['data'] = $thedata;
                midcom_show_style('autoindex-item');
            }
        }
        else
        {
            midcom_show_style('autoindex-directory-empty');
        }

        midcom_show_style('autoindex-end');

        debug_pop();
        return true;
    }

    /**
     * This helper function goes over the topic and loads all available objects for displaying
     * in the autoindex.
     *
     * It will populate the request data key 'create_urls' as well. See the view handler for
     * further details.
     *
     * The computed array has the following keys:
     *
     * - string name: The name of the object.
     * - string url: The full URL to the object.
     * - string size: The formatted size of the document. This is only populated for attachments.
     * - string desc: The object title/description.
     * - string type: The MIME Type of the object.
     * - string lastmod: The localized last modified date.
     *
     * @return Array Autoindex objectes as outlined above
     */
    function _load_autoindex_data()
    {
        $view = Array();

        $datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);

        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $result = $qb->execute();

        if ($result)
        {
            foreach ($result as $article)
            {
                if (! $datamanager->autoset_storage($article))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The datamanager for article {$article->id} could not be initialized, skipping it.");
                    debug_print_r('Object was:', $article);
                    debug_pop();
                    continue;
                }

                $this->_process_datamanager($datamanager, $article, $view);
            }
        }
        ksort ($view);
        return $view;
    }

    /**
     * Converts the main document to a view entry.
     */
    function _process_datamanager (&$datamanager, &$article, &$view)
    {
        $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
        $filename = "{$article->name}.html";

        $view[$filename]['name'] = $filename;
        $view[$filename]['url'] = $prefix . $filename;
        $view[$filename]['size'] = '?';
        $view[$filename]['desc'] = $datamanager->types['title']->value;
        $view[$filename]['type'] = 'text/html';
        $view[$filename]['lastmod'] = strftime('%x %X', $article->metadata->revised);

        foreach ($datamanager->schema->field_order as $name)
        {
            if (is_a($datamanager->types[$name], 'midcom_helper_datamanager2_type_image'))
            {
                if ($datamanager->types[$name]->attachments_info)
                {
                    $data = $datamanager->types[$name]->attachments_info['main'];
                    $filename = "{$article->name}.html/{$data['filename']}";
                    $view[$filename]['name'] = $filename;
                    $view[$filename]['url'] = $data['url'];
                    $view[$filename]['size'] = $data['formattedsize'];
                    $view[$filename]['desc'] = $data['filename'];
                    $view[$filename]['type'] = $data['mimetype'];
                    $view[$filename]['lastmod'] = strftime('%x %X', $data['lastmod']);
                }
            }
            else if (is_a($datamanager->types[$name], 'midcom_helper_datamanager2_type_blobs'))
            {
                foreach ($datamanager->types[$name]->attachments_info as $identifier => $data)
                {
                    $filename = "{$article->name}.html/{$data['filename']}";
                    $view[$filename]['name'] = $filename;
                    $view[$filename]['url'] = $data['url'];
                    $view[$filename]['size'] = $data['formattedsize'];
                    $view[$filename]['desc'] = $data['filename'];
                    $view[$filename]['type'] = $data['mimetype'];
                    $view[$filename]['lastmod'] = strftime('%x %X', $data['lastmod']);
                }
            }
        }

    }

}

?>