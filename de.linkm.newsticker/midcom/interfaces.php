<?php

/**
 * @package de.linkm.newsticker
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Newsticker MidCOM interface class.
 *
 * @package de.linkm.newsticker
 */

class de_linkm_newsticker_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function de_linkm_newsticker_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'de.linkm.newsticker';
        $this->_autoload_files = Array('viewer.php', 'admin.php', 'navigation.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }

    /**
     * Iterate over all articles and create index record using the datamanger indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        debug_push_class(__CLASS__, __FUNCTION__);

        $nav = new midcom_helper_nav();
        $node = $nav->get_node($topic->id);

        if (is_null($config->get('symlink_topic')))
        {
            $qb = midcom_db_article::new_query_builder();
            $qb->add_constraint('topic', '=', $topic->id);
            $articles = $qb->execute();
            if ($articles)
            {
                foreach ($articles as $article)
                {
                    $datamanager = new midcom_helper_datamanager($config->get('schemadb'));
                    if (! $datamanager)
                    {
                        debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $this->_config->get('schemadb'),
                            MIDCOM_LOG_WARN);
                        continue;
                    }

                    if (! $datamanager->init($article))
                    {
                        debug_add("Warning, failed to initialize datamanager for Article {$article->id}. See Debug Log for details.", MIDCOM_LOG_WARN);
                        debug_print_r('Article dump:', $article);
                        continue;
                    }

                    $document = $indexer->new_document($datamanager);
                    $document->component = 'de.linkm.newsticker';
                    $document->topic_guid = $topic->guid;
                    $document->topic_url = $node[MIDCOM_NAV_FULLURL];
                    $indexer->index($document);

                    $datamanager->destroy();
                }
            }
        }
        else
        {
            debug_add("The topic {$topic->id} is symlinked to another topic, skipping indexing.");
        }

        debug_pop();
        return true;
    }

    /**
     * Simple lookup method which tries to map the guid to an article of out topic.
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {
        $article = new midcom_db_article($guid);
        if (   ! $article
            || $article->topic != $topic->id)
        {
            return null;
        }
        return "{$article->name}.html";
    }

}

?>