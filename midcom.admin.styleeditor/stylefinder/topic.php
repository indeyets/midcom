<?php
/**
 * Created on Oct 30, 2005
 * @author tarjei huse
 * @package 
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * This class is a helper for the styleeditor to get the styleelement it wants to edit. 
 */
 
class midcom_admin_styleeditor_stylefinder_topic {

    var $_topic = null;
    // the stylestack for the topic
    var $_stack = array();
    /**
     * constructor
     * @param object refernce to the page in question
     */
    function midcom_admin_styleeditor_stylefinder_topic (&$topic ) {
        $this->_topic = &$topic;
    }
    
    /**
     * This funtion returns the stylestack for a page by finding the context of the 
     * page. Someday... this will not be needed :-)
     */
    function get_style_stack() 
    {
       
        $topicstyle = $this->_get_topic_style();
        
        if ($topicstyle) {
            $style = new midgard_style();
            $style->get_by_id($topicstyle);
            $this->_stack[] = $style;
        }
        
        return $this->_stack;
    }
    
    /**
     * get the styleid assosiated with the topic
     */
    function _get_topic_style() {
        
         // for now, the page is a topic. First we get the topics, style
        $topicstyle = $this->_topic->parameter('midcom', 'style');
        $parent = false;
        if (!$topicstyle) while (!$topicstyle && $parent !== false) {
            if ($parent) {
                $parent = new midcom_db_topic($parent->up);
            } else {
                $parent = new midcom_db_topic($this->_topic->up);
            }
            
            $topicstyle = $parent->parameter('midcom', 'style');
        }
        return $topicstyle;
    
    }
    
    
    /**
     * Get the styleelements assosiated with the topic.
     * @return indexed array 
     * @param midcom_admin_styleeditor_stylefinder_style element
     * @see midcom_admin_styleeditor_stylefinder_style::get_style_elements();
     */
    function get_style_elements(&$stylefinder) {
        $topicstyle = $this->_get_topic_style();

        return $stylefinder->get_style_elements_complete($topicstyle);
        /*
        $return = array();
        foreach ($this->_stack as $style) {
            $stylenames[] = $style->name;
        }
        foreach ($this->_stack as $style) {
            $qb = new MidgardQueryBuilder("midgard_element");
        $qb->add_constraint('style', '=', $style->id);
            $elements = $qb->execute();
            if ($elements !== null && is_array ($elements)) {
                foreach ($elements as $element) {
                     $return[$element->name] = array (
                        'name' => $element->name,
                        'guid' => $element->guid,
                        'styleurl' => '',
                        'comment' =>''
                         );
                }
            }
        }
        print_r($return);
        return $return;
        */
    }
    
}
