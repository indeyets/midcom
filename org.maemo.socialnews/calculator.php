<?php
/**
 * @package org.maemo.socialnews
 * @author Henri Bergius, http://bergie.iki.fi 
 * @version $Id: main.php,v 1.26 2006/07/21 08:40:58 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * Social News score calculator
 *
 * This system uses various data sources to determine an overall "value" of a particular news item in the system. This can for example be
 * used to calculate which news items of a particular day are most relevant.
 * 
 * @package org.maemo.socialnews
 */
class org_maemo_socialnews_calculator extends midcom_baseclasses_components_purecode
{
    private $http_request = null;

    public function __construct()
    {
        $this->_component = 'org.maemo.socialnews';
        parent::__construct();
        
        $_MIDCOM->load_library('org.openpsa.httplib');
        $this->http_request = new org_openpsa_httplib();
    }
    
    private function count_comments($guid)
    {
        $_MIDCOM->componentloader->load_graceful('net.nehmer.comments');
        
        return net_nehmer_comments_comment::count_by_objectguid($guid);
    }
    
    private function count_favourites($guid)
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
        
        return net_nemein_favourites_favourite_dba::count_by_objectguid($guid);
    }
    
    private function count_buries($guid)
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
        
        return net_nemein_favourites_favourite_dba::count_buries_by_objectguid($guid);
    }
    
    private function count_delicious($url)
    {
        $json = $this->http_request->get('http://badges.del.icio.us/feeds/json/url/data?hash=' . md5($url));
        if (empty($json))
        {
            return 0;
        }
        
        $item_data = json_decode($json);
        
        if (!isset($item_data[0]->total_posts))
        {
            return 0;
        }
        
        return $item_data[0]->total_posts;
    }
    
    private function count_digg($url)
    {
        $diggurl = "http://services.digg.com/stories?link=" . urlencode($url) . "&type=json&appkey=" . urlencode($this->_config->get('digg_apikey'));
        $json = $this->http_request->get($diggurl);
        if (empty($json))
        {
            return 0;
        }
        
        $item_data = json_decode($json);
        
        if (   !isset($item_data->stories)
            || !is_array($item_data->stories))
        {
            return 0;
        }
        
        $score = 0;
        
        foreach ($item_data->stories as $story)
        {
            $score += $story->diggs;
            $score += $story->comments * $this->_config->get('digg_modifier_comments');
        }
        
        return $score;
    }
    
    private function count_technorati($url)
    {
        $techurl = "http://api.technorati.com/cosmos?url=" . urlencode($url) . "&key=" . urlencode($this->_config->get('technorati_apikey'));
        $xml = $this->http_request->get($techurl);
        if (empty($xml))
        {
            return 0;
        }
        
        $simplexml = simplexml_load_string($xml);
        
        $score = 0;
        
        if (isset($simplexml->document->result->inboundlinks))
        {
            $score = $simplexml->document->result->inboundlinks;
        }
        
        /*
        if (   isset($simplexml->document->item)
            && is_array($simplexml->document->item))
        {
            foreach ($simplexml->document->item as $item)
            {
                // TODO: Here we could save links of blogs that link to this item if we wanted
            }
        }
        */
        
        return $score;
    }
    
    private function calculate_object($guid, $url)
    {
        // Here we apply the special sauce
        $scores = array
        (
            'total' => 0,
        );
        
        if ($this->_config->get('comments_enable'))
        {
            $scores['comments'] = $this->count_comments($guid) * $this->_config->get('comments_modifier');
            $scores['total'] += $scores['comments'];
        }
        
        if ($this->_config->get('favourites_enable'))
        {
            $scores['favourites'] = $this->count_favourites($guid) * $this->_config->get('favourites_modifier');
            $scores['total'] += $scores['favourites'];
        }
        
        if ($this->_config->get('buries_enable'))
        {
            $scores['buries'] = $this->count_buries($guid) * $this->_config->get('buries_modifier');
            $scores['total'] += $scores['buries'];
        }
        
        if ($this->_config->get('delicious_enable'))
        {
            if (!empty($url))
            {
                $scores['delicious'] = $this->count_delicious($url) * $this->_config->get('delicious_modifier');
                $scores['total'] += $scores['delicious'];
            }
        }
        
        if ($this->_config->get('digg_enable'))
        {
            if (!empty($url))
            {
                $scores['digg'] = $this->count_digg($url) * $this->_config->get('digg_modifier');
                $scores['total'] += $scores['digg'];
            }
        }
        
        if ($this->_config->get('technorati_enable'))
        {
            if (!empty($url))
            {
                $scores['technorati'] = $this->count_technorati($url) * $this->_config->get('technorati_modifier');
                $scores['total'] += $scores['technorati'];
            }
        }
        
        return $scores;
    }
    
    public function calculate_article($article, $cache = false)
    {
        if (empty($article->url))
        {
            // Local news item
            $nap = new midcom_helper_nav();
            $node = $nap->get_node($article->topic);
            if (   $node
                && $node[MIDCOM_NAV_COMPONENT] == 'net.nehmer.blog')
            {
                if ($node[MIDCOM_NAV_CONFIGURATION]->get('view_in_url'))
                {
                    $article->url = "{$node[MIDCOM_NAV_FULLURL]}view/{$article->name}/";
                }
                else
                {
                    $article->url = "{$node[MIDCOM_NAV_FULLURL]}{$article->name}/";
                }
            }
        }
    
        $article_scores = $this->calculate_object($article->guid, $article->url);

        // TODO: Count in article's (and feed's) local modifiers
        
        if ($cache)
        {
            org_maemo_socialnews_score_article_dba::store($article, $article_scores['total']);
        }
        
        return $article_scores;
    }
}
?>