<?php
/**
 * @package org.maemo.socialnews
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: fetchplazes.php 6094 2007-06-01 15:50:49Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for archiving current social news front page
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_cron_archive extends midcom_baseclasses_components_cron_handler
{
    function _on_initialize()
    {
        return true;
    }

    /**
     * List articles and recalculate
     */
    function _on_execute()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_add('_on_execute called');
        
        $nap = new midcom_helper_nav();
        $node = false;
        $socialnews_topic = $GLOBALS['midcom_component_data']['org.maemo.socialnews']['config']->get('archive_generation_topic');
        if ($socialnews_topic)
        {
            $topic = new midcom_db_topic($socialnews_topic);
            $node = $nap->get_node($topic->id);
        }
        else
        {
            $node = midcom_helper_find_node_by_component('org.maemo.socialnews');
        }
        
        if (!$node)
        {
            return;
        }        
        
        // Get social news output
        ob_start();
        $_MIDCOM->dynamic_load($node[MIDCOM_NAV_RELATIVEURL]);
        $contents = ob_get_contents();
        ob_end_clean();
        
        if (!empty($contents))
        {   
            // Find out issue number
            $qb = midcom_db_article::new_query_builder();
            $qb->add_constraint('topic', '=', $node[MIDCOM_NAV_ID]);
            $issue_count = $qb->count_unchecked();
            
            // Create article
            $article = new midcom_db_article();
            $article->title = sprintf('%s %d', $node[MIDCOM_NAV_NAME], $issue_count + 1);
            $article->content = $contents;
            $article->topic = $node[MIDCOM_NAV_ID];
            
            if ($_MIDCOM->serviceloader->can_load('midcom_core_service_urlgenerator'))
            {
                $urlgenerator = $_MIDCOM->serviceloader->load('midcom_core_service_urlgenerator');
                $article->name = $urlgenerator->from_string($article->title);
            }
            else
            {
                $article->name = $article->title;
            }
            
            $article->create();
        }

        debug_add('Done');
        debug_pop();
        return;
    }
}
?>