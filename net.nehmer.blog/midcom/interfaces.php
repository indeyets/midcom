<?php
/**
 * @package net.nehmer.blog
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Blog MidCOM interface class.
 *
 * Compatibility Notes:
 *
 * This component is a complete refactoring of de.linkm.newsticker. It specifically drops
 * a good number of legacies in the old component and thus does not guranntee 100%
 * data compatibility. Specifically:
 *
 * 1. Datamanager2 is used
 * 2. Aegir Symlink Article tool
 *
 * @package net.nehmer.blog
 */
class net_nehmer_blog_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nehmer_blog_interface()
    {
        parent::midcom_baseclasses_components_interface();

        //define('NET_NEHMER_BLOG_LEAFID_ARCHIVE', 1);
        define('NET_NEHMER_BLOG_LEAFID_FEEDS', 2);

        $this->_component = 'net.nehmer.blog';
        $this->_autoload_files = array
        (
            'viewer.php',
            'navigation.php'
        );
        
        $this->_autoload_libraries = array
        (
            'midcom.helper.datamanager2'
        );
        
        if ($GLOBALS['midcom_config']['positioning_enable'])
        {
            $this->_autoload_libraries[] = 'org.routamc.positioning';
        }
    }

    /**
     * Iterate over all articles and create index record using the datamanager indexer
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
                $_MIDCOM->generate_error('Failed to open symlink content topic.');
                // This will exit.
            }

            if ($topic->component != 'net.nehmer.blog')
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
        $arg = $article->name ? $article->name : $article->guid;
        
        if ($config->get('view_in_url'))
        {
            return "view/{$arg}.html";
        }
        else
        {
            return "{$arg}.html";
        }
    }
}
?>