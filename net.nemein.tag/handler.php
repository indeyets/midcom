<?php
/**
 * @package net.nemein.tag
 * @author Henri Bergius, http://bergie.iki.fi 
 * @version $Id: main.php,v 1.26 2006/07/21 08:40:58 rambo Exp $
 * @copyright Nemein Oy, http://www.nemein.com
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
 
/**
 * Tag handling library
 * 
 * @package net.nemein.tag
 */
class net_nemein_tag_handler extends midcom_baseclasses_components_purecode
{
    function net_nemein_tag_handler()
    {
        parent::midcom_baseclasses_components_purecode();
    }
    
    /**
     * Tags given object with the tags in the string
     *
     * Creates missing tags and tag_links, sets tag_link navorder
     * Deletes tag links from object that are not in the list provided
     *
     * @param object $object MidCOM DBA object
     * @param array $tags List of tags and urls, tag is key, url is value
     * @return boolean indicating success/failure
     * @todo Set the link->navorder property (only in 1.8)
     */
    function tag_object(&$object, $tags, $component = null)
    {
        if (is_null($component))
        {
            $component = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_COMPONENT);
        }
        $existing_tags = net_nemein_tag_handler::get_object_tags($object);
        if (!is_array($existing_tags))
        {
            // Major failure when getting existing tags
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('get_object_tags() reported critical failure, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        // Determine operations
        $add_tags = array();
        $update_tags = array();
        $remove_tags = array();
        foreach ($tags as $tagname => $url)
        {
            if (!array_key_exists($tagname, $existing_tags))
            {
                $add_tags[$tagname] = $url;
            }
            else if (!empty($url))
            {
                $update_tags[$tagname] = $url;
            }
        }
        foreach ($existing_tags as $tagname => $url)
        {
            if (!array_key_exists($tagname, $tags))
            {
                $remove_tags[$tagname] = true;
            }
        }
        
        // Excute
        foreach ($remove_tags as $tagname => $bool)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Removing tag {$tagname} from object {$object->guid}");
            debug_pop();
            $tagstring = net_nemein_tag_handler::resolve_tagname($tagname);
            $context = net_nemein_tag_handler::resolve_context($tagname);
            $value = net_nemein_tag_handler::resolve_value($tagname);
            // Ponder make method in net_nemein_tag_link_dba ??
            $qb = net_nemein_tag_link_dba::new_query_builder();
            $qb->add_constraint('tag.tag', '=', $tagstring);
            $qb->add_constraint('context', '=', $context);
            $qb->add_constraint('value', '=', $value);
            $qb->add_constraint('fromGuid', '=', $object->guid);
            $links = $qb->execute();
            if (!is_array($links))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to fetch tag link(s) for tag \"{$tagstring}\" for object {$object->guid}: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }
            foreach ($links as $link)
            {
                if (!$link->delete())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to delete tag_link \"{$tagname}\" for object {$object->guid}: " . mgd_errstr(), MIDCOM_LOG_WARN);
                    debug_pop();
                    continue;
                }
            }
        }
        foreach ($update_tags as $tagname => $url)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Updating tag {$tagname} for object {$object->guid} to URL {$url}");
            debug_pop();
            $tagstring = net_nemein_tag_handler::resolve_tagname($tagname);
            $tag = net_nemein_tag_dba::get_by_tag($tagstring);
            if (!is_object($tag))
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to update tag \"{$tagname}\" for object {$object->guid} (could not get tag object for tag {$tagstring}): " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }
            $tag->url = $url;
            if (!$tag->update())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to update tag \"{$tagname}\" for object {$object->guid}: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }
        }
        foreach ($add_tags as $tagname => $url)
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add("Adding tag \"{$tagname}\" for object {$object->guid}");
            debug_pop();
            $tagstring = net_nemein_tag_handler::resolve_tagname($tagname);
            $context = net_nemein_tag_handler::resolve_context($tagname);
            $value = net_nemein_tag_handler::resolve_value($tagname);
            $tag = net_nemein_tag_dba::get_by_tag($tagstring);
            if (!is_object($tag))
            {
                $tag =  new net_nemein_tag_dba();
                $tag->tag = $tagstring;
                $tag->url = $url;
                if (!$tag->create())
                {
                    debug_push_class(__CLASS__, __FUNCTION__);
                    debug_add("Failed to create tag \"{$tagstring}\": " . mgd_errstr(), MIDCOM_LOG_WARN);
                    debug_pop();
                    continue;
                }
            }
            $link =  new net_nemein_tag_link_dba();
            $link->tag = $tag->id;
            $link->context = $context;
            $link->value = $value;
            $link->fromGuid = $object->guid;
            $link->fromClass = get_class($object);
            $link->fromComponent = $component;
            
            // Carry the original object's publication date to the tag as well
            $link->metadata->published = $object->metadata->published;
            
            if (!$link->create())
            {
                debug_push_class(__CLASS__, __FUNCTION__);
                debug_add("Failed to create tag_link \"{$tagname}\" for object {$object->guid}: " . mgd_errstr(), MIDCOM_LOG_WARN);
                debug_pop();
                continue;
            }
        }
        
        return true;
    }
    
    /**
     * Resolve actual tag from user-inputted tags that may have contexts or values in them
     *
     * @param string $tagname User-inputted tag that may contain a context or value
     * @return string Tag without context or value
     */
    function resolve_tagname($tagname)
    {
        // first get the context out
        if (strpos($tagname, ':'))
        {
            list ($context, $tag) = explode(':', $tagname, 2);
            $tagname = $tag;
        }
        // then get rid of value
        if (strpos($tagname, '='))
        {
            list ($tag, $value) = explode('=', $tagname, 2);
            $tagname = $tag;
        }
        return trim($tagname);
    }

    /**
     * Resolve value from user-inputted tags that may have machine tag values
     *
     * @param string $tagname User-inputted tag that may contain a value
     * @return string Value without tag or context
     */
    function resolve_value($tagname)
    {
        // first get the context out
        if (strpos($tagname, ':'))
        {
            list ($context, $tag) = explode(':', $tagname, 2);
            $tagname = $tag;
        }
        // then see if we have value
        if (strpos($tagname, '='))
        {
            list ($tag, $value) = explode('=', $tagname, 2);
            return trim($value);
        }
        return '';
    }

    /**
     * Resolve context from user-inputted tags that may contain tag and context
     *
     * @param string $tagname User-inputted tag that may contain a context
     * @return string Context without tag or empty if no context is found
     */
    function resolve_context($tagname)
    {
        if (strpos($tagname, ':'))
        {
            list ($context, $tag) = explode(':', $tagname, 2);
            return trim($context);
        }
        return '';
    }

    /**
     * Copy tasks of one object to another object
     */
    function copy_tags($from, $to, $component = null)
    {
        if (   !is_object($from)
            || !is_object($to))
        {
            return false;
        }
        
        $tags = $this->get_object_tags($from);
        return $this->tag_object($to, $tags, $component);
    }

    /**
     * Gets list of tags linked to the object
     *
     * Tag names are modified to include a possible context in format
     * context:tag
     * 
     * @return array list of tags and urls, tag is key, url is value (or false on failure)
     */
    function get_object_tags(&$object)
    {
        return net_nemein_tag_handler::get_tags_by_guid($object->guid);
    }
    
    function get_tags_by_guid($guid)
    {
        $tags = array();
        $qb = net_nemein_tag_link_dba::new_query_builder();
        $qb->add_constraint('fromGuid', '=', $guid);
        if (class_exists('midgard_query_builder'))
        {
            // 1.8 branch allows ordering by linked properties
            // PONDER: Order by metadata->navorder or by tag alpha ??
            $qb->add_order('tag.tag', 'ASC');
        }   
        $links = $qb->execute();
        if (!is_array($links))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('QB reported critical failure, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach ($links as $link)
        {
            $tag = new net_nemein_tag_dba($link->tag);
            $tagname = net_nemein_tag_handler::tag_link2tagname($link, $tag);
            $tags[$tagname] = $tag->url;
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r("Tags for {$guid}: ", $tags);
        debug_pop();
        return $tags;
    }

    function tag_link2tagname(&$link, $tag=false, $include_context = true)
    {
        if (!is_a($tag, 'net_nemein_tag'))
        {
            $tag = new net_nemein_tag_dba($link->tag);
        }
        switch (true)
        {
            /* Tag with context and value and we want contexts */
            case (   !empty($link->value)
                  && !empty($link->context)
                  && !empty($include_context)):
                $tagname = "{$link->context}:{$tag->tag}={$link->value}";
                break;
            /* Tag with value (or value and context but we don't want contexts) */
            case (!empty($link->value)):
                $tagname = "{$tag->tag}={$link->value}";
                break;
            /* Tag with context (no value) and we want contexts */
            case (   !empty($link->context)
                  && !empty($include_context)):
                $tagname = "{$link->context}:{$tag->tag}";
                break;
            /* Default case, just the tag */
            default:
                $tagname = $tag->tag;
        }
        return $tagname;
    }

    
    /**
     * Gets list of tags linked to objects of a particular class
     *
     * Tag names are modified to include a possible context in format
     * context:tag
     * 
     * @return array list of tags and counts, tag is key, count is value
     */
    function get_tags_by_class($class, $user = null)
    {
        $tags = array();
        $tags_handled = array();
        $qb = net_nemein_tag_link_dba::new_query_builder();
        $qb->add_constraint('fromClass', '=', $class);
        
        if (!is_null($user))
        {
            // TODO: User metadata.authors?
            $qb->add_constraint('metadata.creator', '=', $user->guid);
        }
        
        // TODO: Order by metadata->navorder
        $links = $qb->execute();
        if (!is_array($links))
        {
            debug_push_class(__CLASS__, __FUNCTION__);
            debug_add('QB reported critical failure, aborting', MIDCOM_LOG_ERROR);
            debug_pop();
            return false;
        }
        foreach ($links as $link)
        {
            $tag = new net_nemein_tag_dba($link->tag);
            /* PONDER: here we probably want just the tag ?
            $tagname = net_nemein_tag_handler::tag_link2tagname($link, $tag);
            */
            $tagname = $tag->tag;
            
            if (!array_key_exists($tagname, $tags))
            {
                $tags[$tagname] = 0;
            }
            
            $tags[$tagname]++;
        }
        return $tags;
    }
    
    /**
     * Gets list of tags linked to the object arranged by context
     * 
     * @return array list of contexts containing arrays of tags and urls, tag is key, url is value
     */
    function get_object_tags_by_contexts(&$object)
    {
        $tags = array();
        $qb = net_nemein_tag_link_dba::new_query_builder();
        $qb->add_constraint('fromGuid', '=', $object->guid);
        $qb->add_order('context', 'ASC');
        if (class_exists('midgard_query_builder'))
        {
            // 1.8 branch allows ordering by linked properties
            $qb->add_order('tag.tag', 'ASC');
        }   
        $links = $qb->execute();
        if (!is_array($links))
        {
            return false;
        }
        foreach ($links as $link)
        {
            if ($link->context == '')
            {
                $context = 0;
            }
            else
            {
                $context = $link->context;
            }
            
            if (!array_key_exists($context, $tags))
            {
                $tags[$context] = array();
            }
            
            $tag = new net_nemein_tag_dba($link->tag);
            $tagname = net_nemein_tag_handler::tag_link2tagname($link, $tag, false);
            $tags[$context][$tagname] = $tag->url;
        }
        return $tags;
    }
    
    /**
     * Reads machine tag string from content and returns it, the string is removed from content on the fly
     *
     * @param string $content reference to content
     * @return string string of tags, empty for no tags
     */
    function separate_machine_tags_in_content(&$content)
    {
        $regex = '/^(.*)(tags:)\s+?(.*?)(\.?\s*)?$/si';
        if (!preg_match($regex, $content, $tag_matches))
        {
            return '';
        }
        debug_push_class(__CLASS__, __FUNCTION__);
        debug_print_r('tag_matches: ', $tag_matches);
        debug_pop();
        // safety
        if (!empty($tag_matches[1]))
        {
            $content = rtrim($tag_matches[1]);
        }
        return trim($tag_matches[3]);
    }

    /**
     * Gets list of machine tags linked to the object with a context
     * 
     * @return array of maching tags and values, tag is key, value is value
     */
    function get_object_machine_tags_in_context(&$object, $context)
    {
        $tags = array();
        $qb = net_nemein_tag_link_dba::new_query_builder();
        $qb->add_constraint('fromGuid', '=', $object->guid);
        $qb->add_constraint('context', '=', $context);
        $qb->add_constraint('value', '<>', '');
        $links = $qb->execute();
        if (!is_array($links))
        {
            return false;
        }
        foreach ($links as $link)
        {
            $tag = new net_nemein_tag_dba($link->tag);
            $key = $tag->tag;
            $value = $link->value;
            
            $tags[$key] = $value;
        }
        return $tags;
    }
    
    /**
     * Lists all known tags 
     *
     * @return array list of tags and urls, tag is key, url is value
     */
    function get_tags()
    {
        $tags = array();
        $qb = net_nemein_tag_dba::new_query_builder();
        $db_tags = $qb->execute();
        if (!is_array($db_tags))
        {
            return false;
        }
        foreach ($db_tags as $tag)
        {
            $tags[$tag->tag] = $tag->url;
        }
        return $tags;
    }

    /**
     * Gets all objects of given classes with given tags
     *
     * @param array of tags to search for
     * @param array of classes to search in (NOTE: you must have loaded the files that defined these classes beforehand)
     * @param string AND or OR, depending if you require all of the given tags on any of them, defaults to 'OR'
     * @return array of objects or false on critical failure
     */
    function get_objects_with_tags($tags, $classes, $match = 'OR')
    {
        switch (strtoupper($match))
        {
            case 'ANY':
            case 'OR':
                $match = 'OR';
                break;
            case 'ALL':
            case 'AND':
                $match = 'AND';
                break;
            default:
                // Invalid match rule
                return false;
                break;
        }
        $qb = net_nemein_tag_link_dba::new_query_builder();
        $qb->begin_group('OR');
        foreach ($classes as $class)
        {
            if (!class_exists($class))
            {
                // Invalid class
                return false;
            }
            $qb->add_constraint('fromClass', '=', $class);
        }
        $qb->end_group();
        $qb->begin_group('OR');
        foreach ($tags as $tag)
        {
            $qb->add_constraint('tag.tag', '=', $tag);
        }
        $qb->end_group();
        $qb->add_order('fromGuid', 'ASC');
        // TODO: check midgard version and use this sort if we have 1.8
        if (class_exists('midgard_query_builder'))
        {
            $qb->add_order('tag.tag', 'ASC');
        }
        $links = $qb->execute();
        if (!is_array($links))
        {
            // Fatal QB error
            return false;
        }
        $link_object_map = array();
        $tag_cache = array();
        foreach ($links as $k => $link)
        {
            if (!array_key_exists($link->fromGuid, $link_object_map))
            {
                $link_object_map[$link->fromGuid] = array
                (
                    'object' => false,
                    'links'  => array(),
                );
            }
            $map =& $link_object_map[$link->fromGuid];

            if (!array_key_exists($link->tag, $tag_cache))
            {
                $tag_cache[$link->tag] = new net_nemein_tag_dba($link->tag);
            }
            $tag =& $tag_cache[$link->tag];
            // PHP5-TODO: must be copy by value
            $map['links'][$tag->tag] = $link;
        }
        // Clear this reference or it will cause pain later
        unset($map);

        // For AND matches, make sure we have all the required tags.
        if ($match == 'AND')
        {
            // Filter links that do not contain all of the required tags on each object
            foreach ($link_object_map as $guid => $map)
            {
                $link_map = $link_object_map[$guid]['links'];
                foreach ($tags as $tag)
                {
                    if (   !array_key_exists($tag, $link_map)
                        || !is_object($link_map[$tag])
                        /* For some weird reason we sometimes get wrong tags for the last object (the unset($map) above should fix this)
                        || $guid != $link_map[$tag]->fromGuid
                        */
                        )
                    {
                        unset($link_object_map[$guid]);
                    }
                }
            }
        }
        $return = array();

        // Get the actual objects (casted to midcom DBA if possible)
        foreach ($link_object_map as $map)
        {
            if (!$map['object'])
            {
                $link = array_pop($map['links']);
                $tmpclass = $link->fromClass;
                // Rewrite midgard_ level classes to DBA classes
                $tmpclass = preg_replace('/^midgard_/', 'midcom_db_', $tmpclass);
                if (!class_exists($tmpclass))
                {
                    // We don't have a class available, very weird indeed (rewriting may cause this but midcom has wrappers for all first class DB objects)
                    continue;
                }
                $tmpobject = new $tmpclass($link->fromGuid);
                if (!$tmpobject->guid)
                {
                    continue;
                }
                // PHP5-TODO: Must be copy-by-value
                $map['object'] = $tmpobject;
            }
            $return[] = $map['object'];
        }
        return $return;
    }

    /**
     * Parses a string into tag_array usable with tag_object
     *
     * @see net_nemein_tag_handler::tag_object()
     * @param string $from_string string to parse tags from
     * @return array of correct format
     */
    function string2tag_array($from_string)
    {
        $tag_array = array();
        // Clean all whitespace sequences to single space
        $tags_string = preg_replace('/\s+/', ' ', $from_string);
        // Parse the tags string byte by byte
        $tags = array();
        $current_tag = '';
        $quote_open = false;
        for ($i = 0; $i < (strlen($tags_string)+1); $i++)
        {
            $char = substr($tags_string, $i, 1);
            $hex = strtoupper(dechex(ord($char)));
            //echo "DEBUG: iteration={$i}, char={$char} (\x{$hex})\n";
            if (   (   $char == ' '
                    && !$quote_open)
                || $i == strlen($tags_string))
            {
                $tags[] = $current_tag;
                $current_tag = '';
                continue;
            }
            if ($char === $quote_open)
            {
                $quote_open = false;
                continue;
            }
            if (   $char === '"'
                || $char === "'")
            {
                $quote_open = $char;
                continue;
            }
            $current_tag .= $char;
        }
        foreach ($tags as $tag)
        {
            // Just to be sure there is not extra whitespace in beginning or end of tag
            $tag = trim($tag);
            if (empty($tag))
            {
                continue;
            }
            $tag_array[$tag] = '';
        }
        return $tag_array;
    }

    /**
     * Creates string representation of the tag array
     *
     * @param array $tags 
     * @return string representation
     */
    function tag_array2string($tags)
    {
        $ret = '';
        foreach ($tags as $tag => $url)
        {
            if (strpos($tag, ' '))
            {
                // This tag contains whitespace, surround with quotes
                $tag = "\"{$tag}\"";
            }
            
            // Simply place the tags into a string
            $ret .= "{$tag} ";
        }
        return trim($ret);
    }
}
?>
