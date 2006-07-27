<?php

/**
 * @package de.linkm.events
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Events MidCOM interface class.
 * 
 * @package de.linkm.events
 */
class de_linkm_events_interface extends midcom_baseclasses_components_interface
{
    /**
     * Constructor.
     * 
     * Nothing fancy, loads all script files and the datamanager library.
     */
    function de_linkm_events_interface()
    {
        parent::midcom_baseclasses_components_interface();
        
        $this->_component = 'de.linkm.events';
        $this->_autoload_files = Array('viewer.php', 'admin.php', 'navigation.php', 'helpers.php');
        $this->_autoload_libraries = Array('midcom.helper.datamanager');
    }
    
    /**
     * Iterate over all articles and create index record using the datamanger indexer
     * method.
     */
    function _on_reindex($topic, $config, &$indexer)
    {
        debug_push_class($this, '_on_reindex');
        $articles = mgd_list_topic_articles($topic->id);
        if ($articles)
        {
            while ($articles->fetch())
            {
                $article = mgd_get_article($articles->id);
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

        debug_pop();
        return true;
    }
}

?>