<?php
/**
 * @package org.maemo.socialnews
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is an URL handler class for org.maemo.socialnews
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_handler_index  extends midcom_baseclasses_components_handler 
{
    private $articles = array();

    /**
     * Simple default constructor.
     */
    function org_maemo_socialnews_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    private function query_articles($score, $limit)
    {
        if ($score < 0)
        {
            // We shouldn't recurse deeper than this
            return false;
        }
        
        $article_count = count($this->articles);
        if ($article_count >= $limit)
        {
            // Stop recursion when article count passes limit
            return false;
        }
        
        $qb = org_maemo_socialnews_score_article_dba::new_query_builder();
        $qb->add_order('score', 'DESC');
        $qb->add_constraint('score', '>=', $score);
        $qb->set_limit($limit - $article_count);
        
        $ids = array_keys($this->articles);
        foreach ($ids as $id)
        {
            $qb->add_constraint('article', '<>', $id);
        }
        
        $article_scores = $qb->execute();
        foreach ($article_scores as $article_score)
        {
            $article = new midcom_db_article($article_score->article);
            $this->articles[$article_score->article] = $article;
        }
        
        return true;
    }
    
    private function generate_caption($data, $getCnt)
    {
        if (strlen($data) == 0)
        { 
            return false;
        }

        $data = preg_replace('/<\/?(p|br)([^>]*)>/', ' ', $data);
        $data=strip_tags($data,'<a>');

        if (strlen($data) <= $getCnt)
        {
            return $data;
        }

        $ret='';
        $cnt=0;
        $inTag=FALSE;
        $chars=preg_split('//',$data);

        foreach($chars as $k => $char)
        {
            if ($char == '<')
            {
                $inTag = false;
            }
            if (   $char == '>' 
                && $inTag)
            {
                $inTag = false;
            }
            
            if (!$inTag)
            {
                $cnt++;
            }
            
            if (   !$inTag 
                && ($cnt >= $getCnt)
                && preg_match('/\s/', $char))
            {
                $ret .= '...';
                break;
            } 
            else 
            {
                $ret .= $char;
            }
        }
        return $ret;
    }
    
    /**
     * The handler for the index article. 
     * @param mixed $handler_id the array key from the requestarray
     * @param array $args the arguments given to the handler
     * 
     */
    function _handler_index($handler_id, $args, &$data)
    {
        // Find items matching our criteria
        $recurse = true;
        $score = (float) $this->_config->get('frontpage_score_start');
        $limit = (int) $this->_config->get('frontpage_show_main_items') + $this->_config->get('frontpage_show_secondary_items');
        while ($recurse)
        {
            $recurse = $this->query_articles($score, $limit);
            $score -= 10;
        }
        
        // Normalize articles
        foreach ($this->articles as $id => $article)
        {
            if (empty($article->abstract))
            {
                $article->abstract = $this->generate_caption($article->content, $this->_config->get('frontpage_show_abstract_length'));
            }
            else
            {
                $article->abstract = $this->generate_caption($article->abstract, $this->_config->get('frontpage_show_abstract_length'));
            }
            
            if (empty($article->url))
            {
                // Local item
                $article->url = $_MIDCOM->permalinks->create_permalink($article->guid);
            }
            
            $this->articles[$id] = $article;
        }
    
        return true;
    }
    
    /**
     * This function does the output.
     *  
     */
    function _show_index($handler_id, &$data)
    {
        $data['node_title'] = $this->_topic->extra;
        midcom_show_style('index_header');
        
        $main_items = array_slice($this->articles, 0, (int) $this->_config->get('frontpage_show_main_items'));
        $secondary_items = array_slice($this->articles, (int) $this->_config->get('frontpage_show_main_items'));
        
        midcom_show_style('index_main_header');
        foreach ($main_items as $article)
        {
            // TODO: Datamanager
            $data['article'] = $article;
            midcom_show_style('index_main_item');
        }
        midcom_show_style('index_main_footer');
        
        midcom_show_style('index_secondary_header');
        foreach ($secondary_items as $article)
        {
            // TODO: Datamanager
            $data['article'] = $article;
            midcom_show_style('index_secondary_item');
        }
        midcom_show_style('index_secondary_footer');
        
        if ($this->_config->get('frontpage_show_area_latest'))
        {
            $nap = new midcom_helper_nav();
            $qb = midcom_db_topic::new_query_builder();
            $qb->add_constraint('component', '=', 'net.nehmer.blog');
            $qb->add_order('extra');
            $topics = $qb->execute();
            $substyle = $this->_config->get('frontpage_show_area_substyle');
            foreach ($topics as $topic)
            {
                $data['topic'] = $topic;
                $data['node'] = $nap->get_node($topic->id);
                
                $dl_url = "{$data['node'][MIDCOM_NAV_RELATIVEURL]}latest/" . $this->_config->get('frontpage_show_area_latest_items');
                
                if (!empty($substyle))
                {
                    $dl_url = "midcom-substyle-" . $this->_config->get('frontpage_show_area_substyle') . "/{$dl_url}";
                }
                $_MIDCOM->dynamic_load($dl_url);
            }
        }
        
        midcom_show_style('index_footer');
    }
}
?>
