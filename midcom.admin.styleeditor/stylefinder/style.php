<?
/**
 * Created on Oct 30, 2005
 * @author tarjei huse
 * @package midcom.admin.styleeditor
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * This class is a helper for the styleeditor to get the styleelement it wants to edit. 
 */
 
class midcom_admin_styleeditor_stylefinder_style {

    /**
     * the style object
     * @access pricate
     * @var object midcom_style 
     */ 
    var $_style = null ;
    var $_title = "";
    /**
     * Constroctur
     */
    function midcom_admin_styleeditor_stylefinder_style ($style) 
    {
        if (is_object ($style) ) {
            $this->_style = &$style;
        } else {
            $this->_style = new midcom_db_style($style);
        }
    }
    
    /**
     * get the style title
     * @return string title of the style (the path) 
     */
    function get_title () 
    {
        return $this->_title;
    }
    /**
     * Get the id of the top style
     * @return style id
     * @param none
     * @return id style id.
     */
    function get_id() 
    {
        return $this->_style->id;
    }
    
    /**
     * get a list of styleelements from a stylestack and it's substyles
     * Uses the passed stylestack so that it can be used by _topic as well.
     * @static
     * @access public
     * @param array of styleobjects to get elements from.
     * @param stylepath  
     * @return list of styleelements indexed by the styles
     */
    function get_style_elements($styleid,$path = "") 
    {
        $return = array();
        $qb = new MidgardQueryBuilder("midgard_element");
        
        $qb->add_constraint('style', '=', $styleid);
        $elements = @$qb->execute();
        if ($elements !== null && is_array ($elements)) {
            
            foreach ($elements as $element) {
                 $return[$element->name] = array (
                    'name' => $element->name,
                    'guid' => $element->guid,
                    'styleurl' => $path,
                    'comment' =>'',
                    'up' => null
                     );
            }
        }
        return $return;
                
    }
    /**
     * Get a list of styleelements expanded for this style and it's parentstyles
     * @return array of styleelements
     * @param id id of the topstyle.
     * @access public 
     */
    function get_style_elements_complete($styleid) 
    {
        $stack = $this->get_style_and_parents($styleid);
        $path = "";
        $return = array();
        for ($i = 0; $i < count($stack) ; $i++) {
            $path = "/" . $stack[$i]->name ;
            
            $elements = $this->get_style_elements($stack[$i]->id, $path);
            foreach ($elements as $name => $array) {
                if (array_key_exists($name, $return)) {
                    $array['up'] = $return[$name];
                    $return[$name] = $array;
                } else {
                    $return[$name] = $array;
                }
            }
        }
        $this->_title = $path;
        return $return;    
    }
    
    
    /**
     * This funtion returns the style objects as well as it's parents in an array.
     * @param id the id of the style.
     * @return array of styleelements
     * @access public
     */
    function get_style_and_parents($styleid) 
    {
        $stack = array();
        while ($styleid != 0) {
            $style = new midgard_style();
            $style->get_by_id($styleid);
            $stack[] = $style;
            $styleid = $style->up;
            
        }
        
        return $stack;
    }
    

}

