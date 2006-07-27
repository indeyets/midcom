<?php
/**
 * @package net.nemein.wiki
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Wiki note helper class to be used by other components
 * 
 * @package net.nemein.wiki
 */
class net_nemein_wiki_wikipage extends midcom_db_article
{
    /**
     * The topic object, cached for ACL checks
     */
    var $_topic = null;

    /**
     * The default constructor will create an empty object. Optionally, you can pass
     * an object ID or GUID to the object which will then initialize the object with
     * the corresponding DB instance.
     *
     * @param mixed $id A valid object ID or GUID, omit for an empty object.
     */
    function net_nemein_wiki_wikipage($id = null)
    {
        parent::midcom_db_article($id);
    }

    /**
     * Overwrite the query builder getter with a version retrieving the right type.
     * We need a better solution here in DBA core actually, but it will be difficult to
     * do this as we cannot determine the current class in a polymorphic environment without
     * having a this (this call is static).
     */
    function new_query_builder()
    {
        return $_MIDCOM->dbfactory->new_query_builder(__CLASS__);
    }
    
    function _on_loaded()
    {
        // Backwards compatibility
        if ($this->name == '')
        {
            $this->name = midcom_generate_urlname_from_string($this->title);
            $this->update();
        }
        return true;
    }
    
    function _on_creating()
    {
        if (   $this->title == ''
            || !$this->topic)
        {
            // We must have wikiword and topic at this stage
            return false;
        }
        
        // Check for duplicates
        $qb = net_nemein_wiki_wikipage::new_query_builder();
        $qb->add_constraint('topic', '=', $this->topic);
        $qb->add_constraint('title', '=', $this->title);
        $result = $qb->execute();
        if (count($result) > 0)
        {
            return false;
        }
        
        // Generate URL-clean name
        if ($this->name != 'index')
        {
            $this->name = midcom_generate_urlname_from_string($this->title);
        }    
        return true;
    }
    
    function _on_updated()
    {
        // TODO: RCS should be handled by DBA instead of here
        $rcs_handler = new no_bergfald_rcs_aegirrcs($this->guid);
        $rcs_handler->save_object($this, null);
    
        return parent::_on_updated();
    }

    function replace_wikiwords($match)
    {

        // Refactored using code from the WordPress SimpleLink plugin
        // http://warpedvisions.org/projects/simplelink
        $fulltext = $match[1];
        $after = $match[2] or '';
        $wikilink = null;
        $url = null;
        $class = null;

        // Ignore markdown tags
        if (preg_match("/[\(:\[]/", $after))
        {
            // TODO: should by str match (array) instead
            return $match[0];
        }
    
        // Escaped tag [!!text]
        if (preg_match("/^\!\!(.*)/", $fulltext, $parts)) 
        {
            // TODO: find a better format
            return "[{$parts[1]}]{$after}";
        }
        // MediaWiki-style link [wikipage|label]
        elseif (preg_match("/^(.*?)\|(.*?)$/i", $fulltext, $parts))
        {
            $text = $parts[2];
            $wikilink = $parts[1];
        }
        // WikiPedia term search [wiki: search terms]            
        elseif (preg_match("/^wiki: (.*)/", $fulltext, $parts))
        {
            // FIXME: Switch to InterWiki format instead
            $text = $parts[1];
            $target = ucfirst(strtolower(preg_replace('/[\s-,.\']+/', "_", $text)));
            $url = "http://en.wikipedia.org/wiki/{$target}";
            $class = 'wikipedia';
        }
        // Abbreviation support [abbr: Abbreviation - Explanation]
        elseif (preg_match("/^abbr: (.*?) \- (.*)/", $fulltext, $parts)) 
        {
            return "<abbr title=\"{$parts[2]}\">{$parts[1]}</abbr>{$after}";    
        }     
        // Photo inclusion support [photo: GUID]
        elseif (preg_match("/^photo: (.*)/", $fulltext, $parts)) 
        {
            debug_add("Photo inclusion {$parts[1]}");
            
            // Get the correct photo NAP object based on the GUID
            $nap = new midcom_helper_nav();
            $photo = $nap->resolve_guid($parts[1]);
            $show_photo = false;
            
            if ($photo
                && !$this->_rss)
            {
                // FIXME: This has been disabled under RSS mode, as dynamic loading
                // doesn't work there
                if ($photo[MIDCOM_NAV_TYPE] == 'leaf')
                {
                    $node = $nap->get_node($photo[MIDCOM_NAV_NODEID]);
                    if ($node[MIDCOM_NAV_COMPONENT] == 'net.siriux.photos')
                    {
                        $show_photo = true;
                    }
                }
            }
            
            if (!$show_photo)
            {
                debug_add("Requested photo was not valid, showing 'missing photo' tag");
                debug_pop();
                return "<span class=\"missing_photo\" title=\"{$parts[1]}\">{$fulltext}</span>{$after}";
            }
            
            // Start buffering
            $oldcontext = $_MIDCOM->_currentcontext;
            ob_start();
            // Load the photo
            $_MIDCOM->dynamic_load($photo[MIDCOM_NAV_RELATIVEURL]);
            $content = str_replace('h1', 'h3', ob_get_contents());
            ob_end_clean();
            
            // Return from the DLd context into the correct context
            // FIXME: Why doesn't dynamic_load do this by itself?
            $_MIDCOM->style->enter_context($oldcontext);
            
            debug_pop();
            return "{$content}{$after}"; 
        }    
        // MediaWiki-style link [wikipage] (no text)
        else
        {
            $text = $fulltext;
            $wikilink = $fulltext;
        }    
    
        if ($wikilink)
        {
            $prefix = $_MIDCOM->get_context_data(MIDCOM_CONTEXT_ANCHORPREFIX);

            $match = null;
            $qb = net_nemein_wiki_wikipage::new_query_builder();
            $qb->add_constraint('topic', '=', $this->topic);
            $qb->add_constraint('title', '=', $wikilink);
            $result = $qb->execute();
            
            foreach ($result as $wikipage_found)
            {
                $match = $wikipage_found;
            }          
            
            if ($match)
            {
                if ($match->name == 'index')
                {
                    $match->name = '';
                }
                else
                {
                    $match->name = "{$match->name}.html";
                }
                return "<a href=\"{$prefix}{$match->name}\">{$text}</a>{$after}";
            }
            else
            {
                if (!$this->_topic)
                {
                    $this->_topic = new midcom_db_topic($this->topic);
                }
                if ($this->_topic->can_do('midgard:create'))
                {
                    $wikilink = rawurlencode(str_replace('/','_',$wikilink));
                    return "<a href=\"{$prefix}create/{$wikilink}\" class=\"wiki_missing\" title=\"{click to create}\">{$text}</a>{$after}";
                } 
                else
                {
                    return "<span class=\"wiki_missing_nouser\" title=\"{login to create}\">{$text}</span>{$after}";
                }        
            }
        }
        elseif ($url)
        {
            $css_class = '';
            if ($class)
            {
                $css_class = " class=\"{$class}\"";
            }
            return "<a href=\"{$url}\"{$css_class}>{$text}</a>{$after}";
        }
        else
        {
            return $fulltext;
        }
    }
}
?>