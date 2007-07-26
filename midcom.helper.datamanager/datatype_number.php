<?php
/**
 * @package midcom.helper.datamanager
 * @author The Midgard Project, http://www.midgard-project.org 
 * @version $Id$
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Number datatype.
 * 
 * This type will store floating point numbers with an specified precision. Values 
 * returned and stored will always be rounded to that precision. Background are the
 * floating point types of PHP, <i>not</i> an arbitrary precision system.
 * 
 * If the precision is set to -1 ("infinite"), the full precision of the underlying
 * PHP type will be kept.
 * 
 * To ensure proper PHP parsing, all string values stored and retrieved are using 
 * a dot, not a komma as a decimal separator. If any komma is found in any string,
 * it is replaced by a dot again, to ensure proper operation.
 * 
 * <b>Default Parameters</b>
 * 
 * - <i>Location</i>: parameter
 * - <i>widget</i>: text
 * - <i>precision</i>: -1
 * 
 * @package midcom.helper.datamanager
 */
class midcom_helper_datamanager_datatype_number extends midcom_helper_datamanager_datatype 
{
    /**
     * The desired precision
     * 
     * @var int
     * @access protected
     */
    var $_precision;
    
    /**
     * Constructor with default configuration.
     */
    function midcom_helper_datamanager_datatype_number (&$datamanager, &$storage, $field) 
    {
        debug_push_class(__CLASS__, __FUNCTION__);
        
        if (!array_key_exists("location", $field))
        {
            $field["location"] = "parameter";
        }
        if (!array_key_exists("widget", $field))
        {
            $field["widget"] = "text";
        }
        if (!array_key_exists("number_precision", $field))
        {
            $field["number_precision"] = -1;
        }
        
        $this->_precision = $field["number_precision"];
        if ($this->_precision < -1)
        {
            $this->_precision = -1;
        }
                
        parent::_constructor ($datamanager, $storage, $field);
        
        debug_add("Initialized to precision {$this->_precision} and value {$this->_value}");
        
        debug_pop();
    }
    
    function load_from_storage() 
    {
        if (! parent::load_from_storage()) 
        {
            return false;
        } 
        else 
        {
        	debug_push_class(__CLASS__, __FUNCTION__);
            $this->_value = (double) str_replace(',', '.', $this->_value);
            debug_add("Set internal Value to [{$this->_value}]");
            debug_pop();
            return true;
        }
    }
    
    function save_to_storage ()
    {
        $numeric_value = $this->_value;
        $this->_value = number_format($this->_value, $this->_precision, '.', '');
        $result = parent::save_to_storage();
        $this->_value = $numeric_value;
        return $result;
    }
    
    function sync_widget_with_data() 
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        
        $widget =& $this->get_widget();
        if ($this->_precision < 0) 
        {
            $widget->set_value($this->_value);
            debug_add("Set value of the widget to {$this->_value}");
        } else {
            $value = number_format($this->_value, $this->_precision, '.', '');
            $widget->set_value($value);
            debug_add("Set valueof the widget to {$value}");
        }
        
        debug_pop();
    }
    
    function _get_widget_default_value () 
    {
        if ($this->_precision < 0) 
        {
            return $this->_value;
        } 
        else 
        {
            return number_format($this->_value, $this->_precision, '.', '');
        }
    }
    
    function sync_data_with_widget () 
    {
    	debug_push_class(__CLASS__, __FUNCTION__);
        
        $widget =& $this->get_widget();
        $string = $widget->get_value();
        if (trim($string) == '') 
        {
            /* Check for empty string, if yes, set the value to 0 */
            $this->_value = 0;
            
            debug_add("Widget was empty, setting to 0.");
            debug_pop();
            return;
        }
        
        /* Replace all kommas with dots */
        $string = str_replace(',', '.', $string);
        
        if (! is_numeric($string)) 
        {
            $this->_datamanager->append_error(
                sprintf($this->_datamanager->_l10n->get("<em>%s</em> could not be converted into a number, ignored.") . "<br>\n", 
                    $this->_field["description"]));
        } 
        else 
        {
            $this->_value = (double) $string;
            if ($this->_precision >= 0) 
            {
                $this->_value = round($this->_value, $this->_precision);
            }
            debug_print_r('Set the value of this type to this:', $this->_value);
        }
        $this->sync_widget_with_data();
    }
    
    function _get_empty_value() 
    {
        return 0;
    }
    
}

?>