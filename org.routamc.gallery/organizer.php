<?php
/**
 * @package org.routamc.gallery 
 * @author The Midgard Project, http://www.midgard-project.org
 * @version $Id: organizer.php 3757 2006-07-27 14:32:42Z bergie $
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Organizer helper class
 * 
 * @package org.routamc.gallery
 */
class org_routamc_gallery_organizer
{
    /**
     * Sort by this property
     * 
     * @access protected
     * @var String $sort
     */
    var $sort = 'photo.taken';
    
    /**
     * Should the sorting be reversed?
     * 
     * @access public
     * @var boolean $reverse
     */
    var $reverse = true;
    
    /**
     * Should post processing be done to photo links (pre Midgard 1.8.2)
     * 
     * @access private
     */
    var $_post_processing = true;
    
    /**
     * Should legacy support be enabled (i.e. pre Midgard 1.8.2)
     * 
     * @access private
     * @var boolean $_legacy
     */
    var $_legacy = true;
    
    /**
     * Limit the request to a certain number
     * 
     * @access public
     * @var int $limit
     */
    var $limit = 0;
    
    /**
     * Set page offset to the request
     * 
     * @access public
     * @var int $offset
     */
    var $page = 0;
    
    /**
     * Gallery node id (i.e. topic id)
     * 
     * @access public
     * @var int $node
     */
    var $node = null;
    
    /**
     * Constructor
     * 
     * @access public
     */
    function org_routamc_gallery_organizer($sort_string = null)
    {
        if (version_compare(mgd_version(), '1.8.2', '>='))
        {
            $this->_legacy = false;
            $this->_post_processing = false;
        }
        
        if ($sort_string)
        {
            if (is_array($sort_string))
            {
                foreach ($sort_string as $value)
                {
                    $sort_string = $value;
                    break;
                }
            }
            
            $this->sort_by($sort_string);
        }
    }
    
    /**
     * Set sorting method
     * 
     * @access public
     * @return boolean Indicating success
     */
    function sort_by($string)
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (trim($string) === '')
        {
            debug_add('No sorting string set, using default');
            debug_pop();
            
            return true;
        }
        
        if (stristr($string, 'reverse'))
        {
            debug_add('Reversing the results');
            
            $this->reverse = true;
            $string = trim(preg_replace('/\s*reversed?/i', '', $string));
        }
        else
        {
            $this->reverse = false;
        }
        
        $domain = '';
        
        if (strstr($string, '.'))
        {
            $regs = explode('.', $string);
            
            $domain = $regs[0];
            $string = $regs[1];
            $post_processing = true;
        }
        
        switch ($string)
        {
            case 'score':
                if ($this->_legacy)
                {
                    $sort = 'score';
                    $post_processing = false;
                }
                else
                {
                    $sort = 'metadata.score';
                }
                
                break;
            
            default:
                if ($this->_legacy)
                {
                    $sort = $string;
                }
                else
                {
                    $sort = strtolower("{$domain}{$string}");
                }
        }
        
        $this->sort = $sort;
        
        debug_add("Sorting will be done according to property '{$this->sort}'");
        
        if (isset($post_processing))
        {
            debug_add('Pre Midgard 1.8.2 post processing is required');
            $this->_post_processing = $post_processing;
        }
        
        debug_pop();
        return true;
    }
    
    /**
     * Organize the photos
     * 
     * @access public
     * @return Array containing org_routamc_photo_photo objects
     */
    function get_sorted()
    {
        // Initialize the query builder
        $qb = org_routamc_gallery_photolink_dba::new_query_builder();
        $qb->add_constraint('node', '=', $this->node);
        
        // Set the offset
        $offset = $this->limit * $this->page;
        
        // Shorten the variable name
        $sort = $this->sort;
        
        // Initialize results set
        $results = array ();
        
        // No post processing required, get the results straight
        if (!$this->_post_processing)
        {
            $qb->add_order($this->sort);
            
            // Set limit
            if ($this->limit)
            {
                $qb->set_limit($this->limit);
            }
            
            // Add offset
            if ($this->page)
            {
                $qb->add_offset($offset);
            }
            
            // Get the results
            $results = array();
            
            foreach ($qb->execute() as $link)
            {
                $photo[$link->id] = new org_routamc_photostream_photo_dba($link->photo);
                
                if (@$photo->censored)
                {
                    continue;
                }
                
                $results[$link->id] =& $photo[$link->id];
            }
            
            // Return reversed results
            if ($this->reverse)
            {
                return array_reverse($results, true);
            }
            
            return $results;
        }
        else
        {
            $links = array ();
            $photos = array ();
            
            foreach ($qb->execute() as $link)
            {
                $photo = new org_routamc_photostream_photo_dba($link->photo);
                
                if (@$photo->censored)
                {
                    continue;
                }
                
                // QUICKFIX: the sort is specified in QB format, but of course it's not a valid property name
                if (strpos($sort, '.') !== false)
                {
                    list ($prop1, $prop2) = explode('.', $sort, 2);
                    $links[$link->id] = $photo->$prop1->$prop2;
                }
                else
                {                
                    $links[$link->id] = $photo->$sort;
                }
                $photos[$link->id] = $photo;
            }
            
            // Sort the links
            if ($this->reverse)
            {
                arsort($links);
            }
            else
            {
                asort($links);
            }
            
            // Set limit
            if ($this->limit)
            {
                $limit = $this->limit;
            }
            else
            {
                $limit = count($links);
            }
            
            $i = 0;
            
            foreach ($links as $link_id => $value)
            {
                // Skip due to paging
                if ($i < $offset)
                {
                    continue;
                }
                
                // Break due to paging
                if ($i > $offset + $limit)
                {
                    break;
                }
                
                $results[$link_id] =& $photos[$link_id];
            }
        }
        
        return $results;
    }
}
?>