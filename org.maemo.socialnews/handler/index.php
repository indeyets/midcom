<?php
/**
 * @package org.maemo.socialnews
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * This is a URL handler class for org.maemo.socialnews
 *
 * The midcom_baseclasses_components_handler class defines a bunch of helper vars
 * See: http://www.midgard-project.org/api-docs/midcom/dev/midcom.baseclasses/midcom_baseclasses_components_handler.html
 * 
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_handler_index  extends midcom_baseclasses_components_handler 
{
    private $articles = array();
    private $articles_scores = array();
    private $nodes = array();

    /**
     * Simple default constructor.
     */
    function org_maemo_socialnews_handler_index()
    {
        parent::midcom_baseclasses_components_handler();
    }
    
    private function get_node($node_id)
    {
        static $nap = null;
        if (is_null($nap))
        {
            $nap = new midcom_helper_nav();
        }
        
        if (!isset($this->nodes[$node_id]))
        {
            $this->nodes[$node_id] = $nap->get_node($node_id);
        }
        
        return $this->nodes[$node_id];
    }
    
    private function get_initial_score($id)
    {
        $score = 0;
        $sc = org_maemo_socialnews_score_article_dba::new_collector('article', $id);
        $sc->add_value_property('score');
        $sc->execute();
        $score_caches = $sc->list_keys();
        foreach ($score_caches as $guid => $cache)
        {
            $score = $sc->get_subkey($guid, 'score');
        }
        return $score;
    }
    
    private function count_age($score, $timestamp)
    {        
        $article_age = round((time() - $timestamp) / 3600);
        return $score - ($article_age * $this->_config->get('frontpage_score_hour_penalty'));
    }
    
    private function seek_articles($limit)
    {
        // Get list of all articles inside the hard time limit
        // FIXME: Use Midgard_Collector here once it supports metadata properties as value properties
        $articles_scores = array();
        $articles_by_guid = array();
        $qb = midcom_db_article::new_query_builder();
        $cutoff_date = gmdate('Y-m-d H:i:s', mktime(0, 0, 0, date('m'), date('d') - $this->_config->get('frontpage_limit_days'), date('Y')));
        $qb->add_constraint('metadata.published', '>', $cutoff_date);
        $qb->add_constraint('topic.component', '=', 'net.nehmer.blog');
        $articles = $qb->execute();
        foreach ($articles as $article)
        {
            $initial_score = $this->get_initial_score($article->id);
            if ($initial_score < $this->_config->get('frontpage_score_start'))
            {
                continue;
            }
            $articles_by_guid[$article->guid] = $article;
            $this->articles_scores[$article->guid] = $this->count_age($initial_score, $article->metadata->published);
        }
        
        arsort($this->articles_scores);

        $found = 0;
        foreach ($this->articles_scores as $guid => $score)
        {
            if ($found >= $limit)
            {
                break;
            }
            
            $this->articles[$guid] = $articles_by_guid[$guid];
            $found++;
        }
        
    }
    
    private function generate_caption($data, $getCnt)
    {
        if (strlen($data) == 0)
        { 
            return false;
        }

        $data = preg_replace('/<\/?(p|br)([^>]*)>/', ' ', $data);
        $data = strip_tags($data,'<a>');

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
        $limit = (int) $this->_config->get('frontpage_show_main_items') + $this->_config->get('frontpage_show_secondary_items');
        
        $this->seek_articles($limit);
        
        $revised = $this->_topic->metadata->revised;
        
        // Normalize articles
        foreach ($this->articles as $guid => $article)
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
            
            $this->articles[$guid] = $article;
            
            if ($article->metadata->revised > $revised)
            {
                $revised = $article->metadata->revised;
            }
        }
        
        $_MIDCOM->set_26_request_metadata($revised, $this->_topic->guid);
        
        $_MIDCOM->add_link_head(
            array
            (
                'rel' => 'stylesheet',
                'type' => 'text/css',
                'href' => MIDCOM_STATIC_URL . "/org.maemo.socialnews/social.css",
            )
        );
        
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');

        return true;
    }
    
    /**
     * This function does the output.
     *  
     */
    function _show_index($handler_id, &$data)
    {
        $data['node_title'] = $this->_config->get('socialnews_title');
        if (empty($data['node_title']))
        {
            $data['node_title'] = $this->_topic->extra;
        }    

        midcom_show_style('index_header');
        
        $main_items = array_slice($this->articles, 0, (int) $this->_config->get('frontpage_show_main_items'));
        $secondary_items = array_slice($this->articles, (int) $this->_config->get('frontpage_show_main_items'));
        
        midcom_show_style('index_main_header');
        foreach ($main_items as $article)
        {
            // TODO: Datamanager
            $data['article'] = $article;
            $data['node'] = $this->get_node($article->topic);
            midcom_show_style('index_main_item');
        }
        midcom_show_style('index_main_footer');
        
        if ($handler_id != 'main')
        {
            midcom_show_style('index_secondary_header');
            foreach ($secondary_items as $article)
            {
                // TODO: Datamanager
                $data['article'] = $article;
                $data['node'] = $this->get_node($article->topic);
                midcom_show_style('index_secondary_item');
            }
            midcom_show_style('index_secondary_footer');
        }
        midcom_show_style('index_footer');
    }
    
}
?>
