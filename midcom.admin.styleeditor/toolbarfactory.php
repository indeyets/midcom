<?php
/**
 * Created on Nov 29, 2005
 * @author tarjei huse
 * @package midcom.admin.styleeditor
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * 
 */
 
class midcom_admin_styleeditor_toolbarfactory {

    var $_stylefinder = null;
    var $_toolbar     = null;
    function midcom_admin_styleeditor_toolbarfactory ( &$stylefinder) {
        $this->_stylefinder = &$stylefinder;
    }
    /**
     * Set the toolbar object we should work on
     * @param midcom_helper_toolbar
     */
    function set_toolbar(&$toolbar) {
        $this->_toolbar = &$toolbar;
    }        
    /**
     * This generates the whole toolbar. If you provide a toolbar object, it 
     * will use that instead.
     */
    function generate_toolbar() {
        if ($this->_toolbar == null) {
            $this->_toolbar = new midcom_helper_toolbar();
        }
        $this->_toolbar->add_item(
                    $this->get_header_array("Page (" . $this->_stylefinder->get_page_title() . ")", 
                        $this->get_page_toolbar(), 
                        " page elements "));
        $this->_toolbar->add_item($this->get_header_array("Style (" . $this->_stylefinder->get_style_title() . ")", 
                        $this->get_style_toolbar(), " styleelements "));
        $this->_toolbar->add_item($this->get_header_array("Midcom (" . $this->_stylefinder->get_midcom_title() . ")",
            $this->get_midcom_toolbar(), "Elements from the chosen midcom"));
        
    }
    /**
     * Get the generated toolbar.
     * @return micom_helper_toolbar.
     */    
    function &get_toolbar() {
        
        return $this->_toolbar;
    }
    
    /**
     * Generate the element array. Recursive function. 
     * @param array startelement. 
     */
    function get_element_array( $element) 
    {
        return array (
            MIDCOM_TOOLBAR_URL =>$element['guid'],
            MIDCOM_TOOLBAR_LABEL => $element['name'],
            MIDCOM_TOOLBAR_HELPTEXT => $element['styleurl'],
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_ICON => null, 
            MIDCOM_TOOLBAR_SUBMENU => 
                ($element['up'] !== null) ? 
                    $this->get_element_array($element['up']) : null, 
        );
            
    }

    function get_header_array( $name, $elementtoolbar, $helptext = "") 
    {
        return array (
            MIDCOM_TOOLBAR_URL => null,
            MIDCOM_TOOLBAR_LABEL => $name,
            MIDCOM_TOOLBAR_HELPTEXT => $helptext,
            MIDCOM_TOOLBAR_ENABLED => true,
            MIDCOM_TOOLBAR_ICON => null,
            MIDCOM_TOOLBAR_SUBMENU => $elementtoolbar
        );
            
    }
    
    
    /**
     * Make a listing of the pageelements
     */
    function get_page_toolbar () {
        $toolbar = new midcom_helper_toolbar();
        $elements = $this->_stylefinder->get_page_elements();
        
        foreach ($elements as $name => $element) {
            $toolbar->add_item($this->get_element_array($element));        
        }
        return $toolbar;
    }
    
    /**
     * Make a listing of the styleelements at work on the page.
     */
    function get_style_toolbar () 
    {
        $toolbar = new midcom_helper_toolbar();
        
        $elements = $this->_stylefinder->get_style_elements();
        
        if (count($elements) == 0 ) {
            return null;
        }
        foreach ($elements as $name => $element) {
            $toolbar->add_item($this->get_element_array($element));        
        }
        return $toolbar;
    }
    
    /**
     * get the midcom toolbar
     */
    function get_midcom_toolbar() 
    {
        $toolbar = new midcom_helper_toolbar();
        $elements = $this->_stylefinder->get_midcom_elements();
        if (count($elements) == 0 ) {
            return null;
        }
        foreach ($elements as $name => $element) {
            $toolbar->add_item($this->get_element_array($element));        
        }
        return $toolbar;
    }
}