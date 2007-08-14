<?php
class org_maemo_socialnews_score_article_dba extends __org_maemo_socialnews_score_article_dba
{
    function org_maemo_socialnews_score_article_dba($id = null)
    {
        parent::__org_maemo_socialnews_score_article_dba($id);
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
            $stat = true;
            if ($score > $cache->score)
            {
                $cache->score = $score;
                $stat = $cache->update();
            }
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
