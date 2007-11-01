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
        $qb->add_constraint('content', '<>', '');
        return $qb->count_unchecked();
    }
    
    private function count_favourites($guid)
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $guid);
        $qb->add_constraint('bury', '=', false);
        return $qb->count_unchecked();
    }
    
    private function count_buries($guid)
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $guid);
        $qb->add_constraint('bury', '=', true);
        return $qb->count_unchecked();
    }

    private function count_wikicreates($guid)
    {
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $guid);
        $qb->add_constraint('topic.component', '=', 'net.nemein.wiki');
        return $qb->count_unchecked();
    }

    private function count_wikiedits($guid)
    {
        $edits = 0;
        $rcs =& $_MIDCOM->get_service('rcs');    
        $qb = midcom_db_article::new_query_builder();
        // TODO: Add this when wiki inserts all contributors to authors array
        // $qb->add_constraint('metadata.authors', 'LIKE', "%|{$guid}|%");
        $qb->add_constraint('topic.component', '=', 'net.nemein.wiki');
        $pages = $qb->execute_unchecked();
        foreach ($pages as $page)
        {
            $object_rcs = $rcs->load_handler($page);
            $history = $object_rcs->list_history();
            foreach ($history as $rev => $data) 
            {
                if ($data['user'] == "user:{$guid}")
                {
                    // TODO: At some point we may consider line counts here
                    $edits++;
                }
            }
        }
        
        return $edits;
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
                
        $blogs = $qb->execute_unchecked();

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

    private function count_products($guid)
    {
        $_MIDCOM->componentloader->load_graceful('org.openpsa.products');
    
        $qb = org_openpsa_products_product_dba::new_query_builder();
        $qb->add_constraint('metadata.authors', 'LIKE', "%|{$guid}|%");
        
        if (!$this->_config->get('karma_productratings_enable'))
        {
            // We're not valuating products, just return their number
            return $qb->count();
        }
               
        $product_karma = 0;
                
        $products = $qb->execute_unchecked();

        foreach ($products as $product)
        {   
            $product_karma = $product_karma + 1 + $product->price;
        }
        
        return round($product_karma);
    }
    
    private function count_discussion($id)
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.discussion');    
    
        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_constraint('sender', '=', $id);
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_NEW);
        $good_posts = $qb->count_unchecked();
        
        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_constraint('sender', '=', $id);
        $qb->add_constraint('status', '<', NET_NEMEIN_DISCUSSION_NEW);
        $bad_posts = $qb->count_unchecked();
        
        $karma = $good_posts + ($bad_posts * $this->_config->get('karma_discussion_badpost_modifier'));
        return $karma;
    }

    private function count_groups($id)
    {
        $qb = midcom_db_member::new_query_builder();
        $qb->add_constraint('uid', '=', $id);
        return $qb->count_unchecked();
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

        if ($this->_config->get('karma_wikiedits_enable'))
        {
            $karma['wikiedits'] = $this->count_wikiedits($object->guid) * $this->_config->get('karma_wikiedits_modifier');
            $karma['karma'] += $karma['wikiedits'];
        }
        
        if ($this->_config->get('karma_blogs_enable'))
        {
            $karma['blogs'] = $this->count_blogs($object->guid) * $this->_config->get('karma_blogs_modifier');
            $karma['karma'] += $karma['blogs'];
        }

        if ($this->_config->get('karma_products_enable'))
        {
            $karma['products'] = $this->count_products($object->guid) * $this->_config->get('karma_products_modifier');
            $karma['karma'] += $karma['products'];
        }
        
        if ($this->_config->get('karma_discussion_enable'))
        {
            $karma['discussion'] = $this->count_discussion($object->id) * $this->_config->get('karma_discussion_modifier');
            $karma['karma'] += $karma['discussion'];
        }
        
        if ($this->_config->get('karma_groups_enable'))
        {
            $karma['groups'] = $this->count_groups($object->id) * $this->_config->get('karma_groups_modifier');
            $karma['karma'] += $karma['groups'];
        }
       
        return $karma;
    }
    
    public function calculate_person($person, $cache = false)
    {
        $person_karma = $this->calculate($person);

        if ($cache)
        {
            foreach ($person_karma as $source => $karma)
            {
                if ($source == 'karma')
                {
                    // Total karma is cached to metadata score for easy retrieval
                    $person->metadata->score = $karma;
                    $person->update();
                    continue;
                }
                
                // Karma per source goes to params
                $person->parameter('net.nehmer.account:karma', $source, $karma);
            }
            
            $person->parameter('net.nehmer.account', 'karma_calculated', gmdate('Y-m-d H:i:s'));
        }
        
        return $person_karma;
    }
}
?>
