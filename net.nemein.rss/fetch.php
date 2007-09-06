<?php
/**
 * @package net.nemein.rss
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: importer.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * RSS and Atom feed fetching class. Caches the fetched items as articles
 * in net.nehmer.blog or events in net.nemein.calendar
 *
 * @package net.nemein.rss
 */
class net_nemein_rss_fetch extends midcom_baseclasses_components_purecode
{
    /**
     * The feed object we're fetching
     */
    var $_feed;

    /**
     * Timestamp, when was the latest item in the feed updated
     */
    var $_feed_updated;
    
    /**
     * Property of midcom_db_article we're using for storing the feed item GUIDs
     */
    var $_guid_property = 'extra2';
    
    /**
     * Current node we're importing to
     * @var midcom_db_topic
     */
    var $_node = null;

    /**
     * Configuration of node we're importing to
     * @var midcom_helper_configuration
     */
    var $_node_config = null;

    /**
     * Datamanager for handling saves
     * @var midcom_helper_datamanager2
     */
    var $_datamanager = null;
    
    /**
     * Initializes the class with a given feed
     */
    function net_nemein_rss_fetch($feed)
    {
        $this->_feed = $feed;
    
        $this->_node = new midcom_db_topic($this->_feed->node);

        $this->_component = 'net.nemein.rss';
        
        $this->_node_config = $GLOBALS['midcom_component_data'][$this->_node->component]['config'];
        
        parent::midcom_baseclasses_components_purecode();
    }
    
    /**
     * Static method for actually fetching a feed
     */
    function raw_fetch($url)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $items = array();
        
        error_reporting(E_WARNING);
        // TODO: Ensure Magpie uses conditional GETs here
        debug_add("Fetching RSS feed {$url}", MIDCOM_LOG_DEBUG);
        $rss = fetch_rss($url);
        error_reporting(E_ALL);
        
        if (!$rss) 
        {
            // Magpie failed fetching or parsing the feed somehow
            debug_add("Failed to fetch or parse feed", MIDCOM_LOG_ERROR);
            debug_add($GLOBALS['MAGPIE_ERROR'], MIDCOM_LOG_ERROR);
            debug_pop();
            return $items;
        }
        
        foreach ($rss->items as $item)
        {
            // Normalize the item
            $item = net_nemein_rss_fetch::normalize_item($item);
            
            if ($item)
            {
                $items[] = $item;
            }
        }
        $rss->items = $items;
        
        debug_add('Got ' . count($items) . ' RSS items.', MIDCOM_LOG_DEBUG);
        debug_pop();        
        
        return $rss;
    }
    
    /**
     * Fetch given RSS or Atom feed
     *
     * @param Array Array of normalized feed items
     */
    function fetch()
    {   
        $rss = net_nemein_rss_fetch::raw_fetch($this->_feed->url);
        
        if (!$rss)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("MagpieRSS did not return any items", MIDCOM_LOG_WARN);
            debug_pop();
            return array();
        }
        
        $this->_feed->latestfetch = time();
        $this->_feed->update();
        
        return $rss->items;
    }
    
    /**
     * Fetches and imports items in the feed
     */
    function import()
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        $items = $this->fetch();
        
        if (count($items) == 0)
        {
            // This feed didn't return any items, skip
            return array();
        }
        
        // Reverse items so that creation times remain in correct order even for feeds without timestamps
        $items = array_reverse($items);
        
        foreach ($items as $item_id => $item)
        {
            $items[$item_id]['local_guid'] = $this->import_item($item);
            
            if (!$items[$item_id]['local_guid'])
            {
                debug_add("Failed to import item {$item['guid']}: " . mgd_errstr(), MIDCOM_LOG_ERROR);
            }
            else
            {
                debug_add("Imported item {$item['guid']} as {$items[$item_id]['local_guid']}", MIDCOM_LOG_INFO);
            }
        }
        
        $this->clean($items);

        debug_pop();        
        return $items;
    }
    
    /**
     * Imports a feed item into the database
     *
     * @param Array $item Feed item as provided by MagpieRSS
     */
    function import_item($item)
    {
        switch ($this->_node->component)
        {
            case 'net.nehmer.blog':
                return $this->import_article($item);
                break;
                
            case 'net.nemein.calendar':
                return $this->import_event($item);
                break;
                
            default:
                $_MIDCOM->generate_error(MIDCOM_ERRCRIT, "RSS fetching for component {$this->_node->component} is unsupported");
                // This will exit.
        }
    }
    
    /**
     * Imports an item as a news article
     */
    private function import_article($item)
    {
        $guid_property = $this->_guid_property;
        $qb = midcom_db_article::new_query_builder();
        $qb->add_constraint('topic', '=', $this->_feed->node);
        // TODO: Move this to a parameter in Midgard 1.8
        $qb->add_constraint($guid_property, '=', substr($item['guid'], 0, 255));
        $articles = $qb->execute();  
        if (count($articles) > 0)
        {
            // This item has been imported already earlier. Update
            $article = $articles[0];
        }
        else
        {
            // This is a new item
            $article = new midcom_db_article();
        }
        
        $updated = false;
        
        // Copy properties
        if ($article->title != $item['title'])
        {
            $article->title = $item['title'];
            $updated = true;
        }
        
        if ($article->name != md5($item['guid']))
        {
            $article->name = md5($item['guid']);
            $updated = true;
        }
        
        // FIXME: This breaks with URLs longer than 255 chars
        if ($article->$guid_property != $item['guid'])
        {
            $article->$guid_property = $item['guid'];
            $updated = true;
        }
        
        if ($article->content != $item['description'])
        {
            $article->content = $item['description'];
            $updated = true;
        }

        $article->topic = $this->_feed->node;
        
        if ($article->url != $item['link'])
        {
            $article->url = $item['link'];
            $updated = true;
        }
        
        $feed_category = 'feed:' . md5($this->_feed->url);
        $orig_extra1 = $article->extra1;
        $article->extra1 = "|{$feed_category}|";

        // Handle categories provided in the feed
        if (isset($item['category']))
        {
            // Check if we have multiple categories
            if (is_array($item['category']))
            {
                // Some systems provide multiple categories as per in spec
                $categories = $item['category'];
            }
            elseif (strstr($item['category'], ','))
            {
                // Some systems expose multiple categories in single category element
                $categories = explode(',', $item['category']);
            }
            else
            {
                $categories = array();
                $categories[] = $item['category'];
            }
            
            foreach ($categories as $category)
            {
                // Clean up the categories and save
                $category = str_replace('|', '_', trim($category));
                $article->extra1 .= "{$category}|";
            }
        }
        
        if ($orig_extra1 != $article->extra1)
        {
            $updated = true;
        }
        
        // Try to figure out item author
        if (   $this->_feed->forceauthor
            && $this->_feed->defaultauthor)
        {
            // Feed has a "default author" set, use it
            $article_author = new midcom_db_person($this->_feed->defaultauthor);
        }
        else
        {
            $article_author = $this->match_item_author($item);
            $fallback_person_id = 1;
            if (   !$article_author
                || $article_author->id == $fallback_person_id)
            {
                if ($this->_feed->defaultauthor)
                {
                    // Feed has a "default author" set, use it
                    $article_author = new midcom_db_person($this->_feed->defaultauthor);
                }
                else
                {
                    // Fall back to "Midgard Admin" just in case
                    $fallback_author = new midcom_db_person($fallback_person_id);
                    $article_author = $fallback_author;
                }
            }
        }
        
        if (   is_object($article_author)
            && $article_author->guid)
        {
            if ($article->author != $article_author->id)
            {
                $article->author = $article_author->id;
                $updated = true;
            }
            
            if ($article->metadata->authors != "|{$article_author->guid}|")
            {
                $article->metadata->authors = "|{$article_author->guid}|";
                $updated = true;
            }
        }
        
        // Try to figure out item publication date
        $article_date = null;
        if (isset($item['date_timestamp']))
        {
            $article_date = $item['date_timestamp'];
        }
        $article_data_tweaked = false;
        if (!$article_date)
        {
            $article_date = time();
            $article_data_tweaked = true;
        }
        
        if ($article_date > $this->_feed->latestupdate)
        {
            // Cache "latest updated" time to feed
            $this->_feed->latestupdate = $article_date;
            $this->_feed->update();
        }
                
        if ($article->id)
        {
            if (   $article->metadata->published != $article_date
                && !$article_data_tweaked) 
            {
                $article->metadata->published = $article_date;
                $updated = true;
            }

            if (!$updated)
            {
                // No data changed, avoid unnecessary I/O
                return $article->guid;
            }
            
            if ($article->update())
            {
                if ($this->_feed->autoapprove)
                {
                    $metadata =& midcom_helper_metadata::retrieve($article);
                    $metadata->approve();
                }

                $this->parse_tags($article, $item);
                $this->parse_parameters($article, $item);
                
                return $article->guid;
            }
            
            return false;
        }
        else
        {
            if ($article->create())
            {
                $article->metadata->published = $article_date;
                $article->update();
                
                if ($this->_feed->autoapprove)
                {
                    $metadata =& midcom_helper_metadata::retrieve($article);
                    $metadata->approve();
                }
            
                $this->parse_tags($article, $item);
                $this->parse_parameters($article, $item);
                            
                return $article->guid;
            }
            return false;
        }
    }
    
    /**
     * Imports an item as an event
     */
    private function import_event($item)
    {
        // Check that we're trying to import item suitable to be an event
        if (!isset($item['xcal']))
        {
            // Not an event
            return false;
        }
            
        // Load root event
        if (!$this->_node_config->get('root_event'))
        {
            // This calendar is not really functional
            return false;
        }   
        static $root_event = null;
        if (is_null($root_event))
        {
            $root_event = new net_nemein_calendar_event($this->_node_config->get('root_event'));
        }
        if (!$root_event->guid)
        {
            return false;
        }

        // Get start and end times
        $start = null;
        $end = null;
        if (isset($item['xcal']['dtstart']))
        {
            $start = strtotime($item['xcal']['dtstart']);
        }
        elseif (isset($item['xCal']['start']))
        {
            // The format used by Upcoming
            $start = strtotime($item['xCal']['start']);
        }
        if (isset($item['xcal']['dtend']))
        {
            $end = strtotime($item['xcal']['dtend']);
        }
        elseif (isset($item['xCal']['end']))
        {
            // The format used by Upcoming
            $end = strtotime($item['xCal']['end']);
        }
        
        if (   !$start
            || !$end)
        {
            return false;
        }
        
        if (!$this->_datamanager)
        {
            $schemadb = midcom_helper_datamanager2_schema::load_database($this->_node_config->get('schemadb'));
            $this->_datamanager = new midcom_helper_datamanager2_datamanager($schemadb);
        }

        $qb = net_nemein_calendar_event::new_query_builder();
        $qb->add_constraint('up', '=', $root_event->id);
        $qb->add_constraint('extra', '=', md5($item['guid']));
        $events = $qb->execute();  
        if (count($events) > 0)
        {
            // This item has been imported already earlier. Update
            $event = $events[0];
        }
        else
        {
            // This is a new item
            $event = new net_nemein_calendar_event();
            $event->start = $start;
            $event->end = $end;
            $event->extra = md5($item['guid']);
            $event->up = $root_event->id;
            if (!$event->create())
            {
                return false;
            }
        }
        
        $this->_datamanager->autoset_storage($event);
        $this->_datamanager->types['start']->value = new Date($start);
        $this->_datamanager->types['end']->value = new Date($end);
        foreach ($item as $key => $value)
        {
            if (isset($this->_datamanager->types[$key]))
            {
                $this->_datamanager->types[$key]->value = $value;
            }
        }
        
        if (!$this->_datamanager->save())
        {
            return false;
        }
        
        return $event->guid;
    }
    
    /**
     * Cleans up old, removed items from feeds
     * @param Array $item Feed item as provided by MagpieRSS
     */
    function clean($items)
    {
        if ($this->_feed->keepremoved)
        {
            // This feed is set up so that we retain items removed from array
            return false;
        }

        // Create array of item GUIDs
        $item_guids = array();
        foreach ($items as $item)
        {
            $item_guids[] = $item['guid'];
        }
    
        // Find articles resulting from this feed
        $qb = midcom_db_article::new_query_builder();
        $feed_category = md5($this->_feed->url);
        $qb->begin_group('OR');
            $qb->add_constraint('extra1', 'LIKE', "%|feed:{$feed_category}|%");
            // TODO: This is the old format, we can remove it later
            $qb->add_constraint('extra1', 'LIKE', "%|{$feed_category}|%");
        $qb->end_group();
        $local_items = $qb->execute_unchecked();
        $guid_property = $this->_guid_property;
        foreach ($local_items as $item)
        {
            if (!in_array($item->$guid_property, $item_guids))
            {
                // This item has been removed from the feed.
                
                // If it has been favorited keep it
                $_MIDCOM->componentloader->load_graceful('net.nemein.favourites');
                $qb = net_nemein_favourites_favourite_dba::new_query_builder();
                $qb->add_constraint('objectGuid', '=', $item->guid);
                if ($qb->count_unchecked() > 0)
                {
                    continue;
                    // Skip deleting this one
                }
                
                $item->delete();
            }
        }
        return true;
    }
    
    /**
     * Parses author formats used by different feed standards and
     * and returns the information
     *
     * @param Array $item Feed item as provided by MagpieRSS
     * @return Array Information found
     */
    function parse_item_author($item)
    {
        $author_info = array();
        
        // First try dig up any information about the author possible
        
        if (isset($item['author']))
        {   
            if (strstr($item['author'], '<'))
            {
                // The classic "Full Name <email>" format
                $regex = '/(.+) <?([a-zA-Z0-9_.-]+?@[a-zA-Z0-9_.-]+)>?[ ,]?/';
                if (preg_match_all($regex, $item['author'], $matches_to))
                {
                    foreach ($matches_to[1] as $fullname)
                    {
                        $author_info['user_or_full'] = $fullname;
                    }
                    foreach ($matches_to[2] as $email)
                    {
                        $author_info['email'] = $email;
                    }
                }
            }
            elseif (strstr($item['author'], '('))
            {
                // The classic "email (Full Name)" format
                $regex = '/^([a-zA-Z0-9_.-]+?@[a-zA-Z0-9_.-]+) \((.+)\)$/';
                if (preg_match_all($regex, $item['author'], $matches_to))
                {
                    foreach ($matches_to[1] as $email)
                    {
                        $author_info['email'] = $email;
                    }
                    foreach ($matches_to[2] as $fullname)
                    {
                        $author_info['user_or_full'] = $fullname;
                    }
                }
            }
            else
            {
                $author_info['user_or_full'] = $item['author'];
            }
        }
        
        if (isset($item['author_name']))
        {
            // Atom feed, the value can be either full name or username
            $author_info['user_or_full'] = $item['author_name'];

        }
        
        if (isset($item['dc']))
        {
            // We've got Dublin Core metadata
            if (isset($item['dc']['creator']))
            {
                $author_info['user_or_full'] = $item['dc']['creator'];
            }
        }
        
        if (isset($author_info['user_or_full']))
        {
            if (strstr($author_info['user_or_full'], ' '))
            {
                // This value has a space in it, assuming full name
                $author_info['full_name'] = $author_info['user_or_full'];
            }
            else
            {
                $author_info['username'] = $author_info['user_or_full'];
            }
            unset($author_info['user_or_full']);
        }
        
        return $author_info;
    }
    
    /**
     * Parses author formats used by different feed standards and
     * tries to match to persons in database.
     *
     * @param Array $item Feed item as provided by MagpieRSS
     * @return MidgardPerson Person object matched, or NULL
     */
    function match_item_author($item)
    {
        // Parse the item for author information
        $author_info = $this->parse_item_author($item);
        
        // Start matching the information found to person entries in the database
        $matched_person = null;
        
        if (isset($author_info['email']))
        {
            // Email is a pretty good identifier, start with it
            $person_qb = midcom_db_person::new_query_builder();
            $person_qb->add_constraint('email', '=', $author_info['email']);
            $persons = $person_qb->execute();
            if (count($persons) > 0)
            {
                $matched_person = $persons[0];
            }
        }
        
        if (   is_null($matched_person)
            && isset($author_info['username']))
        {
            // Email is a pretty good identifier, start with it
            $person_qb = midcom_db_person::new_query_builder();
            $person_qb->add_constraint('username', '=', strtolower($author_info['username']));
            $persons = $person_qb->execute();
            if (count($persons) > 0)
            {
                $matched_person = $persons[0];
            }
        }
        
        if (   is_null($matched_person)
            && isset($author_info['full_name']))
        {

            $name_parts = explode(' ', $author_info['full_name']);
            if (count($name_parts) > 1)
            {
                // We assume the western format Firstname Lastname            
                $firstname = $name_parts[0];
                $lastname = $name_parts[1];
                
                $person_qb = midcom_db_person::new_query_builder();
                $person_qb->add_constraint('firstname', '=', $firstname);
                $person_qb->add_constraint('lastname', '=', $lastname);
                $persons = $person_qb->execute();
                if (count($persons) > 0)
                {
                    $matched_person = $persons[0];
                }
            }
        }
        
        return $matched_person;
    }

    /**
     * Parses additional metadata in RSS item and sets parameters accordingly
     *
     * @param midgard_article $article Imported article
     * @param Array $item Feed item as provided by MagpieRSS
     * @return boolean
     */    
    function parse_parameters($article, $item)
    {
        if (isset($item['media']))
        {
            foreach ($item['media'] as $name => $value)
            {
                $article->parameter('net.nemein.rss:media', $name, $value);
            }
        }
        
        if (isset($item['enclosure@url']))
        {
            $article->parameter('net.nemein.rss:enclosure', 'url', $item['enclosure@url']);
        }

        if (isset($item['enclosure@duration']))
        {
            $article->parameter('net.nemein.rss:enclosure', 'duration', $item['enclosure@duration']);
        }

        if (isset($item['enclosure@type']))
        {
            $article->parameter('net.nemein.rss:enclosure', 'mimetype', $item['enclosure@type']);
        }
        
        return true;
    }

    /**
     * Parses rel-tag links in article content and tags the object based on them
     *
     * @param midgard_article $article Imported article
     * @param Array $item Feed item as provided by MagpieRSS
     * @return boolean
     */    
    function parse_tags($article, $item)
    {
        $html_tags = org_openpsa_httplib_helpers::get_anchor_values($article->content, 'tag');
        $tags = array();
        
        if (count($html_tags) > 0)
        {
            foreach ($html_tags as $html_tag)
            {
                if (!$html_tag['value'])
                {
                    // No actual tag specified, skip
                    continue;
                }
                
                $tag = strtolower(strip_tags($html_tag['value']));
                $tags[$tag] = $html_tag['href'];
            }
            
            return net_nemein_tag_handler::tag_object($article, $tags);
        }
        
        return true;
    }

    /**
     * Normalizes items provided by different feed formats.
     *
     * @param Array $item Feed item as provided by MagpieRSS
     * @param Array Normalized feed item
     */
    function normalize_item($item) 
    {

        if (!is_array($item)) 
        {
            // Broken item, skip
            return false;
        }
        
        // Fix missing titles
        if (   !isset($item['title']) 
            || !$item['title']) 
        {
            $item['title'] = $_MIDCOM->i18n->get_string('untitled', 'net.nemein.rss');

            //$item_date = net_nemein_rss_fetch::parse_item_date($item);
            $item_date = $item['date_timestamp'];
            
            // Check if this item is newer than the others
            if (isset($this))
            {
                if ($item_date > $this->_feed_updated) 
                {
                    $this->_feed_updated = $item_date;
                }
            }

            if (isset($item['description'])) 
            {
                // Use 20 first characters from the description as title
                $item['title'] = substr(strip_tags($item['description']), 0, 20) . '...';
            } 
            elseif ($item_date) 
            {
                // Use publication date as title
                $item['title'] = strftime('%x', $item_date);
            }
        }

        // Fix missing links
        if (   !isset($item['link']) 
            || !$item['link']) 
        {
            $item['link'] = '';
            if (isset($item['guid'])) 
            {
                $item['link'] = $item['guid'];
            } 
        }
        
        if (!array_key_exists('link', $item))
        {
            // No link or GUID defined
            // TODO: Generate a "link" using channel URL
            $item['link'] = '';
        }

        // Fix missing GUIDs
        if (   !isset($item['guid']) 
            || !$item['guid']) 
        {
            if (isset($item['link'])) 
            {
                $item['guid'] = $item['link'];
            } 
        }

        if (   !isset($item['description'])
            || !$item['description'])
        {
            // Ensure description is always set
            $item['description'] = '';
        }

        if (   isset($item['content']) 
            && isset($item['content']['encoded']))
        {
            // Some RSS feeds use "content:encoded" for storing HTML-formatted full item content, 
            // so we prefer this instead of simpler description
            $item['description'] = $item['content']['encoded'];
        }
        
        if ($item['description'] == '')
        {
            // Empty description, fallbacks for some feed formats
            if (   isset($item['dc'])
                && isset($item['dc']['description']))
            {
                $item['description'] = $item['dc']['description'];
            } 
            elseif (isset($item['atom_content']))
            {
                // Atom 1.0 feeds store content in the atom_content field
                $item['description'] = $item['atom_content'];
            }
            elseif (strpos($item['link'], 'cws.huginonline.com') !== false)
            {
                // Deal with the funky RSS format provided by Hugin Online
                // Link points to actual news item in hexML format
                $http_client = new org_openpsa_httplib();
                $news_xml = $http_client->get($item['link']);
                $news = simplexml_load_string($news_xml);
                if (isset($news->body->press_releases->press_release->main))
                {
                    $item['description'] = (string) $news->body->press_releases->press_release->main;
                }
            }
        }
        
        return $item;
    }
}
?>
