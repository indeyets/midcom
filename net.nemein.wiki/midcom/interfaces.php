<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wiki MidCOM interface class.
 *
 * @package net.nemein.wiki
 */
class net_nemein_wiki_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     *
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function net_nemein_wiki_interface()
    {
        parent::midcom_baseclasses_components_interface();

        $this->_component = 'net.nemein.wiki';
        $this->_autoload_files = Array(
            'wikipage.php',
            'viewer.php',
            'admin.php',
            'navigation.php',
            'notes.php',
        );
        $this->_autoload_libraries = Array(
            'midcom.helper.datamanager2',
            'de.bitfolge.feedcreator',
            'net.nehmer.markdown',
            'no.bergfald.rcs',
            'org.openpsa.contactwidget',
            'org.openpsa.relatedto',
        );
    }

    /**
     * Iterate over all articles and create index record using the datamanger indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $qb = midcom_baseclasses_database_article::new_query_builder();
        $qb->add_constraint('topic', '=', $topic->id);
        $qb->add_order('title', 'ASC');
        $result = $qb->execute();

        foreach ($result as $wikipage)
        {
            $datamanager = new midcom_helper_datamanager($config->get('schemadb'));
            if (! $datamanager)
            {
                debug_add('Warning, failed to create a datamanager instance with this schemapath:' . $this->_config->get('schemadb'),
                    MIDCOM_LOG_WARN);
                continue;
            }

            if (! $datamanager->init($wikipage))
            {
                debug_add("Warning, failed to initialize datamanager for Article {$article->id}. See Debug Log for details.", MIDCOM_LOG_WARN);
                debug_print_r('Article dump:', $article);
                continue;
            }

            $indexer->index($datamanager);
            $datamanager->destroy();
        }

        debug_pop();
        return true;
    }

    function _on_resolve_permalink($topic, $config, $guid)
    {
        $article = new midcom_db_article($guid);
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
    
    /**
     * Check whether given wikiword is free in given node
     *
     * Returns true if word is free, false if reserved
     */
    function node_wikiword_is_free(&$node, $wikiword)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        if (empty($node))
        {
            //Invalid node
            debug_add('given node is not valid', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        $wikiword_name = midcom_generate_urlname_from_string($wikiword);
        $qb = new MidgardQueryBuilder('midgard_article');
        $qb->add_constraint('topic', '=', $node[MIDCOM_NAV_OBJECT]->id);
        $qb->add_constraint('name', '=', $wikiword_name);
        $ret = @$qb->execute();
        if (   is_array($ret)
            && count($ret) > 0)
        {
            //Match found, word is reserved
            debug_add("QB found matches for name '{$wikiword_name}' in topic #{$node[MIDCOM_NAV_OBJECT]->id}, given word '{$wikiword}' is reserved", MIDCOM_LOG_INFO);
            //sprint_r is not part of MidCOM helpers
            ob_start();
            print_r($ret);
            $ret_r = ob_get_contents();
            ob_end_clean();
            debug_add("QB results:\n===\n{$ret_r}===\n");
            debug_pop();
            return false;
        }
        debug_pop();
        return true;
    }
}

?>
