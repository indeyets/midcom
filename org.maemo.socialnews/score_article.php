<?php
/**
 * @package org.maemo.socialnews
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_score_article_dba extends __org_maemo_socialnews_score_article_dba
{
    function __construct($id = null)
    {
        $this->_use_rcs = false;
        parent::__construct($id);
    }

    function get_label()
    {
        if (!$this->article)
        {
            return $this->guid;
        }
        $mc = midcom_db_article::new_collector('id', $this->article);
        $mc->add_value_property('title');
        $mc->execute();
        $articles = $mc->list_keys();
        if (!$articles)
        {
            return $this->guid;
        }
        foreach ($articles as $article_guid => $value)
        {
            return sprintf($_MIDCOM->i18n->get_string('score for %s', 'org.maemo.socialnews'), $mc->get_subkey($article_guid, 'title'));
        }
    }

    /**
     * Static method for storing score of an article
     */
    function store($article, $score)
    {
        $score = round($score);

        // Check if we have score object already
        $qb = org_maemo_socialnews_score_article_dba::new_query_builder();
        $qb->add_constraint('article', '=', $article->id);
        $caches = $qb->execute();
        if (count($caches) > 0)
        {
            $cache = $caches[0];
            $cache->score = $score;
            $stat = $cache->update();
            return $stat;
        }

        // Otherwise create new one
        $cache = new org_maemo_socialnews_score_article_dba();
        $cache->article = $article->id;
        $cache->score = $score;
        $stat = $cache->create();
        return $stat;
    }
}
?>
