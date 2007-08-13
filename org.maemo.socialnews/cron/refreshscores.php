<?php
/**
 * @package org.maemo.socialnews
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: fetchplazes.php 6094 2007-06-01 15:50:49Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Cron handler for recalculating scores of recent items
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_cron_refreshscores extends midcom_baseclasses_components_cron_handler
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

        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic.component', '=', 'net.nehmer.blog');
        $qb->add_constraint('metadata.published', '>', date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 3, date('y'))));
        $articles = $qb->execute();
        foreach ($articles as $article)
        {
            $calculator->calculate_article($article, $cache);
        }

        debug_add('Done');
        debug_pop();
        return;
    }
}
?>