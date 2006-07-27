<?php

/**
 * @package net.nemein.discussion
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Forum MidCOM interface class.
 *
 * Defines the privilege net.nemein.discussion::moderation to superseed the original
 * moderator group.
 *
 * @package net.nemein.discussion
 */
class net_nemein_discussion_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_discussion_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'net.nemein.discussion';
        $this->_autoload_files = Array
        (
            'viewer.php',
            'admin.php',
            'navigation.php',
            'thread.php',
            'post.php',
        );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            'de.bitfolge.feedcreator',
            'org.openpsa.qbpager',
            'net.nehmer.markdown',
        );
        $this->_acl_privileges['moderation'] = MIDCOM_PRIVILEGE_DENY;

        // New messages enter at 4, and can be lowered or raised
        define('NET_NEMEIN_DISCUSSION_JUNK', 1);
        define('NET_NEMEIN_DISCUSSION_ABUSE', 2);
        define('NET_NEMEIN_DISCUSSION_REPORTED_ABUSE', 3);
        define('NET_NEMEIN_DISCUSSION_NEW', 4);
        define('NET_NEMEIN_DISCUSSION_MODERATED', 5);
    }

    /**
     * Iterate over all articles and create index record using the datamanger indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        return;

        debug_push_class(__CLASS__, __FUNCTION__);

        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $topic->id);
        $qb->add_constraint('up', '=', 0);
        $articles = $qb->execute();
        if (! $articles)
        {
            return true;
        }

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

            $document = net_nemein_discussion_thread2document ($datamanager, &$indexer);
            $indexer->index($document);

            $datamanager->destroy();
        }

        debug_pop();
        return true;
    }

    /**
     * Simple lookup method which tries to map the guid to an article of out topic. Reply-Articles
     * are filtered out of the list, as they are not (yet) permalinked.
     */
    function _on_resolve_permalink($topic, $config, $guid)
    {
        $article = new midcom_db_article($guid);
        if (   ! $article
            || $article->topic != $topic->id
            || $article->up != 0)
        {
            return null;
        }
        return "{$article->name}.html";
    }

}

?>