<?php

/**
 * @package de.linkm.taviewer
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * TAViewer MidCOM interface class.
 *
 * @package de.linkm.taviewer
 */
class de_linkm_taviewer_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function de_linkm_taviewer_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'de.linkm.taviewer';
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
        if (is_null($config->get('symlink_topic')))
        {
            $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_article');
            $qb->add_constraint('topic', '=', $topic->id);
            $result = $_MIDCOM->dbfactory->exec_query_builder($qb);

            if ($result)
            {
                foreach ($result as $article)
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

                    $indexer->index($datamanager);
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
        $topic_guid = $config->get('symlink_topic');
        if ($topic_guid !== null)
        {
            $topic = new midcom_db_topic($topic_guid);
            // Validate topic.

            if (! $topic)
            {
                debug_add('Failed to open symlink content topic, (might also be an invalid object) last Midgard Error: '
                    . mgd_errstr(), MIDCOM_LOG_ERROR);
                $_MIDCOM->generate_error('Failed to open symlink content topic.');
                // This will exit.
            }

            if ($topic->get_parameter('midcom', 'component') != 'de.linkm.taviewer')
            {
                debug_print_r('Retrieved topic was:', $topic);
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT,
                    'Symlink content topic is invalid, see the debug level log for details.');
                // This will exit.
            }
        }

        $article = new midcom_baseclasses_database_article($guid);
        if (   ! $article
            || $article->topic != $topic->id)
        {
            return null;
        }
        if ($article->name == 'index')
        {
            return '';
        }

        return "{$article->name}.html";
    }


}
?>
