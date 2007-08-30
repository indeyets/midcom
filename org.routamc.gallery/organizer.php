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
        if (trim($string) === '')
        {
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
        }
        
        switch ($string)
        {
            case 'score':
                $sort = 'metadata.score';
                
                break;
            
            default:
                $sort = strtolower("{$domain}{$string}");
        }
        
        $this->sort = $sort;
        
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
        $mc = org_routamc_gallery_photolink_dba::new_collector('node', $this->node);
        $mc->add_value_property('id');
        $mc->add_value_property('node');
        $mc->add_value_property('photo');
        
        $mc->add_constraint('censored', '=', 0);
        $mc->add_order($this->sort);
        
        // Set the offset
        $offset = $this->limit * $this->page;
        
        // Set limit
        if ($this->limit)
        {
            $mc->set_limit($this->limit);
        }
        
        // Add offset
        if ($this->page)
        {
            $mc->add_offset($offset);
        }
        
        // Execute the collector
        $mc->execute();
        
        $links = $mc->list_keys();
        
        // Initialize results set
        $results = array ();
        
        foreach ($links as $guid => $array)
        {
            $results[$mc->get_subkey($guid, 'id')] = new org_routamc_photostream_photo_dba($mc->get_subkey($guid, 'photo'));
        }
        
        // Return reversed results
        if ($this->reverse)
        {
            return array_reverse($results, true);
        }
        
        return $results;
    }
}
?>