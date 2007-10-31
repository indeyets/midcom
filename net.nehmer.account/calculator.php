<?php
/**
 * @package net.nehmer.account
 * @author Henri Bergius, http://bergie.iki.fi 
 * @version $Id: main.php,v 1.26 2006/07/21 08:40:58 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * Karma calculator
 * 
 * @package net.nehmer.account
 */
class net_nehmer_account_calculator extends midcom_baseclasses_components_purecode
{
    private $http_request = null;

    public function __construct()
    {
        $this->_component = 'net.nehmer.account';
        parent::midcom_baseclasses_components_purecode();
    }
    
    private function count_comments($guid)
    {
        $_MIDCOM->componentloader->load_graceful('net.nehmer.comments');
        
        $qb = net_nehmer_comments_comment::new_query_builder();
        $qb->add_constraint('metadata.authors', 'LIKE', "%|{$guid}|%");
        return $qb->count();
    }
    
    private function count_favourites($guid)
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $guid);
        $qb->add_constraint('bury', '=', false);
        return $qb->count();
    }
    
    private function count_buries($guid)
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $guid);
        $qb->add_constraint('bury', '=', true);
        return $qb->count();
    }

    private function count_wikicreates($guid)
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $guid);
        $qb->add_constraint('topic.component', '=', 'net.nemein.wiki');
        return $qb->count();
    }

    private function count_wikipages($guid)
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('metadata.authors', 'LIKE', "%|{$guid}|%");
        $qb->add_constraint('topic.component', '=', 'net.nemein.wiki');
        return $qb->count();
    }

    private function count_blogs($guid)
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('metadata.authors', 'LIKE', "%|{$guid}|%");
        $qb->add_constraint('topic.component', '=', 'net.nehmer.blog');
        
        if (!$this->_config->get('karma_socialnews_enable'))
        {
            // We're not valuating blog posts, just return their number
            return $qb->count();
        }
        
        $_MIDCOM->componentloader->load_graceful('org.maemo.socialnews');        
        $blog_karma = 0;
                
        $blogs = $qb->execute();

        foreach ($blogs as $blog)
        {
            $mc = org_maemo_socialnews_score_article_dba::new_collector('article', $blog->id);
            $mc->add_value_property('score');
            $mc->execute();
            $scores = $mc->list_keys();
            if (!$scores)
            {
                // Unscored article, just count as 1
                $blog_karma++;
                continue;
            }
            $blog_score = 0;
            foreach ($scores as $score_guid => $value)
            {
                $blog_score = $mc->get_subkey($score_guid, 'score');
            }
            
            $blog_karma = $blog_karma + 1 + $blog_score;
        }
        
        return round($blog_karma);
    }

    private function calculate($object)
    {
        // Here we apply the special sauce
        $karma = array
        (
            'karma' => 0,
        );
        
        if ($this->_config->get('karma_comments_enable'))
        {
            $karma['comments'] = $this->count_comments($object->guid) * $this->_config->get('karma_comments_modifier');
            $karma['karma'] += $karma['comments'];
        }
        
        if ($this->_config->get('karma_favourites_enable'))
        {
            $karma['favourites'] = $this->count_favourites($object->guid) * $this->_config->get('karma_favourites_modifier');
            $karma['karma'] += $karma['favourites'];
        }
        
        if ($this->_config->get('karma_buries_enable'))
        {
            $karma['buries'] = $this->count_buries($object->guid) * $this->_config->get('karma_buries_modifier');
            $karma['karma'] += $karma['buries'];
        }
        
        if ($this->_config->get('karma_wikicreates_enable'))
        {
            $karma['wikicreates'] = $this->count_wikicreates($object->guid) * $this->_config->get('karma_wikicreates_modifier');
            $karma['karma'] += $karma['wikicreates'];
        }

        if ($this->_config->get('karma_wikipages_enable'))
        {
            $karma['wikipages'] = $this->count_wikipages($object->guid) * $this->_config->get('karma_wikipages_modifier');
            $karma['karma'] += $karma['wikipages'];
        }
        
        if ($this->_config->get('karma_blogs_enable'))
        {
            $karma['blogs'] = $this->count_blogs($object->guid) * $this->_config->get('karma_blogs_modifier');
            $karma['karma'] += $karma['blogs'];
        }
        
        return $karma;
    }
    
    public function calculate_person($person, $cache = false)
    {
        $person_karma = $this->calculate($person);

        if ($cache)
        {
            //net_nehmer_account_karma_person_dba::store($person, $person_karma['karma']);
        }
        
        return $person_karma;
    }
}
?>
