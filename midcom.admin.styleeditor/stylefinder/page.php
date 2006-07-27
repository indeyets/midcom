<?php
/**
 * Created on Oct 30, 2005
 * @author tarjei huse
 * @package midcom.admin.styleditor
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * This class is a helper for the styleeditor to get the styleelement it wants to edit. 
 */
 
class midcom_admin_styleeditor_stylefinder_page {

    var $_page = null;
    // the stylestack
    var $_stack = array();
    /**
     * constructor
     * @param object refernce to the page in question
     */
    function midcom_admin_styleeditor_stylefinder_page (&$page) {
        
        if ( is_object($page) ) {
            
            if (is_a( $page, 'midgard_host')) {
                $this->_page = &$page;
            } elseif (is_a( $page, 'midgard_page')) {
                $this->_page = &$page;
            } else {
                //$this->_page = get_class($page);
            }
            
            return;
        }
        
        $this->_page = new midcom_db_page($page);
        return;        
    }
    /**
     * Get the styleid of the page
     * @access public
     * @return the id of the pagestyle.
     */
    function get_style_id() {
        return $this->_page->style;
    }
    
    function get_title() {
        return $this->_page->name;
    }
    
    /**
     * This funtion returns the stylestack for a page by finding the context of the 
     * page. Someday... this will not be needed :-)
     */
    function get_style_stack() 
    {
        //print "<pre>";
        //var_dump($this->_page);
        //print "</pre>";
        // for now, the page is a topic. First we get the topics, style
        
        $style = new midgard_style ();
        $style->get_by_id($this->_page->style);
        $styles[] = $style;
        
        
        for ($i = 0; $style->up != 0 && $i < 20; $i++) {
             $style->get_by_id($style->up);
             $styles[] = $style;
             
        }
        return $styles;        
                 
    }
    /**
     * Get the pageelements assosiated with the page.
     * @return indexed array 
     * @see midcom_admin_stylefinder::get_style_elements();
     */
    function get_style_elements() {
        
        $qb = new MidgardQueryBuilder("midgard_pageelement");
        
        $qb->add_constraint('page', '=', $this->_page->id);
        
        $elements = $qb->execute();
        $return = array();
        if ($elements !== null && is_array ($elements)) {
            foreach ($elements as $element) {
                 $return[$element->name] = array (
                    'name' => $element->name,
                    'guid' => $element->guid,
                    'styleurl' => '',
                    'comment' =>'',
                    'up' => null
                     );
            }
        }
        
        return $return;
    }
    
}
