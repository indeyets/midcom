<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Blog Index handler page handler
 *
 * Shows the configured number of postings with their abstracts.
 *
 * @package net.nehmer.blog
 */

class net_nehmer_blog_handler_index extends midcom_baseclasses_components_handler
{
    /**
     * The content topic to use
     *
     * @var midcom_db_topic
     * @access private
     */
    var $_content_topic = null;

    /**
     * The articles to display
     *
     * @var Array
     * @access private
     */
    var $_articles = null;

    /**
     * The datamanager for the currently displayed article.
     *
     * @var midcom_helper_datamanger2_datamanager
     */
    var $_datamanager = null;

    /**
     * Simple default constructor.
     */
    function net_nehmer_blog_handler_index()
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
     * Simple helper which references all important members to the request data listing
     * for usage within the style listing.
     */
    function _prepare_request_data()
    {
        $this->_request_data['datamanager'] =& $this->_datamanager;
    }


    /**
     * Shows the autoindex list. Nothing to do in the handle phase except setting last modified
     * dates.
     */
    function _handler_index ($handler_id, $args, &$data)
    {
        $this->_datamanager = new midcom_helper_datamanager2_datamanager($this->_request_data['schemadb']);
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_content_topic->id);
        $qb->add_constraint('up', '=', 0);

        // Set default page title
        $this->_request_data['page_title'] = $this->_topic->extra;

        // Filter by categories
        if (   $handler_id == 'index-category'
            || $handler_id == 'latest-category')
        {
            $data['category'] = $args[0];
            if (!in_array($data['category'], $this->_request_data['categories']))
            {
                // This is not a predefined category from configuration, check if site maintainer allows us to show it
                if (!$this->_config->get('categories_custom_enable'))
                {
                    return false;
                }
                // TODO: Check here if there are actually items in this cat
            }

            // TODO: check schema storage to get fieldname
            $multiple_categories = true;
            if (   isset($this->_request_data['schemadb']['default']->fields['categories'])
                && array_key_exists('allow_multiple', $this->_request_data['schemadb']['default']->fields['categories']['type_config'])
                && !$this->_request_data['schemadb']['default']->fields['categories']['type_config']['allow_multiple'])
            {
                $multiple_categories = false;
            }
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("multiple_categories={$multiple_categories}");
            debug_pop();
            if ($multiple_categories)
            {
                $qb->add_constraint('extra1', 'LIKE', "%|{$this->_request_data['category']}|%");
            }
            else
            {
                $qb->add_constraint('extra1', '=', (string)$this->_request_data['category']);
            }

            // Add category to title
            $this->_request_data['page_title'] = sprintf($this->_request_data['l10n']->get('%s category %s'), $this->_topic->extra, $data['category']);
        }

        $qb->add_order('metadata.published', 'DESC');
        
        switch ($handler_id)
        {
            case 'latest':
                $qb->set_limit((int) $args[0]);
                break;
            
            case 'latest-category':
                $qb->set_limit((int) $args[1]);
                break;
                
            case 'index':
            case 'index-category':
            default:
                $qb->set_limit($this->_config->get('index_entries'));
                break;
        }
        
        $this->_articles = $qb->execute_unchecked();

        $this->_prepare_request_data();
        $_MIDCOM->set_26_request_metadata(net_nehmer_blog_viewer::get_last_modified($this->_topic, $this->_content_topic), $this->_topic->guid);
        return true;
    }

    /**
     * Displays the index page
     */
    function _show_index($handler_id, &$data)
    {
        midcom_show_style('index-start');

        if ($this->_config->get('comments_enable'))
        {
            $_MIDCOM->componentloader->load('net.nehmer.comments');
            $this->_request_data['comments_enable'] = true;
        }

        if ($this->_articles)
        {
            $total_count = count($this->_articles);
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);
            foreach ($this->_articles as $article_counter => $article)
            {
                if (! $this->_datamanager->autoset_storage($article))
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("The datamanager for article {$article->id} could not be initialized, skipping it.");
                    debug_print_r('Object was:', $article);
                    debug_pop();
                    continue;
                }

                $data['article'] =& $article;
                $data['article_counter'] = $article_counter;
                $data['article_count'] = $total_count;
                $arg = $article->name ? $article->name : $article->guid;
                
                if (   $this->_config->get('link_to_external_url')
                    && !empty($article->url))
                {
                    $data['view_url'] = $article->url;
                }
                else
                {
                    if ($this->_config->get('view_in_url'))
                    {
                        $data['view_url'] = "{$prefix}view/{$arg}.html";
                    }
                    else
                    {
                        $data['view_url'] = "{$prefix}{$arg}.html";
                    }
                }

                midcom_show_style('index-item');
            }
        }
        else
        {
            midcom_show_style('index-empty');
        }

        midcom_show_style('index-end');
        return true;
    }
}
?>
