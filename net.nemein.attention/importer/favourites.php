<?php
/**
 * @package net.nemein.attention
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Importer for Favouriting data
 *
 * @package net.nemein.attention
 */
class net_nemein_attention_importer_favourites extends net_nemein_attention_importer
{
    var $feed_categories = array();

    /**
     * Initializes the class. The real startup is done by the initialize() call.
     */
    function net_nemein_attention_importer_favourites()
    {
        $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
        $_MIDCOM->load_library('net.nemein.tag');
        $_MIDCOM->load_library('net.nemein.rss');
        parent::net_nemein_attention_importer();
        
        $this->get_feed_categories();
    }
    
    function seek_favourites_users()
    {
        $qb = midcom_db_person::new_query_builder();
        $qb->add_constraint('username', '<>', '');
        $persons = $qb->execute();
        foreach ($persons as $person)
        {
            $this->import($person);
        }
    }
    
    function get_feed_categories()
    {
        $feed_qb = net_nemein_rss_feed_dba::new_query_builder();
        $feeds = $feed_qb->execute();
        foreach ($feeds as $feed)
        {
            $this->feed_categories['feed:' . md5($feed->url)] = $feed->url;
        }
    }
    
    // TODO: Move to a more generic place, useful also for calculators
    function read_tags($object_guid, $nodes, $item_concepts, $value = 1)
    {
        $tags = net_nemein_tag_handler::get_tags_by_guid($object_guid);
        
        foreach ($tags as $tag => $url)
        {
            if (isset($item_concepts[$tag]))
            {
                // We counted this already, skip
                continue;
            }
            $item_concepts[$tag] = true;
            
            if (!isset($nodes['concepts'][$tag]))
            {
                $nodes['concepts'][$tag] = 0;
            }
            
            $nodes['concepts'][$tag] += $value;
        }
    }
    
    function read_authors($author_string, $nodes, $value = 1)
    {
        $object_authors = explode('|', substr($author_string, 1, -1));
        foreach ($object_authors as $author)
        {
            $author_user = $_MIDCOM->auth->get_user($author);
            if (!$author_user)
            {
                continue;
            }
            
            if (!isset($nodes['authors'][$author_user->name]))
            {
                $nodes['authors'][$author_user->name] = 0;
            }
            
            $nodes['authors'][$author_user->name] += $value;
        }
    }
    
    function read_categories($category_string, $nodes, $item_concepts, $value = 1)
    {
        $categories = explode('|', substr($category_string, 1, -1));
        foreach ($categories as $category)
        {
            if (empty($category))
            {
                continue;
            }
            
            if (substr($category, 0, 5) == 'feed:')
            {
                // RSS feed information, can be used for "sources" handling
                if (!isset($this->feed_categories[$category]))
                {
                    // Deleted feed, skip
                    continue;
                }
                $feed_url = $this->feed_categories[$category];
                
                if (!isset($nodes['sources'][$feed_url]))
                {
                    $nodes['sources'][$feed_url] = 0;
                }
                $nodes['sources'][$feed_url] += $value;
                
                continue;
            }
            
            $tag = strtolower($category);
            if (isset($item_concepts[$tag]))
            {
                // We counted this already, skip
                continue;
            }
            $item_concepts[$tag] = true;
            
            if (!isset($nodes['concepts'][$tag]))
            {
                $nodes['concepts'][$tag] = 0;
            }
            
            $nodes['concepts'][$tag] += $value;
        }
    }
    
    function read_favourites($user_guid, $nodes)
    {
        // List user's favs
        $qb = net_nemein_favourites_favourite_dba::new_query_builder();
        $qb->add_constraint('metadata.creator', '=', $user_guid);
        $favs = $qb->execute();
        
        if (count($favs) == 0)
        {
            return false;
        }
        
        foreach ($favs as $fav)
        {
            $item_concepts = array();
            $object = $_MIDCOM->dbfactory->get_object_by_guid($fav->objectGuid);
            if (   !$object
                || !$object->guid)
            {
                // This object is no more
                $fav->delete();
                continue;
            }
            
            $value = 1;
            if ($fav->bury)
            {
                $value = -1;
            }
            
            // Read object tags from database into Concepts
            $this->read_tags($fav->objectGuid, &$nodes, &$item_concepts, $value);

            // Read object authors from database into Authors
            $this->read_authors($object->metadata->authors, &$nodes, $value);
            
            if (   is_a($object, 'midgard_article')
                && strpos($object->extra1, '|') !== false)
            {
                // Special handling for blog articles
                $this->read_categories($object->extra1, &$nodes, &$item_concepts, $value);
            }
        }
        
        return true;
    }
    
    function calculate($user_guid)
    {
        // Prepare data
        $nodes = array
        (
            'concepts' => array(),
            'authors' => array(),
            'sources' => array(),
        );
        
        // Read favourites from DB
        if (!$this->read_favourites($user_guid, &$nodes))
        {
            return $nodes;
        }
        
        // Sort highest scoring items first
        foreach ($nodes as $type => $elements)
        {
            arsort($nodes[$type]);
        }
        
        // Iterate and count values
        foreach ($nodes as $type => $elements)
        {
            $highest = 0;
            foreach ($elements as $key => $score)
            {
                if ($score > $highest)
                {
                    $highest = $score;
                }
                
                $value = 1 / $highest * $score;
                
                if ($value < -1)
                {
                    $value = -1;
                }

                $nodes[$type][$key] = $value;
            }
        }
        
        return $nodes;
    }
    
    function import($user)
    {
        $nodes = $this->calculate($user->guid);
        
        // Iterate and store values
        foreach ($nodes as $type => $elements)
        {
            foreach ($elements as $key => $value)
            {
                // Store to database
                switch ($type)
                {
                    case 'concepts':
                        $object = net_nemein_attention_concept_dba::get_concept($key, $user->id, 'web');
                        $object->source = $_SERVER['SERVER_NAME'];
                        $object->value = $value;
                        $object->update();
                        break;
                    case 'sources':
                        $object = net_nemein_attention_source_dba::get_source($key, $user->id, 'web');
                        $object->source = $_SERVER['SERVER_NAME'];
                        $object->value = $value;
                        $object->update();
                        break;
                }
            }
        }
        
        return true;
    }
}
?>