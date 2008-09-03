<?php
/**
 * @package net.nemein.discussion
 * @author Henri Bergius, http://bergie.iki.fi 
 * @version $Id: main.php,v 1.26 2006/07/21 08:40:58 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * Thread popularity calculator
 * 
 * @package net.nemein.discussion
 */
class net_nemein_discussion_calculator extends midcom_baseclasses_components_purecode
{
    public function __construct()
    {
        $this->_component = 'net.nemein.discussion';
        parent::__construct();
        
        //Disable limits
        // TODO: Could this be done more safely somehow
        @ini_set('memory_limit', -1);
        @ini_set('max_execution_time', 0);
    }
    
    private function count_favourites($guids_array)
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('objectGuid', 'IN', $guids_array);
        $qb->add_constraint('bury', '=', false);
        return $qb->count_unchecked();
    }
    
    private function count_buries($guids_array)
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
        
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('objectGuid', 'IN', $guids_array);
        $qb->add_constraint('bury', '=', true);
        return $qb->count_unchecked();
    }
    private function count_posts($thread_id)
    {
        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_constraint('thread', '=', $thread_id);
        $qb->add_constraint('status', '>=', NET_NEMEIN_DISCUSSION_NEW);
        $good_posts = $qb->count_unchecked();
        
        $qb = net_nemein_discussion_post_dba::new_query_builder();
        $qb->add_constraint('thread', '=', $thread_id);
        $qb->add_constraint('status', '<', NET_NEMEIN_DISCUSSION_NEW);
        $bad_posts = $qb->count_unchecked();
        
        $popularity = $good_posts + ($bad_posts * $this->_config->get('popularity_badpost_modifier'));
        return $popularity;
    }

    private function calculate($object)
    {
        // Here we apply the special sauce
        $popularity = array
        (
            'popularity' => 0,
        );
        
        $guids = array();
        $guids[] = $object->guid;
        $mc = net_nemein_discussion_post_dba::new_collector('thread', $object->id);
        $mc->execute();
        $post_guids = $mc->list_keys();
        foreach ($post_guids as $post_guid => $array)
        {
            $guids[] = $post_guid;
        }
        
        if ($this->_config->get('popularity_favourites_enable'))
        {
            $popularity['favourites'] = $this->count_favourites($guids) * $this->_config->get('popularity_favourites_modifier');
            $popularity['popularity'] += $popularity['favourites'];
        }
        
        if ($this->_config->get('popularity_buries_enable'))
        {
            $popularity['buries'] = $this->count_buries($guids) * $this->_config->get('popularity_buries_modifier');
            $popularity['popularity'] += $popularity['buries'];
        }
                
        if ($this->_config->get('popularity_posts_enable'))
        {
            $popularity['posts'] = $this->count_posts($object->id) * $this->_config->get('popularity_posts_modifier');
            $popularity['popularity'] += $popularity['posts'];
        }
       
        return $popularity;
    }
    
    public function calculate_thread($thread, $cache = false)
    {
        $thread_popularity = $this->calculate($thread);

        if ($cache)
        {
            foreach ($thread_popularity as $source => $popularity)
            {
                if ($source == 'popularity')
                {
                    // Total popularity is cached to metadata score for easy retrieval
                    $thread->metadata->score = $popularity;
                    $thread->update();
                    continue;
                }
                
                // Karma per source goes to params
                $thread->parameter('net.nemein.discussion:popularity', $source, $popularity);
            }
            
            $thread->parameter('net.nemein.discussion', 'popularity_calculated', gmdate('Y-m-d H:i:s'));
        }
        
        return $thread_popularity;
    }
}
?>