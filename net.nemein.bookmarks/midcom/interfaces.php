<?php
/**
 * @package net.nemein.bookmarks
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Bookmarks MidCOM interface class.
 *
 * @package net.nemein.bookmarks
 */
class net_nemein_bookmarks_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_bookmarks_interface()
    {
        parent::__construct();

        $this->_component = 'net.nemein.bookmarks';
        $this->_autoload_files = Array('viewer.php', 'navigation.php', 'helpers.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }

    /**
     * Iterate over all events and create index record using the custom indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        if ($articles = mgd_list_topic_articles($topic->id))
        {
            while ($articles->fetch ())
            {
                $article = mgd_get_article($articles->id);
                if ($article)
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
        return true;
    }
}
?>