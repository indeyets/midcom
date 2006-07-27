<?php
/**
 * Created on Oct 30, 2005
 * @author tarjei huse
 * @package 
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * This class is a helper for the styleeditor to get the styleelement it wants to edit. 
 */
 
class midcom_admin_styleeditor_stylefinder_midcom {

    /**
     * The midcom in question
     * @acces private
     */
    var $_midcom = null;
    
    /**
     * Path to the midcom filestyle. For now we
     * assume there's only one of these.
     * @access private
     */
    var $_style  = null;
    
    /**
     * The styleelements assosiated with the style
     * @access private
     */
    var $_style_elements = array();
    /**
     * constructor
     * @param object refernce to the page in question
     */
    function midcom_admin_styleeditor_stylefinder_midcom ($midcom ) {
        $this->_midcom = $midcom;
    }
    
    function get_title() {
        return $this->_midcom;
    }
    
    /**
     * This funtion returns the stylestack for a page by finding the context of the 
     * page. Someday... this will not be needed :-)
     */
    function get_style_stack() 
    {
        $midcom_dir = MIDCOM_ROOT . '/'. str_replace (".", '/', $this->_midcom) . "/style";
        $this->_style  =  $midcom_dir;
        
        return array (0 => $this->_style);
    }
    /**
     * this gets the styleelements defined for this midcom.
     * @return datatype description
     * 
     */
    
    function get_style_elements() {
        if ($this->_style == null) {
            $styles = $this->get_style_stack();
            //var_dump($styles);
            $style = $styles[0];
        } else {
            $style = $this->_style;            
        }
        
        if (count($this->_style_elements ) > 0 ) {
            return $this->_style_elements;
        }
        
        $this->_style_elements = array();
        
        $dir_open = @ opendir($this->_style);
        while (($dir_content = readdir($dir_open)) !== false) {
            if (substr($dir_content,-4 ) == '.php') {
                $name = basename($dir_content, '.php');
                $this->_style_elements[$name] = array (
                        'name' => $name,
                        'guid' => null,
                        'styleurl' => 'file://' . $this->_style . '/' . $name,
                        'comment' =>'',
                        'up' => null
                         ); ;
            }
        }
        return $this->_style_elements;
        
    }
    
}
