<?php



class midcom_admin_styleeditor_stylefinder {

    /**
     * Page stylefinder
     */
    var $_page = null;
    
    /**
     * topic stylefinder
     */
     var $_topic = null;
     
     /**
      * midcom stylefinder
      */
     var $_midcom;
     
     /**
      * The current style
      */
     var $_style;

    function midcom_admin_styleeditor_stylefinder() 
    {
        
    }
    
    /**
     * set the reference to the cahce.
     * @param reference ref to the navigation cache.
     */
    function set_cache (&$cache) 
    {
        $this->_cache = &$cache;
    }

    function set_topic (&$topic) 
    {
        if (is_object($topic)) {
            require_once ('stylefinder/topic.php');
            $this->_topic = new midcom_admin_styleeditor_stylefinder_topic(&$topic);
            
            if ($this->_midcom == null) {
                $this->set_midcom($topic->parameter('midcom' , 'component'));
            }
        }
    }
    /**
     * set the current midcom
     * @param string midcom name.
     */
    function set_midcom ($midcom) 
    {
        if ($midcom == null || $midcom == "") {
            return;
        }
        require_once ('stylefinder/midcom.php');
        
        $this->_midcom = new midcom_admin_styleeditor_stylefinder_midcom($midcom);        
    }
    
    /**
     * Set the style
     * @param midcom_db_style
     */
    function set_style (&$style) {
        
        if ($style !== null) {
            require_once ('stylefinder/style.php');
            $this->_style = new midcom_admin_styleeditor_stylefinder_style(&$style);
        }
    }
    /**
     * Initialize the page object.
     */
    function set_page( &$page) 
    {
        if ($page !== null) {
            require_once ('stylefinder/page.php');
            require_once ('stylefinder/style.php');
            $this->_page = new midcom_admin_styleeditor_stylefinder_page(&$page);
            $this->_style = new midcom_admin_styleeditor_stylefinder_style($this->_page->get_style_id());
        }
    }
    /**
     * Create a new finder instance
     * @param object pointer to the "page" (for now, a topic) that starts the
     * stylestack.
     * @param reference to the topic
     * @param reference to page or host object 
     */
    function &factory() {
        
        $obj = & new midcom_admin_styleeditor_stylefinder();
        
        return $obj;
    }
    
    /**
     * Get the stylestack for this finder
     * @return array 
     */
    function get_style_stack() 
    {
        $stack = array();
        $stack = array_merge($stack,$this->_topic->get_style_stack());
        $stack = array_merge($stack,$this->_page->get_style_stack());
        
        return $stack;
    }
    
    /**
     * Get the elements for each part that is relevant:
     * @return array list of elements and some of their attributes
     * 
     * $return = array ('element_name' => array(
     *      'guid' => '<elementguid>',
     *      'name'  => string,
     *      'styleurl' => styleurl,
     *          ) )
     */
    function get_style_elements_all() 
    {
        $return = array();
        // merge up from the bottom of the stack.
        if ($this->_midcom !== null) {
            $return = $this->_midcom->get_style_elements();
        }
        
        if ($this->_page !== null) {
            foreach ($this->_page->get_style_elements() as $name => $info) {
                $return[$name] = $info;
            }
        }
        if ($this->_topic !== null) {
            foreach ($this->_topic->get_style_elements(&$this->_style) as $name => $info) {
                $return[$name] = $info;
            }
        }
        
        return $return;
    
    }
    
    /**
     * get the stack assosiated with the page and it's styles
     * @return array of elements organized after page_elements and styleelements.
     */        
    
    function get_page_elements() 
    {
        if ($this->_page !== null) {
            return  $this->_page->get_style_elements();
        }
        return array();
    }
    
    /**
     * get the name of the page 
     */
    function get_page_title() {
        
        return ($this->_page == null) ? "" : $this->_page->get_title();
    }
    function get_midcom_title() {
        return ($this->_midcom == null) ? "" : $this->_midcom->get_title();
    }
    function get_style_title() {
        return ( $this->_style == null) ? "" : $this->_style->get_title();
    }
    
    /**
     * get the elements in the style
     * @return style element array
     */
    function get_style_elements() 
    {
        if ($this->_style !== null) {
            return $this->_style->get_style_elements_complete($this->_style->get_id());
        }
        return array();
    }
    
    /**
     * Get the elements assosiated with the current midcom.
     */
    function get_midcom_elements() 
    {
        $return = array();
        // merge up from the bottom of the stack.
        $return = $this->_midcom->get_style_elements();
        foreach ($this->get_topic_style_elements() as $name => $info) {
            if (array_key_exists($name, $return)) {
                $info['up'] = $return[$name];
                $return[$name] = $info;
            } else {
                $return[$name] = $info;
            }
        }
        return $return;
    }
    /**
     * Get the styleelements set through the topic's style
     * @return array
     * @access public
     */
    function get_topic_style_elements() {
        if ($this->_topic !== null && $this->_style !== null) {
            return $this->_topic->get_style_elements(&$this->_style);
        }
        return array();
    }
}
