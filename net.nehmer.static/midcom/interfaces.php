<?php

/**
 * @package net.nehmer.static
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * n.n.static MidCOM interface class.
 *
 * Compatibility Notes:
 *
 * This component is a complete refactoring of de.linkm.taviewer. It specifically drops
 * a good number of legacies in the old component and thus does not guarantee 100%
 * data compatibility. Specifically:
 *
 * 1. Datamanager2 is used
 * 2. Aegir Symlink Article tool
 *
 * @package net.nehmer.static
 */
class net_nehmer_static_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function __construct()
    {
        parent::__construct();

        $this->_component = 'net.nehmer.static';
        $this->_autoload_libraries = Array
        (
            'midcom.helper.datamanager2',
        );
    }

    /**
     * Iterate over all articles and create index record using the datamanager indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        if (is_null($config->get('symlink_topic')))
        {
            $qb = $_MIDCOM->dbfactory->new_query_builder('midcom_baseclasses_database_article');
            $qb->add_constraint('topic', '=', $topic->id);
            $result = $_MIDCOM->dbfactory->exec_query_builder($qb);

            if ($result)
            {
                $schemadb = midcom_helper_datamanager2_schema::load_database($config->get('schemadb'));
                $datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
                if (! $datamanager)
                {
                    debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $config->get('schemadb'),
                        MIDCOM_LOG_WARN);
                    continue;
                }

                foreach ($result as $article)
                {
                    if (! $datamanager->autoset_storage($article))
                    {
                        debug_add("Warning, failed to initialize datamanager for Article {$article->id}. Skipping it.", MIDCOM_LOG_WARN);
                        continue;
                    }

                    net_nehmer_static_viewer::index($datamanager, $indexer, $topic);
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
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, 'Failed to open symlink content topic.');
                // This will exit.
            }

            if ($topic->component != 'net.nehmer.static')
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
        if (   $article->name == 'index'
            && ! $config->get('autoindex'))
        {
            return '';
        }

        return "{$article->name}.html";
    }


}
?>